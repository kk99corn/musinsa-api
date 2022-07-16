<?php

namespace App\Controllers;

use App\Libraries\util\DSNUtil;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\ApiResponseTemplate;
use PDO;

class DataInit extends BaseController
{
    public function index(): ResponseInterface
    {
        $apiResponse = new ApiResponseTemplate();
        $sqlitePdo = null;

        try {
            if (!is_dir(SQLITE_PATH)) {
                mkdir(SQLITE_PATH, 0777, true);
            }
            $sqlitePdo = new PDO(DSNUtil::getDSN('sqlite'));
        } catch (\Exception $e) {
            $apiResponse->setStatusCode(500);
            $apiResponse->setDesc($e->getMessage());
        }

        if ($apiResponse->getStatusCode() !== 500) {
            try {
                // 1. 테이블 생성
                // 회원
                $sCreateMemberQuery = "
                        CREATE TABLE IF NOT EXISTS tMember (
                            nMemberSeq  INTEGER PRIMARY KEY AUTOINCREMENT,
                            sMemberId   TEXT NOT NULL DEFAULT ''
                        );
                    ";
                $sqlitePdo->exec($sCreateMemberQuery);

                // 업체
                $sCreateShopQuery = "
                        CREATE TABLE IF NOT EXISTS tShop (
                            nShopSeq    INTEGER PRIMARY KEY AUTOINCREMENT,
                            sShopName   TEXT NOT NULL DEFAULT ''
                        );
                    ";
                $sqlitePdo->exec($sCreateShopQuery);

                // 상품
                $sCreateProductQuery = "
                        CREATE TABLE IF NOT EXISTS tProduct (
                            nProductSeq     INTEGER PRIMARY KEY AUTOINCREMENT,
                            nShopSeq        INTEGER,
                            sProductName    TEXT NOT NULL DEFAULT '',
                            nPrice          INTEGER NOT NULL DEFAULT 0,
                            nStock          INTEGER NOT NULL DEFAULT 0,
                            FOREIGN KEY ('nShopSeq') REFERENCES tShop (nShopSeq)
                        );
                    ";
                $sqlitePdo->exec($sCreateProductQuery);

                // 주문
                $sCreateOrderQuery = "
                        CREATE TABLE IF NOT EXISTS tOrder (
                            nOrderSeq       INTEGER PRIMARY KEY AUTOINCREMENT,
                            dtCreate        DATE DEFAULT (datetime('now','localtime')),
                            nPrice          INTEGER NOT NULL DEFAULT 0,
                            nDeliveryPrice  INTEGER NOT NULL DEFAULT 0,
                            nDeliveryMethod INTEGER NOT NULL DEFAULT 0,
                            nMemberSeq      INTEGER,
                            FOREIGN KEY ('nMemberSeq') REFERENCES tMember (nMemberSeq)
                        );
                    ";
                $sqlitePdo->exec($sCreateOrderQuery);

                // 주문상품
                $sCreateOrderProductQuery = "
                        CREATE TABLE IF NOT EXISTS tOrderProduct (
                            nOrderProductSeq    INTEGER PRIMARY KEY AUTOINCREMENT,
                            nOrderSeq           INTEGER,
                            nProductSeq         INTEGER,
                            nOrderProductState  INTEGER NOT NULL DEFAULT 1,
                            sProductName        TEXT NOT NULL DEFAULT '',
                            nOrderProductPrice  INTEGER NOT NULL DEFAULT 0,
                            nQuantity           INTEGER,
                            FOREIGN KEY ('nOrderSeq') REFERENCES tOrder (nOrderSeq),
                            FOREIGN KEY ('nProductSeq') REFERENCES tProduct (nProductSeq)
                        );
                    ";
                $sqlitePdo->exec($sCreateOrderProductQuery);

                // 환불
                $sCreateRefundQuery = "
                        CREATE TABLE IF NOT EXISTS tRefund (
                            nRefundSeq              INTEGER PRIMARY KEY AUTOINCREMENT,
                            nOrderSeq               INTEGER,
                            dtRequestDate           DATE DEFAULT (datetime('now','localtime')),
                            dtCompletedDate         DATE,
                            nRefundMethod           INTEGER NOT NULL DEFAULT 0,
                            nRefundState            INTEGER NOT NULL DEFAULT 0,
                            nRefundPrice            INTEGER NOT NULL DEFAULT 0,
                            sOrderProductSeqList    TEXT NOT NULL DEFAULT '',
                            FOREIGN KEY ('nOrderSeq') REFERENCES tOrder (nOrderSeq)
                        );
                    ";
                $sqlitePdo->exec($sCreateRefundQuery);

                // 2. 데이터 초기화
                $aTable = ['tRefund', 'tOrderProduct', 'tOrder', 'tProduct', 'tShop', 'tMember'];
                foreach ($aTable as $sTableName) {
                    $sDeleteQuery = "DELETE FROM {$sTableName}";
                    $stmt = $sqlitePdo->prepare($sDeleteQuery);
                    $stmt->execute();
                }

                // 3. 기본 데이터 세팅
                // 회원
                $aMemberList = [
                    [1, 'hahahoho5915']
                ];
                foreach ($aMemberList as $aMember) {
                    $sQuery = 'INSERT INTO tMember (nMemberSeq, sMemberId) VALUES (:nMemberSeq, :sMemberId)';
                    $stmt = $sqlitePdo->prepare($sQuery);
                    $stmt->bindParam(':nMemberSeq', $aMember[0], PDO::PARAM_INT);
                    $stmt->bindParam(':sMemberId', $aMember[1]);
                    $stmt->execute();
                }

                // 업체
                $aShopList = [
                    [1, '무신사 스탠다드'],
                    [2, 'IAB studio'],
                    [3, '루이비동']
                ];
                foreach ($aShopList as $aShop) {
                    $sQuery = 'INSERT INTO tShop (nShopSeq, sShopName) VALUES (:nShopSeq, :sShopName)';
                    $stmt = $sqlitePdo->prepare($sQuery);
                    $stmt->bindParam(':nShopSeq', $aShop[0], PDO::PARAM_INT);
                    $stmt->bindParam(':sShopName', $aShop[1]);
                    $stmt->execute();
                }

                // 상품
                $aProductList = [
                    [1, 1, '신발A', 30000, 999],
                    [2, 2, '신발B', 40000, 999],
                    [3, 3, '신발C', 50000, 999],
                    [4, 1, '셔츠A', 15000, 999],
                    [5, 2, '셔츠B', 16000, 999],
                    [6, 3, '셔츠C', 17000, 999],
                    [7, 1, '바지A', 45000, 999],
                    [8, 2, '바지B', 10000, 999],
                    [9, 3, '바지C', 38000, 999]
                ];
                foreach ($aProductList as $aProduct) {
                    $sQuery = 'INSERT INTO tProduct (nProductSeq, nShopSeq, sProductName, nPrice, nStock) 
                              VALUES (:nProductSeq, :nShopSeq, :sProductName, :nPrice, :nStock)';
                    $stmt = $sqlitePdo->prepare($sQuery);
                    $stmt->bindParam(':nProductSeq', $aProduct[0], PDO::PARAM_INT);
                    $stmt->bindParam(':nShopSeq', $aProduct[1], PDO::PARAM_INT);
                    $stmt->bindParam(':sProductName', $aProduct[2]);
                    $stmt->bindParam(':nPrice', $aProduct[3], PDO::PARAM_INT);
                    $stmt->bindParam(':nStock', $aProduct[4], PDO::PARAM_INT);
                    $stmt->execute();
                }

                // 주문
                $aOrderList = [
                    [1, 120000, 3000, DELIVERY_METHOD_PAY, 1],
                    [2, 48000, 2500, DELIVERY_METHOD_FREE, 1],
                    [3, 93000, 2500, DELIVERY_METHOD_CONDITIONALLY_FREE, 1]
                ];
                foreach ($aOrderList as $aOrder) {
                    $sQuery = 'INSERT INTO tOrder (nOrderSeq, nPrice, nDeliveryPrice, nDeliveryMethod, nMemberSeq) 
                              VALUES (:nOrderSeq, :nPrice, :nDeliveryPrice, :nDeliveryMethod, :nMemberSeq)';
                    $stmt = $sqlitePdo->prepare($sQuery);
                    $stmt->bindParam(':nOrderSeq', $aOrder[0], PDO::PARAM_INT);
                    $stmt->bindParam(':nPrice', $aOrder[1], PDO::PARAM_INT);
                    $stmt->bindParam(':nDeliveryPrice', $aOrder[2], PDO::PARAM_INT);
                    $stmt->bindParam(':nDeliveryMethod', $aOrder[3], PDO::PARAM_INT);
                    $stmt->bindParam(':nMemberSeq', $aOrder[4], PDO::PARAM_INT);
                    $stmt->execute();
                }

                // 주문상품
                $aOrderProductList = [
                    [1, 1, 1, ORDER_STATE_DELIVERY_COMPLETED, '신발A', 30000, 1],
                    [2, 1, 2, ORDER_STATE_DELIVERY_COMPLETED, '신발B', 40000, 1],
                    [3, 1, 3, ORDER_STATE_DELIVERY_COMPLETED, '신발C', 50000, 1],
                    [4, 2, 4, ORDER_STATE_DELIVERY_COMPLETED, '셔츠A', 15000, 1],
                    [5, 2, 5, ORDER_STATE_DELIVERY_COMPLETED, '셔츠B', 16000, 1],
                    [6, 2, 6, ORDER_STATE_DELIVERY_COMPLETED, '셔츠C', 17000, 1],
                    [7, 3, 7, ORDER_STATE_DELIVERY_COMPLETED, '바지A', 45000, 1],
                    [8, 3, 8, ORDER_STATE_DELIVERY_COMPLETED, '바지B', 10000, 1],
                    [9, 3, 9, ORDER_STATE_DELIVERY_COMPLETED, '바지C', 38000, 1]
                ];
                foreach ($aOrderProductList as $aOrderProduct) {
                    $sQuery = 'INSERT INTO tOrderProduct (nOrderProductSeq, nOrderSeq, nProductSeq, nOrderProductState, sProductName, nOrderProductPrice, nQuantity) 
                              VALUES (:nOrderProductSeq, :nOrderSeq, :nProductSeq, :nOrderProductState, :sProductName, :nOrderProductPrice, :nQuantity)';
                    $stmt = $sqlitePdo->prepare($sQuery);
                    $stmt->bindParam(':nOrderProductSeq', $aOrderProduct[0], PDO::PARAM_INT);
                    $stmt->bindParam(':nOrderSeq', $aOrderProduct[1], PDO::PARAM_INT);
                    $stmt->bindParam(':nProductSeq', $aOrderProduct[2], PDO::PARAM_INT);
                    $stmt->bindParam(':nOrderProductState', $aOrderProduct[3], PDO::PARAM_INT);
                    $stmt->bindParam(':sProductName', $aOrderProduct[4]);
                    $stmt->bindParam(':nOrderProductPrice', $aOrderProduct[5], PDO::PARAM_INT);
                    $stmt->bindParam(':nQuantity', $aOrderProduct[6], PDO::PARAM_INT);
                    $stmt->execute();
                }
            } catch (\Exception $e) {
                $apiResponse->setStatusCode(500);
                $apiResponse->setDesc($e->getMessage());
            }
        }

        $this->response->setHeader('content-type', 'application/json');
        $this->response->setStatusCode($apiResponse->getStatusCode());
        return $this->response->setJSON($apiResponse->toArray());
    }
}