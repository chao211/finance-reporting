<?php

namespace finance\models;

use think\Model;
use think\helper\Arr;

class PaymentOrderBranch extends Model
{


    protected $name = 'payment_order_branch';


    public function addOrderBranch(PaymentOrder $order, $list)
    {
        $insertData = array_map(function ($item) use ($order) {
            return [
                'paymnet_order_id' => $order->id,
                'order_no' => Arr::get($item, 'order_no'),
                'order_amount' => Arr::get($item, 'order_amount'),
            ];
        }, $list);

        $this->saveAll($insertData);
    }


}