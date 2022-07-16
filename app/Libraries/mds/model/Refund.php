<?php

namespace App\Libraries\mds\model;

/**
 * 환불정보
 */
class Refund
{
    /**
     * 환불정보번호
     * @var int
     */
    private int $refundSeq;

    /**
     * 주문번호
     * @var int
     */
    private int $orderSeq;

    /**
     * 요청일자
     * @var string
     */
    private string $requestDate;

    /**
     * 처리일자
     * @var ?string
     */
    private ?string $completedDate;

    /**
     * 환불방법(교환/반품)
     * @var int
     */
    private int $refundMethod;

    /**
     * @var int
     */
    private int $refundState;

    /**
     * 환불금액
     * @var int
     */
    private int $refundPrice;

    /**
     * 환불요청 주문상품번호 리스트
     * @var string
     */
    private string $orderProductSeqList;

    /**
     * Refund constructor.
     * @param int $refundSeq
     * @param int $orderSeq
     * @param string $requestDate
     * @param ?string $completedDate
     * @param int $refundMethod
     * @param int $refundState
     * @param int $refundPrice
     * @param string $orderProductSeqList
     */
    public function __construct(int $refundSeq, int $orderSeq, string $requestDate, ?string $completedDate, int $refundMethod, int $refundState, int $refundPrice, string $orderProductSeqList)
    {
        $this->refundSeq = $refundSeq;
        $this->orderSeq = $orderSeq;
        $this->requestDate = $requestDate;
        $this->completedDate = $completedDate;
        $this->refundMethod = $refundMethod;
        $this->refundState = $refundState;
        $this->refundPrice = $refundPrice;
        $this->orderProductSeqList = $orderProductSeqList;
    }

    /**
     * @return int
     */
    public function getRefundSeq(): int
    {
        return $this->refundSeq;
    }

    /**
     * @param int $refundSeq
     */
    public function setRefundSeq(int $refundSeq): void
    {
        $this->refundSeq = $refundSeq;
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
     * @return string
     */
    public function getRequestDate(): string
    {
        return $this->requestDate;
    }

    /**
     * @param string $requestDate
     */
    public function setRequestDate(string $requestDate): void
    {
        $this->requestDate = $requestDate;
    }

    /**
     * @return ?string
     */
    public function getCompletedDate(): ?string
    {
        return $this->completedDate;
    }

    /**
     * @param string $completedDate
     */
    public function setCompletedDate(string $completedDate): void
    {
        $this->completedDate = $completedDate;
    }

    /**
     * @return int
     */
    public function getRefundMethod(): int
    {
        return $this->refundMethod;
    }

    /**
     * @param int $refundMethod
     */
    public function setRefundMethod(int $refundMethod): void
    {
        $this->refundMethod = $refundMethod;
    }

    /**
     * @return int
     */
    public function getRefundState(): int
    {
        return $this->refundState;
    }

    /**
     * @param int $refundState
     */
    public function setRefundState(int $refundState): void
    {
        $this->refundState = $refundState;
    }

    /**
     * @return int
     */
    public function getRefundPrice(): int
    {
        return $this->refundPrice;
    }

    /**
     * @param int $refundPrice
     */
    public function setRefundPrice(int $refundPrice): void
    {
        $this->refundPrice = $refundPrice;
    }

    /**
     * @return string
     */
    public function getOrderProductSeqList(): string
    {
        return $this->orderProductSeqList;
    }

    /**
     * @param string $orderProductSeqList
     */
    public function setOrderProductSeqList(string $orderProductSeqList): void
    {
        $this->orderProductSeqList = $orderProductSeqList;
    }
}