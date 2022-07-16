<?php

namespace App\Libraries\mds\model;

/**
 * 주문
 */
class Order
{
    /**
     * 주문번호
     * @var int
     */
    private int $orderSeq;

    /**
     * 주문일자
     * @var String
     */
    private string $createDate;

    /**
     * 주문가격
     * @var int
     */
    private int $price;

    /**
     * 배송비
     * @var int
     */
    private int $deliveryPrice;

    /**
     * 배송방법
     * @var int
     */
    private int $deliveryMethod;

    /**
     * 주문회원번호
     * @var int
     */
    private int $memberSeq;

    /**
     * 주문상품리스트
     * @var array
     */
    private array $orderProductList;

    /**
     * @param int $orderSeq
     * @param string $createDate
     * @param int $price
     * @param int $deliveryPrice
     * @param int $deliveryMethod
     * @param int $memberSeq
     * @param array $orderProductList
     */
    public function __construct(int $orderSeq, string $createDate, int $price, int $deliveryPrice, int $deliveryMethod, int $memberSeq, array $orderProductList)
    {
        $this->orderSeq = $orderSeq;
        $this->createDate = $createDate;
        $this->price = $price;
        $this->deliveryPrice = $deliveryPrice;
        $this->deliveryMethod = $deliveryMethod;
        $this->memberSeq = $memberSeq;
        $this->orderProductList = $orderProductList;
    }

    /**
     * @return int
     */
    public function getOrderSeq(): int
    {
        return $this->orderSeq;
    }

    /**
     * @param int $orderSeq
     */
    public function setOrderSeq(int $orderSeq): void
    {
        $this->orderSeq = $orderSeq;
    }

    /**
     * @return String
     */
    public function getCreateDate(): string
    {
        return $this->createDate;
    }

    /**
     * @param String $createDate
     */
    public function setCreateDate(string $createDate): void
    {
        $this->createDate = $createDate;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getDeliveryPrice(): int
    {
        return $this->deliveryPrice;
    }

    /**
     * @param int $deliveryPrice
     */
    public function setDeliveryPrice(int $deliveryPrice): void
    {
        $this->deliveryPrice = $deliveryPrice;
    }

    /**
     * @return int
     */
    public function getDeliveryMethod(): int
    {
        return $this->deliveryMethod;
    }

    /**
     * @param int $deliveryMethod
     */
    public function setDeliveryMethod(int $deliveryMethod): void
    {
        $this->deliveryMethod = $deliveryMethod;
    }

    /**
     * @return int
     */
    public function getMemberSeq(): int
    {
        return $this->memberSeq;
    }

    /**
     * @param int $memberSeq
     */
    public function setMemberSeq(int $memberSeq): void
    {
        $this->memberSeq = $memberSeq;
    }

    /**
     * @return array
     */
    public function getOrderProductList(): array
    {
        return $this->orderProductList;
    }

    /**
     * @param array $orderProductList
     */
    public function setOrderProductList(array $orderProductList): void
    {
        $this->orderProductList = $orderProductList;
    }
}