<?php

namespace App\Libraries\mds\model\command;

class RefundCommand
{
    public int $memberSeq;
    public int $orderSeq;
    public array $orderProductSeqList;
    public int $refundMethodSeq;
    public int $refundPrice;
}