<?php

namespace App\Controllers;

use App\Libraries\ApiResponseTemplate;
use App\Libraries\mds\dao\OrderDao;
use App\Libraries\mds\dao\RefundDao;
use App\Libraries\mds\model\command\RefundCommand;
use App\Libraries\mds\service\OrderService;
use App\Libraries\mds\service\RefundService;
use App\Libraries\util\DSNUtil;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use PDO;

/**
 * Class Refund
 * 환불 API
 * @package App\Controllers
 */
class Refund extends BaseController
{
    private ApiResponseTemplate $apiResponse;

    private OrderService $orderService;
    private RefundService $refundService;

    public function __construct()
    {
        $this->apiResponse = new ApiResponseTemplate();
        try {
            $pdo = new PDO(DSNUtil::getDSN('sqlite'));

            $orderDao = OrderDao::getInstance();
            $orderDao->setPdo($pdo);
            $this->orderService = OrderService::getInstance();
            $this->orderService->setOrderDao($orderDao);

            $refundDao = RefundDao::getInstance();
            $refundDao->setPdo($pdo);
            $this->refundService = RefundService::getInstance();
            $this->refundService->setRefundDao($refundDao);
            $this->refundService->setOrderService($this->orderService);
        } catch (Exception $e) {
            // db 연결 실패
            log_message('error', '[ERROR] ' . __CLASS__ . ' > ' . __FUNCTION__ . '(' . __LINE__ . ')'
                . ', ip: ' . $this->request->getServer('REMOTE_ADDR')
                . ', DB연결실패');
        }
    }


    /**
     * 반품비 예상 금액 조회(/api/v1/refund/expectation)
     * @return ResponseInterface
     */
    public function getRefundExpectation(): ResponseInterface
    {
        $expectationRefund = [];
        $aParams = [
            'memberSeq' => (int)$this->request->getGet('memberSeq') ?? 0,
            'orderSeq' => (int)$this->request->getGet('orderSeq') ?? 0,
            'orderProductSeqList' => ($this->request->getGet('orderProductSeqList') != '') ? explode(',', $this->request->getGet('orderProductSeqList')) : [],
            'refundMethodSeq' => (int)$this->request->getGet('refundMethodSeq') ?? 0,
        ];
        log_message('error', '[INFO] ' . __CLASS__ . ' > ' . __FUNCTION__ . '(' . __LINE__ . ')'
            . ', ip: ' . $this->request->getServer('REMOTE_ADDR')
            . ', param: ' . json_encode($aParams));

        // 파라미터 체크
        if ($aParams['memberSeq'] <= 0 || $aParams['orderSeq'] <= 0 || $aParams['refundMethodSeq'] <= 0) {
            // 필수값 누락
            $this->apiResponse->setStatusCode(400);
            $this->apiResponse->setDesc('필수 파라미터 누락');
        } else if (!in_array($aParams['refundMethodSeq'], REFUND_METHOD)) {
            // 환불방법 체크
            $this->apiResponse->setStatusCode(400);
            $this->apiResponse->setDesc('refundMethodSeq 파라미터 범위(1~2)');
        } else if (count($aParams['orderProductSeqList']) > 0) {
            foreach ($aParams['orderProductSeqList'] as $key => $orderProductSeq) {
                if (!is_numeric($orderProductSeq)) {
                    $this->apiResponse->setStatusCode(400);
                    $this->apiResponse->setDesc('orderProductSeqList 파라미터 오류(숫자만 가능)');
                    break;
                }
                $aParams['orderProductSeqList'][$key] = (int)$orderProductSeq;
            }
            $aParams['orderProductSeqList'] = array_unique($aParams['orderProductSeqList']);
        }

        if ($this->apiResponse->getStatusCode() === 200) {
            $refundCommand = new RefundCommand();
            $refundCommand->memberSeq = $aParams['memberSeq'];
            $refundCommand->orderSeq = $aParams['orderSeq'];
            $refundCommand->orderProductSeqList = $aParams['orderProductSeqList'];
            $refundCommand->refundMethodSeq = $aParams['refundMethodSeq'];

            try {
                $expectationRefund = $this->refundService->getExpectationRefund($refundCommand);
            } catch (Exception $e) {
                $this->apiResponse->setStatusCode(500);
                $this->apiResponse->setDesc($e->getMessage());

                log_message('error', '[ERROR] ' . __CLASS__ . ' > ' . __FUNCTION__ . '(' . __LINE__ . ')'
                    . ', ip: ' . $this->request->getServer('REMOTE_ADDR')
                    . ', exception: ' . get_class($e)
                    . ', exceptionMessage: ' . $e->getMessage());
            }
        }

        $this->response->setHeader('content-type', 'application/json');
        $this->response->setStatusCode($this->apiResponse->getStatusCode());
        $this->apiResponse->setResult($expectationRefund);

        return $this->response->setJSON($this->apiResponse->toArray());
    }

    /**
     * 교환/환불 접수
     * route
     *  /api/v1/refund/exchange (교환)
     *  /api/v1/refund/return (환불)
     * @param $refundMethod
     * @return ResponseInterface
     */
    public function postRefund($refundMethod): ResponseInterface
    {
        $refundResult = [];
        $aParams = [
            'memberSeq' => (int)$this->request->getPost('memberSeq') ?? 0,
            'orderSeq' => (int)$this->request->getPost('orderSeq') ?? 0,
            'orderProductSeqList' => ($this->request->getPost('orderProductSeqList') != '') ? explode(',', $this->request->getPost('orderProductSeqList')) : []
        ];
        log_message('error', '[INFO] ' . __CLASS__ . ' > ' . __FUNCTION__ . '(' . __LINE__ . ')'
            . ', ip: ' . $this->request->getServer('REMOTE_ADDR')
            . ', refundMethod: ' . $refundMethod . '(' . REFUND_METHOD_NAME[$refundMethod] . ')'
            . ', param: ' . json_encode($aParams));

        // 파라미터 체크
        if ($aParams['memberSeq'] <= 0 || $aParams['orderSeq'] <= 0) {
            // 필수값 누락
            $this->apiResponse->setStatusCode(400);
            $this->apiResponse->setDesc('필수 파라미터 누락');
        } else if (count($aParams['orderProductSeqList']) > 0) {
            foreach ($aParams['orderProductSeqList'] as $key => $orderProductSeq) {
                if (!is_numeric($orderProductSeq)) {
                    $this->apiResponse->setStatusCode(400);
                    $this->apiResponse->setDesc('orderProductSeqList 파라미터 오류(숫자만 가능)');
                    break;
                }
                $aParams['orderProductSeqList'][$key] = (int)$orderProductSeq;
            }
            $aParams['orderProductSeqList'] = array_unique($aParams['orderProductSeqList']);
        }

        if ($this->apiResponse->getStatusCode() === 200) {
            $refundCommand = new RefundCommand();
            $refundCommand->memberSeq = $aParams['memberSeq'];
            $refundCommand->orderSeq = $aParams['orderSeq'];
            $refundCommand->orderProductSeqList = $aParams['orderProductSeqList'];
            $refundCommand->refundMethodSeq = $refundMethod;

            try {
                $refundResult = $this->refundService->insertRefund($refundCommand);
            } catch (Exception $e) {
                $this->apiResponse->setStatusCode(500);
                $this->apiResponse->setDesc($e->getMessage());

                log_message('error', '[ERROR] ' . __CLASS__ . ' > ' . __FUNCTION__ . '(' . __LINE__ . ')'
                    . ', ip: ' . $this->request->getServer('REMOTE_ADDR')
                    . ', exception: ' . get_class($e)
                    . ', exceptionMessage: ' . $e->getMessage());
            }
        }
        $this->response->setHeader('content-type', 'application/json');
        $this->response->setStatusCode($this->apiResponse->getStatusCode());
        $this->apiResponse->setResult($refundResult);

        return $this->response->setJSON($this->apiResponse->toArray());
    }
}