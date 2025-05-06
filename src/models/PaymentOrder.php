<?php


namespace finance\models;

use finance\contracts\ReportInterface;
use think\facade\Log;
use think\Model;
use think\helper\Arr;

class PaymentOrder extends Model implements ReportInterface
{
    protected $name = 'finance_payment_order';


    protected $type = [
        'result' => 'array'
    ];

    public function addOrder($content, $message)
    {
        try {
            // 创建主订单
            $order = self::create([
                'mch_id' => Arr::get($message, 'mch_id', ''),
                'transaction_id' => Arr::get($message, 'transaction_id', ''),
                'out_trade_no' => Arr::get($message, 'out_trade_no', ''),
                'order_amount' => (float)Arr::get($message, 'total_fee', 0),
                'pay_success_time' => Arr::get($message, 'time_end', ''),
                'report_status' => self::REPORT_STATUS_INIT,
                'base_cost' => (float)Arr::get($content, 'base_cost', 0),
                'waste_cost' => (float)Arr::get($content, 'waste_cost', 0),
                'project' => Arr::get($content, 'project', ''),
                'data_type' => (int)Arr::get($content, 'data_type', 1),
                'order_time' => Arr::get($content, 'order_time', ''),
            ]);

            // 处理批量子订单
            if (isset($content['batch_detail']) && is_array($content['batch_detail'])) {
                $orderBranch = new PaymentOrderBranch();
                $orderBranch->addOrderBranch($order, $content['batch_detail']);
            }

            return $order;
        } catch (\Exception $e) {
            // 其他异常处理
            Log::error('创建支付订单失败', ['error' => $e->getMessage(), 'content' => $content, 'message' => $message]);
            return false;
        }
    }

    public function getBranchDetail()
    {
        return PaymentOrderBranch::where('payment_order_id', $this->id)->select();
    }


    public static function loadById($id)
    {
        return self::where('id', $id)->find();
    }


    public function reportSuccess($result)
    {
        $code = Arr::get($result, 'code', -1);
        $msg_code = Arr::get($result, 'msg_code', -1);
        if ($code == 200 && $msg_code == 100000) {
            $report_status = self::REPORT_STATUS_SUCCESS;
        } else {
            $report_status = self::REPORT_STATUS_FAIL;
        }
        return $this->save([
            'report_status' => $report_status,
            'result' => $result
        ]);
    }


}
