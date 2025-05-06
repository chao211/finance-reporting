<?php

namespace finance;

use finance\contracts\FinanceInterface;
use finance\models\PaymentOrder;
use finance\models\RefundOrder;
use think\facade\Log;

class Finance implements FinanceInterface
{

    /**
     * 添加支付订单
     *
     * 该方法用于处理支付订单的添加操作它需要两个数组作为输入，分别包含订单内容和支付信息
     * 方法首先会验证输入是否为数组，然后检查数组中是否包含所有必需的字段如果验证通过，
     * 它将尝试将支付订单添加到数据库中如果在任何步骤中遇到问题，它将记录错误并返回false
     *
     * @param array $content 包含订单内容的数组，如项目和订单时间
     * @param array $message 包含支付信息的数组，如商户ID、交易ID、总费用等
     *
     * @return bool 添加支付订单成功或失败
     */
    public function addPaymentOrder($content, $message)
    {
        // 验证输入是否为数组
        if (!is_array($content)) {
            Log::error("Invalid input: content is not an array. Input: " . json_encode($content, JSON_UNESCAPED_UNICODE));
            return false;
        }
        if (!is_array($message)) {
            Log::error("Invalid input: message is not an array. Input: " . json_encode($message, JSON_UNESCAPED_UNICODE));
            return false;
        }

        // 验证 $content 中的必要字段
        $requiredContentFields = ['project', 'order_time'];
        foreach ($requiredContentFields as $field) {
            if (!isset($content[$field]) || empty($content[$field])) {
                Log::error("Missing or empty required field in content: $field. Content: " . json_encode($content, JSON_UNESCAPED_UNICODE));
                return false;
            }
        }

        // 验证 $message 中的必要字段
        $requiredMessageFields = ['mch_id', 'transaction_id', 'out_trade_no', 'total_fee', 'time_end'];
        foreach ($requiredMessageFields as $field) {
            if (!isset($message[$field]) || empty($message[$field])) {
                Log::error("Missing or empty required field in message: $field. Message: " . json_encode($message, JSON_UNESCAPED_UNICODE));
                return false;
            }
        }

        // 尝试添加支付订单
        try {
            $model = new PaymentOrder();
            $model->addOrder($content, $message);
        } catch (\Exception $e) {
            Log::error("Error occurred while adding payment order: " . $e->getMessage());
            return false;
        }

        return true; // 示例返回值
    }


    /**
     * 根据订单ID获取支付订单信息
     *
     * 该方法通过订单ID加载支付订单的详细信息它使用了PaymentOrder类中的loadById静态方法
     * 来实现订单信息的获取这个方法主要用于需要根据特定的订单ID获取支付订单详情的场景
     *
     * @param int $orderId 订单ID，用于标识特定的支付订单
     * @return PaymentOrder|null 返回PaymentOrder对象，如果找不到对应的订单则返回null
     */
    public function getPaymentOrder($orderId)
    {
        return PaymentOrder::loadById($orderId);
    }


    /**
     * 添加退款订单
     *
     * 该方法用于处理退款订单的创建过程，需要接收包含商家信息和退款消息的数组作为参数
     * 它首先验证输入数据的格式和完整性，然后尝试将这些数据添加到退款订单中
     *
     * @param array $content 包含商家信息的数组，包括mch_id, project等关键信息
     * @param array $message 包含退款消息的数组，如transaction_id, out_trade_no等关键信息
     *
     * @return bool 如果订单成功添加，则返回true；否则返回false
     */
    public function addRefundOrder($content, $message)
    {
        // 验证输入是否为数组
        if (!is_array($content)) {
            Log::error("Invalid input: content is not an array. Input: " . json_encode($content, JSON_UNESCAPED_UNICODE));
            return false;
        }
        if (!is_array($message)) {
            Log::error("Invalid input: message is not an array. Input: " . json_encode($message, JSON_UNESCAPED_UNICODE));
            return false;
        }

        // 验证 $content 中的必要字段
        $requiredContentFields = ['mch_id', 'project', 'refund_time'];
        foreach ($requiredContentFields as $field) {
            if (!isset($content[$field]) || empty($content[$field])) {
                Log::error("Missing or empty required field in content: $field. Content: " . json_encode($content, JSON_UNESCAPED_UNICODE));
                return false;
            }
        }

        // 验证 $message 中的必要字段
        $requiredMessageFields = ['transaction_id', 'out_trade_no', 'refund_id', 'refund_fee', 'success_time'];
        foreach ($requiredMessageFields as $field) {
            if (!isset($message[$field]) || empty($message[$field])) {
                Log::error("Missing or empty required field in message: $field. Message: " . json_encode($message, JSON_UNESCAPED_UNICODE));
                return false;
            }
        }

        // 尝试添加退款订单
        try {
            $model = new RefundOrder();
            $order = $model->addOrder($content, $message);
            if (!$order) {
                Log::error("Failed to create refund order", ['content' => $content, 'message' => $message]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error occurred while adding refund order: " . $e->getMessage(), ['content' => $content, 'message' => $message]);
            return false;
        }

        return true; // 示例返回值
    }


    /**
     * 获取退款订单信息
     *
     * 该方法通过订单ID加载并返回相应的退款订单对象
     * 主要用于在给定订单ID的情况下，获取与该订单相关的退款信息
     *
     * @param int $orderId 订单ID，用于标识特定的订单
     * @return RefundOrder|false 返回RefundOrder对象，如果找不到对应的订单则返回false
     */
    public function getRefundOrder($orderId)
    {
        // 实现逻辑：通过订单ID加载退款订单
        return RefundOrder::loadById($orderId);
    }


}
