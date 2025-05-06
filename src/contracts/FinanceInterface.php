<?php

namespace finance\contracts;


interface FinanceInterface
{

    public function addPaymentOrder($content, $message);

    public function getPaymentOrder($order_no);

    public function getRefundOrder($orderId);

    public function addRefundOrder($content, $message);

}



