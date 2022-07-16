<?php

namespace App\Libraries\mds\dao;

use App\Libraries\mds\model\command\OrderCommand;
use App\Libraries\mds\model\Order;
use App\Libraries\mds\model\OrderProduct;
use PDO;
use PDOException;

class OrderDao
{
    private PDO $pdo;

    private static OrderDao $instance;

    private function __construct()
    {
    }

    public static function getInstance(): OrderDao
    {
        if (!isset(self::$instance)) {
            self::$instance = new OrderDao();
        }
        return self::$instance;
    }

    public function setPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * 주문정보 조회
     * @param OrderCommand $orderCommand
     * @return array
     */
    public function selectOrders(OrderCommand $orderCommand): array
    {
        $aResult = [];
        $aBindParam = [];
        $sQuery = '
            SELECT
                ord.nOrderSeq AS orderSeq,
                ord.dtCreate as createDate,
                ord.nPrice as price,
                ord.nDeliveryPrice as deliveryPrice,
                ord.nDeliveryMethod as deliveryMethod,
                ord.nMemberSeq as memberSeq,
                ordp.nOrderProductSeq as orderProductSeq,
                ordp.nOrderProductState as orderProductState,
                ordp.nProductSeq as productSeq,
                ordp.sProductName as productName,
                ordp.nOrderProductPrice as orderProductPrice,
                ordp.nQuantity as quantity
            FROM tOrder ord, tOrderProduct ordp
            WHERE ord.nOrderSeq = ordp.nOrderSeq
            AND ord.nMemberSeq = :memberSeq
        ';
        $aBindParam[':memberSeq'] = $orderCommand->memberSeq;

        if (isset($orderCommand->orderSeq) && $orderCommand->orderSeq > 0) {
            $sQuery .= 'AND ord.nOrderSeq = :orderSeq';
            $aBindParam[':orderSeq'] = $orderCommand->orderSeq;
        }

        $stmt = $this->pdo->prepare($sQuery);
        if ($stmt->execute($aBindParam) === false) {
            $error = $stmt->errorInfo();
            throw new PDOException(__CLASS__ . ' > ' . __FUNCTION__ . ' [' . $error[0] . ']' . '[' . $error[1] . '] ' . $error[2]);
        }
        $selectRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($selectRows as $row) {
            $orderProductList[$row['orderSeq']][$row['orderProductSeq']] = new OrderProduct(
                $row['orderProductSeq'],
                $row['orderSeq'],
                $row['orderProductState'],
                $row['productSeq'],
                $row['productName'],
                $row['orderProductPrice'],
                $row['quantity']
            );

            $order = new Order(
                $row['orderSeq'],
                $row['createDate'],
                $row['price'],
                $row['deliveryPrice'],
                $row['deliveryMethod'],
                $row['memberSeq'],
                $orderProductList[$row['orderSeq']]
            );

            $aResult[$row['orderSeq']] = $order;
        }

        return $aResult;
    }

    /**
     * 주문상품 상태 업데이트
     * @param OrderCommand $orderCommand
     * @return bool
     */
    public function updateOrderProductState(OrderCommand $orderCommand): bool
    {
        $aBindParam = [];
        $sQuery = 'UPDATE tOrderProduct SET nOrderProductState = :orderProductState WHERE nOrderProductSeq = :orderProductSeq';
        $aBindParam[':orderProductState'] = $orderCommand->orderProductState;
        $aBindParam[':orderProductSeq'] = $orderCommand->orderProductSeq;

        $stmt = $this->pdo->prepare($sQuery);
        if ($stmt->execute($aBindParam) === false) {
            $error = $stmt->errorInfo();
            throw new PDOException(__CLASS__ . ' > ' . __FUNCTION__ . ' [' . $error[0] . ']' . '[' . $error[1] . '] ' . $error[2]);
        }
        return true;
    }
}