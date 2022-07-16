<?php

namespace App\Libraries\mds\service;

use App\Libraries\mds\dao\OrderDao;
use App\Libraries\mds\model\command\OrderCommand;
use InvalidArgumentException;

/**
 * Class OrderService
 * @package App\Libraries\mds\service
 */
class OrderService
{
    private OrderDao $orderDao;
    private static OrderService $instance;

    private function __construct(){}

    public static function getInstance(): OrderService
    {
        if (!isset(self::$instance)) {
            self::$instance = new OrderService();
        }
        return self::$instance;
    }

    public function setOrderDao(OrderDao $orderDao)
    {
        $this->orderDao = $orderDao;
    }

    /**
     * 주문정보 조회
     * @param OrderCommand $orderCommand
     * @return array
     */
    public function selectOrders(OrderCommand $orderCommand): array
    {
        if (!isset($orderCommand->memberSeq) || $orderCommand->memberSeq <= 0) {
            throw new InvalidArgumentException('유효하지 않은 파라미터');
        }
        $result = [];
        $orderList = $this->orderDao->selectOrders($orderCommand);

        foreach ($orderList as $orderSeq => $order) {
            $orderProductList = [];
            foreach ($order->getOrderProductList() as $orderProductSeq => $orderProduct) {
                $orderProductList[$orderProductSeq] = [
                    'orderProductSeq' => $orderProduct->getOrderProductSeq(),
                    'orderProductState' => $orderProduct->getOrderProductState(),
                    'orderProductStateName' => ORDER_STATE_NAME[$orderProduct->getOrderProductState()],
                    'productSeq' => $orderProduct->getProductSeq(),
                    'productName' => $orderProduct->getProductName(),
                    'orderProductPrice' => $orderProduct->getOrderProductPrice(),
                    'quantity' => $orderProduct->getQuantity()
                ];
            }
            $result[$orderSeq] = [
                'orderSeq' => $order->getOrderSeq(),
                'createDate' => $order->getCreateDate(),
                'price' => $order->getPrice(),
                'deliveryPrice' => $order->getDeliveryPrice(),
                'deliveryMethod' => $order->getDeliveryMethod(),
                'deliveryMethodName' => DELIVERY_METHOD_NAME[$order->getDeliveryMethod()],
                'memberSeq' => $order->getMemberSeq(),
                'orderProductList' => $orderProductList
            ];
        }

        return $result;
    }

    /**
     * 주문상품 상태 업데이트
     * @param OrderCommand $orderCommand
     * @return bool
     */
    public function updateOrderProductState(OrderCommand $orderCommand): bool
    {
        if (!isset($orderCommand->orderProductState) || $orderCommand->orderProductSeq <= 0) {
            throw new InvalidArgumentException('유효하지 않은 파라미터');
        }
        return $this->orderDao->updateOrderProductState($orderCommand);
    }
}