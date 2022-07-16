<?php

namespace App\Controllers;

use App\Libraries\ApiResponseTemplate;
use App\Libraries\mds\dao\OrderDao;
use App\Libraries\mds\model\command\OrderCommand;
use App\Libraries\mds\service\OrderService;
use App\Libraries\util\DSNUtil;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use PDO;

/**
 * Class Order
 * 주문정보 API
 * @package App\Controllers
 */
class Order extends BaseController
{
    private ApiResponseTemplate $apiResponse;
    private OrderService $orderService;

    public function __construct()
    {
        $this->apiResponse = new ApiResponseTemplate();
        try {
            $pdo = new PDO(DSNUtil::getDSN('sqlite'));

            $orderDao = OrderDao::getInstance();
            $orderDao->setPdo($pdo);
            $this->orderService = OrderService::getInstance();
            $this->orderService->setOrderDao($orderDao);
        } catch (Exception $e) {
            // db 연결 실패
            log_message('error', '[ERROR] ' . __CLASS__ . ' > ' . __FUNCTION__ . '(' . __LINE__ . ')'
                . ', DB연결실패');
        }
    }

    /**
     * 주문조회(/api/v1/orders)
     * @return ResponseInterface
     */
    public function getOrders(): ResponseInterface
    {
        $orderList = [];
        $aParams = [
            'memberSeq' => (int)$this->request->getGet('memberSeq') ?? 0,
            'orderSeq' => (int)$this->request->getGet('orderSeq') ?? 0
        ];
        log_message('error', '[INFO] ' . __CLASS__ . ' > ' . __FUNCTION__ . '(' . __LINE__ . ')'
            . ', ip: ' . $this->request->getServer('REMOTE_ADDR')
            . ', param: ' . json_encode($aParams));

        // 파라미터 체크
        if ($aParams['memberSeq'] <= 0) {
            // 필수값 누락
            $this->apiResponse->setStatusCode(400);
            $this->apiResponse->setDesc('필수 파라미터 누락');
        }

        if ($this->apiResponse->getStatusCode() === 200) {
            // 주문조회
            $orderCommand = new OrderCommand();
            $orderCommand->memberSeq = $aParams['memberSeq'];
            $orderCommand->orderSeq = $aParams['orderSeq'];

            try {
                $orderList = $this->orderService->selectOrders($orderCommand);
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
        $this->apiResponse->setResult($orderList);

        return $this->response->setJSON($this->apiResponse->toArray());
    }
}