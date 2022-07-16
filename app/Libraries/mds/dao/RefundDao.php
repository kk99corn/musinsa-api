<?php

namespace App\Libraries\mds\dao;

use App\Libraries\mds\model\command\RefundCommand;
use PDO;
use PDOException;

class RefundDao
{
    private PDO $pdo;

    private static RefundDao $instance;

    private function __construct()
    {
    }

    public static function getInstance(): RefundDao
    {
        if (!isset(self::$instance)) {
            self::$instance = new RefundDao();
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
     * 환불 입력
     * @param RefundCommand $refundCommand
     * @return string
     */
    public function insertRefund(RefundCommand $refundCommand): string
    {
        $aBindParam = [];
        $sQuery = 'INSERT INTO tRefund (nOrderSeq, nRefundMethod, nRefundState, nRefundPrice, sOrderProductSeqList) 
                    VALUES (:orderSeq, :refundMethod, :refundState, :refundPrice, :orderProductSeqList)';
        $aBindParam[':orderSeq'] = $refundCommand->orderSeq;
        $aBindParam[':refundMethod'] = $refundCommand->refundMethodSeq;
        $aBindParam[':refundState'] = REFUND_PROCESS_STATE_REQUEST;
        $aBindParam[':refundPrice'] = $refundCommand->refundPrice;
        $aBindParam[':orderProductSeqList'] = implode(',', $refundCommand->orderProductSeqList);

        $stmt = $this->pdo->prepare($sQuery);
        if ($stmt->execute($aBindParam) === false) {
            $error = $stmt->errorInfo();
            throw new PDOException(__CLASS__ . ' > ' . __FUNCTION__ . ' [' . $error[0] . ']' . '[' . $error[1] . '] ' . $error[2]);
        }
        return $this->pdo->lastInsertId();
    }
}