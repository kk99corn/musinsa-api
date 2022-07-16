<?php

namespace App\Libraries\mds\model;

/**
 * 주문상품
 */
class OrderProduct
{
    /**
     * 주문상품번호
     * @var int
     */
    private int $orderProductSeq;

    /**
     * 주문번호
     * @var int
     */
    private int $orderSeq;

    /**
     * 주문상품상태
     * @var int
     */
    private int $orderProductState;

    /**
     * 주문상품번호
     * @var int
     */
    private int $productSeq;

    /**
     * 주문상품명
     * @var string
     */
    private string $productName;

    /**
     * 주문상품가격
     * @var int
     */
    private int $orderProductPrice;

    /**
     * 주문수량
     * @var int
     */
    private int $quantity;

    /**
     * @param int $orderProductSeq
     * @param int $orderSeq
     * @param int $orderProductState
     * @param int $productSeq
     * @param string $productName
     * @param int $orderProductPrice
     * @param int $quantity
     */
    public function __construct(int $orderProductSeq, int $orderSeq, int $orderProductState, int $productSeq, string $productName, int $orderProductPrice, int $quantity)
    {
        $this->orderProductSeq = $orderProductSeq;
        $this->orderSeq = $orderSeq;
        $this->orderProductState = $orderProductState;
        $this->productSeq = $productSeq;
        $this->productName = $productName;
        $this->orderProductPrice = $orderProductPrice;
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getOrderProductSeq(): int
    {
        return $this->orderProductSeq;
    }

    /**
     * @param int $orderProductSeq
     */
    public function setOrderProductSeq(int $orderProductSeq): void
    {
        $this->orderProductSeq = $orderProductSeq;
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
     * @return int
     */
    public function getOrderProductState(): int
    {
        return $this->orderProductState;
    }

    /**
     * @param int $orderProductState
     */
    public function setOrderProductState(int $orderProductState): void
    {
        $this->orderProductState = $orderProductState;
    }

    /**
     * @return int
     */
    public function getProductSeq(): int
    {
        return $this->productSeq;
    }

    /**
     * @param int $productSeq
     */
    public function setProductSeq(int $productSeq): void
    {
        $this->productSeq = $productSeq;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     */
    public function setProductName(string $productName): void
    {
        $this->productName = $productName;
    }

    /**
     * @return int
     */
    public function getOrderProductPrice(): int
    {
        return $this->orderProductPrice;
    }

    /**
     * @param int $orderProductPrice
     */
    public function setOrderProductPrice(int $orderProductPrice): void
    {
        $this->orderProductPrice = $orderProductPrice;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}