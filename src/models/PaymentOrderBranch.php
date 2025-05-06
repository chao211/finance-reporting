<?php

namespace finance\models;

use think\Model;
use think\helper\Arr;

class PaymentOrderBranch extends Model
{


    protected $name = 'finance_payment_order_branch';


    public function addOrderBranch(PaymentOrder $order, $list)
    {
        foreach ($list as $item) {
            $detail = $this->where('order_no', $item['platform_order_no'])
                ->where('payment_order_id', $order->id)
                ->find();
            if ($detail) {
                $detail->save([
                    'order_amount' => Arr::get($item, 'total_amount'),
                ]);
            } else {
                $this->save([
                    'payment_order_id' => $order->id,
                    'order_no' => Arr::get($item, 'platform_order_no'),
                    'order_amount' => Arr::get($item, 'total_amount'),
                ]);
            }
        }
        return true;
    }


}