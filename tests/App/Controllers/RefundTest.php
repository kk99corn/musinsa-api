<?php

namespace App\Controllers;

use CodeIgniter\Router\Exceptions\RedirectException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Exception;

class RefundTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * @throws RedirectException
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // 테스트 후 데이터 초기화
        // $this->skipEvents()->call('post', '/api/v1/dataInit');
    }

    /**
     * 반품비 예상 금액 조회 테스트(교환)
     * @throws RedirectException
     * @throws Exception
     */
    public function testGetRefundExpectationExchange()
    {
        $memberSeq = 1;
        $orderSeq = 1;
        $orderProductSeqList = '1,3';
        $refundMethodSeq = 1;

        // 주문 조회
        $result = $this->skipEvents()->call('get', '/api/v1/refund/expectation', [
            'memberSeq' => $memberSeq,
            'orderSeq' => $orderSeq,
            'orderProductSeqList' => $orderProductSeqList,
            'refundMethodSeq' => $refundMethodSeq
        ]);

        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $resultBody = json_decode($result->response()->getBody(), true);

        $this->assertEquals(200, $resultBody['status']);
        $this->assertEquals(10000, $resultBody['result']['refundPrice']);
        $this->assertTrue($resultBody['result']['isRefundAvailable']);
    }

    /**
     * 반품비 예상 금액 조회 테스트(교환) - 필수 파라미터 없는 경우
     * @throws RedirectException
     * @throws Exception
     */
    public function testGetRefundExpectationExchangeByNoneParameters()
    {
        $memberSeq = null;
        $orderSeq = null;
        $orderProductSeqList = '1,3';
        $refundMethodSeq = 1;

        // 주문 조회
        $result = $this->skipEvents()->call('get', '/api/v1/refund/expectation', [
            'memberSeq' => $memberSeq,
            'orderSeq' => $orderSeq,
            'orderProductSeqList' => $orderProductSeqList,
            'refundMethodSeq' => $refundMethodSeq
        ]);

        $result->assertStatus(400);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $resultBody = json_decode($result->response()->getBody(), true);

        $this->assertEquals(400, $resultBody['status']);
        $this->assertEmpty($resultBody['result']);
    }

    /**
     * 반품비 예상 금액 조회 테스트(환불)
     * @throws RedirectException
     * @throws Exception
     */
    public function testGetRefundExpectationReturn()
    {
        $memberSeq = 1;
        $orderSeq = 1;
        $orderProductSeqList = '1';
        $refundMethodSeq = 2;

        // 주문 조회
        $result = $this->skipEvents()->call('get', '/api/v1/refund/expectation', [
            'memberSeq' => $memberSeq,
            'orderSeq' => $orderSeq,
            'orderProductSeqList' => $orderProductSeqList,
            'refundMethodSeq' => $refundMethodSeq
        ]);

        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $resultBody = json_decode($result->response()->getBody(), true);

        $this->assertTrue($resultBody['status'] === 200);
        $this->assertTrue($resultBody['result']['isRefundAvailable'] === true);
        $this->assertTrue($resultBody['result']['refundPrice'] === 3000);

        $this->assertEquals(200, $resultBody['status']);
        $this->assertEquals(3000, $resultBody['result']['refundPrice']);
        $this->assertTrue($resultBody['result']['isRefundAvailable']);
    }

    /**
     * 반품비 예상 금액 조회 테스트(환불) - 필수 파라미터 없는 경우
     * @throws RedirectException
     * @throws Exception
     */
    public function testGetRefundExpectationReturnByNoneParameters()
    {
        $memberSeq = null;
        $orderSeq = null;
        $orderProductSeqList = '1';
        $refundMethodSeq = 2;

        // 주문 조회
        $result = $this->skipEvents()->call('get', '/api/v1/refund/expectation', [
            'memberSeq' => $memberSeq,
            'orderSeq' => $orderSeq,
            'orderProductSeqList' => $orderProductSeqList,
            'refundMethodSeq' => $refundMethodSeq
        ]);

        $result->assertStatus(400);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $resultBody = json_decode($result->response()->getBody(), true);

        $this->assertEquals(400, $resultBody['status']);
        $this->assertEmpty($resultBody['result']);
    }

//    /**
//     * 교환 접수 테스트
//     * @throws RedirectException
//     * @throws Exception
//     */
//    public function testPostRefundExchange()
//    {
//        $memberSeq = 1;
//        $orderSeq = 1;
//        $orderProductSeqList = '1,3';
//
//        // 주문 조회
//        $result = $this->skipEvents()->call('post', '/api/v1/refund/exchange', [
//            'memberSeq' => $memberSeq,
//            'orderSeq' => $orderSeq,
//            'orderProductSeqList' => $orderProductSeqList
//        ]);
//
//        $result->assertStatus(201);
//        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
//
//        $resultBody = json_decode($result->response()->getBody(), true);
//
//        $this->assertTrue($resultBody['status'] === 201);
//        $this->assertTrue($resultBody['result']['isRefundAvailable'] === true);
//        $this->assertTrue($resultBody['result']['refundPrice'] === 10000);
//    }

//    /**
//     * 환불 접수 테스트
//     * @throws RedirectException
//     * @throws Exception
//     */
//    public function testPostRefundReturn()
//    {
//        $memberSeq = 1;
//        $orderSeq = 3;
//        $orderProductSeqList = '7,8,9';
//
//        // 주문 조회
//        $result = $this->skipEvents()->call('post', '/api/v1/refund/return', [
//            'memberSeq' => $memberSeq,
//            'orderSeq' => $orderSeq,
//            'orderProductSeqList' => $orderProductSeqList
//        ]);
//
//        $result->assertStatus(201);
//        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
//
//        $resultBody = json_decode($result->response()->getBody(), true);
//
//        $this->assertTrue($resultBody['status'] === 201);
//        $this->assertTrue($resultBody['result']['isRefundAvailable'] === true);
//        $this->assertTrue($resultBody['result']['refundPrice'] === 10000);
//    }
}
