<?php

namespace App\Controllers;

use CodeIgniter\Router\Exceptions\RedirectException;
use CodeIgniter\Test\CIUnitTestCase;
use Exception;
use PDO;
use CodeIgniter\Test\FeatureTestTrait;

require_once 'tests/TestDataSettingFunction.php';

class OrderTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * 주문정보 조회 테스트
     * @throws RedirectException
     * @throws Exception
     */
    public function testGetOrders()
    {
        $memberSeq = 1;
        $orderSeq = 1;

        // 주문 조회
        $result = $this->skipEvents()->call('get', '/api/v1/orders', [
            'memberSeq' => $memberSeq,
            'orderSeq' => $orderSeq
        ]);

        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $resultBody = json_decode($result->response()->getBody(), true);

        $this->assertEquals(200, $resultBody['status']);
        $this->assertEquals($orderSeq, $resultBody['result'][$orderSeq]['orderSeq']);
    }

    /**
     * 주문정보 조회 테스트(필수 파라미터 없는 경우)
     * @throws RedirectException
     * @throws Exception
     */
    public function testGetOrdersByNoneParameters()
    {
        $memberSeq = null;
        $orderSeq = null;

        // 주문 조회
        $result = $this->skipEvents()->call('get', '/api/v1/orders', [
            'memberSeq' => $memberSeq,
            'orderSeq' => $orderSeq
        ]);

        $result->assertStatus(400);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $resultBody = json_decode($result->response()->getBody(), true);

        $this->assertEquals(400, $resultBody['status']);
        $this->assertEmpty($resultBody['result']);
    }
}
