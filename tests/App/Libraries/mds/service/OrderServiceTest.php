<?php

namespace App\Libraries\mds\service;

use App\Libraries\mds\dao\OrderDao;
use App\Libraries\mds\model\command\OrderCommand;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use PDO;

require_once 'tests/TestDataSettingFunction.php';

class OrderServiceTest extends CIUnitTestCase
{
    private OrderService $orderService;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $pdo = new PDO('sqlite::memory:');
        setMemorySqliteTestData($pdo);

        $orderDao = OrderDao::getInstance();
        $orderDao->setPdo($pdo);
        $this->orderService = OrderService::getInstance();
        $this->orderService->setOrderDao($orderDao);
    }

    /**
     * 주문조회 테스트
     * 필수 파라미터 없는 경우 -> InvalidArgumentException 발생
     */
    public function testSelectOrdersByNoneParameters()
    {
        $this->expectException(InvalidArgumentException::class);

        $orderCommand = new OrderCommand();
        $result = $this->orderService->selectOrders($orderCommand);
    }

    /**
     * 주문조회 테스트
     * memberSeq만 사용하는 경우
     */
    public function testSelectOrdersByMemberSeq()
    {
        $orderCommand = new OrderCommand();
        $orderCommand->memberSeq = 1;

        $result = $this->orderService->selectOrders($orderCommand);

        $this->assertCount(3, $result);
    }

    /**
     * 주문조회 테스트
     * memberSeq, orderSeq 사용하는 경우
     */
    public function testSelectOrdersByMemberSeqAndOrderSeq()
    {
        $orderCommand = new OrderCommand();
        $orderCommand->memberSeq = 1;
        $orderCommand->orderSeq = 1;

        $result = $this->orderService->selectOrders($orderCommand);

        $this->assertCount(1, $result);
    }
}