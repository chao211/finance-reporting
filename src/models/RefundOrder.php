<?php


namespace finance\models;

use finance\contracts\ReportInterface;
use think\Exception;
use think\facade\Log;
use think\helper\Arr;
use think\Model;

class RefundOrder extends Model implements ReportInterface
{
    protected $name = 'finance_refund_order';
    protected $type = [
        'result' => 'array'
    ];


    public function addOrder($content, $message)
    {
        try {
            $orderData = [
                'mch_id' => Arr::get($content, 'mch_id'),
                'project' => Arr::get($content, 'project'),
                'refund_time' => Arr::get($content, 'refund_time'),
                'transaction_id' => Arr::get($message, 'transaction_id'),
                'out_trade_no' => Arr::get($message, 'out_trade_no'),
                'refund_no' => Arr::get($message, 'refund_id'),
                'refund_amount' => Arr::get($message, 'refund_fee'),
                'refund_success_time' => strtotime(Arr::get($message, 'success_time')),
            ];
            // 提取数据并创建订单
            $order = self::where('transaction_id', $message['transaction_id'])->find();
            if (empty($order)) {
                // 创建主订单
                $order = self::create($orderData);
            } else {
                $orderData['report_status'] = self::REPORT_STATUS_INIT;
                $order->save($orderData);
            }
            return $order;
        } catch (Exception $e) {
            throw $e;
            // 记录详细日志信息
            Log::error('创建支付订单失败', [
                'error_message' => $e->getMessage(),
                'content' => $content,
                'message' => $message,
            ]);
            return false;
        }
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
