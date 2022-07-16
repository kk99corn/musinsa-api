<?php

namespace App\Libraries\mds\service;

use App\Libraries\mds\dao\RefundDao;
use App\Libraries\mds\model\command\OrderCommand;
use App\Libraries\mds\model\command\RefundCommand;
use Exception;
use InvalidArgumentException;
use PDOException;

class RefundService
{
    private RefundDao $refundDao;
    private OrderService $orderService;
    private static RefundService $instance;

    private function __construct(){}

    public static function getInstance(): RefundService
    {
        if (!isset(self::$instance)) {
            self::$instance = new RefundService();
        }
        return self::$instance;
    }

    public function setRefundDao(RefundDao $refundDao)
    {
        $this->refundDao = $refundDao;
    }

    /**
     * @param OrderService $orderService
     */
    public function setOrderService(OrderService $orderService): void
    {
        $this->orderService = $orderService;
    }

    /**
     * 반품비 예상 금액 조회
     * @param RefundCommand $refundCommand
     * @return array
     */
    public function getExpectationRefund(RefundCommand $refundCommand): array
    {
        if (!isset($refundCommand->memberSeq) || $refundCommand->memberSeq <= 0
            || !isset($refundCommand->orderSeq) || $refundCommand->orderSeq <= 0
            || !in_array($refundCommand->refundMethodSeq, REFUND_METHOD)) {
            throw new InvalidArgumentException('유효하지 않은 파라미터');
        }
        $result = [
            'isRefundAvailable' => true,        // 환불접수 주문/주문상품 환불 가능여부
            'refundMethod' => REFUND_METHOD_NAME[$refundCommand->refundMethodSeq],  // 환불방법(교환/반품)
            'refundPrice' => 0,                 // 환불금액
            'refundOrderProductSeqList' => [],  // 환불예정 주문상품번호
            'description' => ''
        ];

        // 주문조회
        $orderCommand = new OrderCommand();
        $orderCommand->memberSeq = $refundCommand->memberSeq;
        $orderCommand->orderSeq = $refundCommand->orderSeq;
        $order = current($this->orderService->selectOrders($orderCommand));

        if (!is_array($order) || count($order) === 0) {
            // 주문정보 없음
            $isRefundAvailable = false;
            $result['description'] = '해당하는 주문정보 없음(memberSeq=' . $refundCommand->memberSeq . ', orderSeq=' . $refundCommand->orderSeq . ')';
        } else {
            $isRefundAvailable = true;  // 환불접수 주문 or 주문상품 환불 가능여부

            $nOrderPrice = $order['price'];
            $nDeliveryMethod = $order['deliveryMethod'];    // 배송비정책
            $nDeliveryPrice = $order['deliveryPrice'];      // 배송비
            $aNotExistOrderProductSeq = [];                 // 환불요청 주문에 포함되지않는 주문상품번호
            $aNotChangeOrderProductSeq = [];                // 환불처리 불가능한 주문상품번호

            // 환불가능 주문상품 체크
            $aRefundAvailableOrderProductSeqList = [];      // 환불가능 주문상품번호 리스트
            if (count($refundCommand->orderProductSeqList) <= 0) {
                // 환불접수할 주문상품 선택하지 않은 경우, 해당 주문내 남아있는 환불 가능한 모든 상품 환불 계산
                foreach ($order['orderProductList'] as $orderProduct) {
                    // 주문상품 환불 가능한 주문상태(결제완료, 배송완료, 교환완료)인지 체크
                    if (in_array($orderProduct['orderProductState'], REFUND_REQUEST_AVAILABLE_ORDER_STATE)) {
                        $aRefundAvailableOrderProductSeqList[] = $orderProduct['orderProductSeq'];
                    } else {
                        // 환불접수 주문상품 환불 불가능한 상태
                        $isRefundAvailable = false;
                        $aNotChangeOrderProductSeq[] = $orderProduct['orderProductSeq'] . '(' . ORDER_STATE_NAME[$order['orderProductList'][$orderProduct['orderProductSeq']]['orderProductState']] . ')';
                        $result['description'] = '해당 주문상품 환불접수 불가능 상태(orderProductSeq=' . implode(',', $aNotChangeOrderProductSeq) . ')';
                    }
                }
            } else {
                // 환불접수할 주문상품 선택한 경우
                // 환불접수 주문상품 존재여부 체크
                foreach ($refundCommand->orderProductSeqList as $nOrderProductSeq) {
                    if (!isset($order['orderProductList'][$nOrderProductSeq])) {
                        // 환불접수 주문상품 해당 주문에 없음
                        $isRefundAvailable = false;
                        $aNotExistOrderProductSeq[] = $nOrderProductSeq;
                        $result['description'] = '해당 주문에 다음 주문상품정보 없음(orderProductSeq=' . implode(',', $aNotExistOrderProductSeq) . ')';
                    } else {
                        // 환불접수 주문상품 환불 가능한 주문상태(결제완료, 배송완료, 교환완료)인지 체크
                        if (in_array($order['orderProductList'][$nOrderProductSeq]['orderProductState'], REFUND_REQUEST_AVAILABLE_ORDER_STATE)) {
                            $aRefundAvailableOrderProductSeqList[] = $nOrderProductSeq;
                        } else {
                            // 환불접수 주문상품 환불 불가능한 상태
                            $isRefundAvailable = false;
                            $aNotChangeOrderProductSeq[] = $nOrderProductSeq . '(' . ORDER_STATE_NAME[$order['orderProductList'][$nOrderProductSeq]['orderProductState']] . ')';
                            $result['description'] = '해당 주문상품 환불접수 불가능 상태(orderProductSeq=' . implode(',', $aNotChangeOrderProductSeq) . ')';
                        }
                    }
                }
            }

            if ($isRefundAvailable) {
                $result['refundOrderProductSeqList'] = $aRefundAvailableOrderProductSeqList;
                if ($refundCommand->refundMethodSeq === REFUND_METHOD_EXCHANGE) {
                    // 교환정책 : 무료 정책과 유료정책 모두 왕복 배송비 5000원이 요구됩니다.
                    $result['refundPrice'] = 5000 * count($aRefundAvailableOrderProductSeqList);
                } else if ($refundCommand->refundMethodSeq === REFUND_METHOD_RETURN) {
                    // 환불정책
                    //  a. 주문 전체 환불인 경우 편도 반품 배송비 + 배송비 지원금액까지 더하여 반품비가 요구됩니다.
                    //  b. 주문 일부 환불인 경우 배송비 지원금액은 아직 유효하므로, 반품비는 편도 배송비 금액만큼만 요구됩니다.

                    $nTotalOrderProductCount = count($order['orderProductList']);   // 전체 주문상품수
                    $nTotalReturnOrderProductCount = 0;    // 환불접수/환불완료 상품수
                    foreach ($order['orderProductList'] as $orderProduct) {
                        if (in_array($orderProduct['orderProductState'], [ORDER_STATE_RETURN_REQUEST, ORDER_STATE_RETURN_COMPLETED])) {
                            $nTotalReturnOrderProductCount++;
                        }
                    }

                    // 배송비정책별 배송비지원금액 계산
                    switch ($nDeliveryMethod) {
                        case DELIVERY_METHOD_PAY:
                            // 유료배송
                            $nDeliverySubsidy = 0;
                            break;
                        case DELIVERY_METHOD_FREE:
                            // 무료배송
                            $nDeliverySubsidy = $nDeliveryPrice;
                            break;
                        case DELIVERY_METHOD_CONDITIONALLY_FREE:
                            // 조건부무료배송
                            if ($nOrderPrice >= DELIVERY_METHOD_CONDITIONALLY_PRICE) {
                                $nDeliverySubsidy = $nDeliveryPrice;
                            } else {
                                $nDeliverySubsidy = 0;
                            }
                            break;
                        default:
                            $nDeliverySubsidy = 0;
                            break;
                    }

                    // 환불완료 주문 수 + 환불접수 주문 수
                    $nTotalReturnOrderProductCount += count($result['refundOrderProductSeqList']);

                    // 전체 상품 환불인 경우, 반품비에 배송비 지원금액 추가
                    if ($nTotalReturnOrderProductCount === $nTotalOrderProductCount) {
                        $result['refundPrice'] += $nDeliverySubsidy;
                    }

                    $result['refundPrice'] += ($nDeliveryPrice * count($result['refundOrderProductSeqList']));
                }
            }
        }

        $result['isRefundAvailable'] = $isRefundAvailable;
        return $result;
    }

    /**
     * 환불정보 입력 + 주문상품상태 변경
     * @param RefundCommand $refundCommand
     * @return array
     */
    public function insertRefund(RefundCommand $refundCommand): array
    {
        $result = $this->getExpectationRefund($refundCommand);
        if ($result['isRefundAvailable']) {
            $this->refundDao->getPdo()->beginTransaction();

            try {
                $insertRefundCommand = new RefundCommand();
                $insertRefundCommand->orderSeq = $refundCommand->orderSeq;
                $insertRefundCommand->refundMethodSeq = $refundCommand->refundMethodSeq;
                $insertRefundCommand->refundPrice = $result['refundPrice'];
                $insertRefundCommand->orderProductSeqList = $result['refundOrderProductSeqList'];
                // 환불정보 저장
                $this->refundDao->insertRefund($insertRefundCommand);

                foreach ($result['refundOrderProductSeqList'] as $refundOrderProductSeq) {
                    $nOrderProductState = 0;
                    // 환불방법에 따라 주문상품상태 변경
                    switch ($refundCommand->refundMethodSeq) {
                        case REFUND_METHOD_EXCHANGE:
                            // 환불방법: 교환 -> 주문상품상태: 교환접수
                            $nOrderProductState = ORDER_STATE_EXCHANGE_REQUEST;
                            break;
                        case REFUND_METHOD_RETURN:
                            // 환불방법: 환불 -> 주문상품상태: 환불접수
                            $nOrderProductState = ORDER_STATE_RETURN_REQUEST;
                            break;
                    }
                    $orderCommand = new OrderCommand();
                    $orderCommand->orderProductState = $nOrderProductState;
                    $orderCommand->orderProductSeq = $refundOrderProductSeq;
                    // 주문상품상태 업데이트
                    if ($this->orderService->updateOrderProductState($orderCommand) !== true) {
                        $this->refundDao->getPdo()->rollBack();
                    }
                }
                $this->refundDao->getPdo()->commit();
            } catch(Exception $e) {
                $this->refundDao->getPdo()->rollBack();
            }
        }
        return $result;
    }
}