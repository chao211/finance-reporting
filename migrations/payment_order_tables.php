<?php

use think\migration\Migrator;
use think\migration\db\Column;

class PaymentOrderTables extends Migrator
{
    protected $paymentOrder = 'finance_payment_order';
    protected $refundOrder = 'finance_refund_order';
    protected $paymentOrderBranch = 'finance_payment_order_branch';

    public function up()
    {
        if (!$this->hasTable($this->paymentOrder)) {
            $table = $this->table($this->paymentOrder, ['engine' => 'InnoDB', 'comment' => '支付订单列表']);
            $table->addColumn(Column::integer('mch_id')->setComment('商户号'))
                ->addColumn(Column::string('transaction_id', 60)->setComment('交易号'))
                ->addColumn(Column::string('out_trade_no', 60)->setComment('订单号'))
                ->addColumn(Column::integer('order_amount')->setComment('订单金额(分)'))
                ->addColumn(Column::integer('base_cost')->setDefault(0)->setComment('基础成本(分)'))
                ->addColumn(Column::integer('waste_cost')->setDefault(0)->setComment('损耗成本(分)'))
                ->addColumn(Column::string('project', 100)->setComment('项目名称'))
                ->addColumn(Column::integer('order_time')->setComment('下单时间'))
                ->addColumn(Column::integer('pay_success_time')->setComment('支付时间'))
                ->addColumn(Column::tinyInteger('data_type')->setDefault(1)->setComment('1 单笔付款 2 批量付款 3 批量补单'))
                ->addColumn(Column::tinyInteger('report_status')->setDefault(0)->setComment('上报状态'))
                ->addColumn(Column::text('result')->setComment('上报结果'))
                ->addColumn(Column::integer('create_time')->setComment('记录添加时间'))
                ->addColumn(Column::integer('update_time')->setComment('记录修改时间'))
                ->addIndex('transaction_id', ['unique' => true])
                ->addIndex('mch_id')
                ->addIndex('project')
                ->addIndex('order_time')
                ->addIndex('pay_success_time')
                ->addIndex('report_status')
                ->create();
        }
        if (!$this->hasTable($this->paymentOrderBranch)) {
            $table = $this->table($this->paymentOrderBranch, ['engine' => 'InnoDB', 'comment' => '支付订单分表']);
            $table->addColumn(Column::integer('payment_order_id')->setComment('支付订单id'))
                ->addColumn(Column::string('order_no', 60)->setComment('商户订单号'))
                ->addColumn(Column::integer('order_amount')->setComment('订单金额(分)'))
                ->addIndex('payment_order_id')
                ->create();
        }
        if (!$this->hasTable($this->refundOrder)) {
            $table = $this->table($this->refundOrder, ['engine' => 'InnoDB', 'comment' => '退款订单列表']);
            $table->addColumn(Column::integer('mch_id')->setComment('商户号'))
                ->addColumn(Column::string('transaction_id', 60)->setComment('交易号'))
                ->addColumn(Column::string('refund_no', 60)->setComment('退款单号'))
                ->addColumn(Column::string('out_trade_no', 60)->setComment('订单号'))
                ->addColumn(Column::integer('refund_amount')->setComment('退款金额(分)'))
                ->addColumn(Column::string('project', 100)->setComment('项目名称'))
                ->addColumn(Column::integer('refund_time')->setComment('下单时间'))
                ->addColumn(Column::integer('refund_success_time')->setComment('支付时间'))
                ->addColumn(Column::tinyInteger('report_status')->setDefault(0)->setComment('上报状态'))
                ->addColumn(Column::text('result')->setComment('上报结果'))
                ->addColumn(Column::integer('create_time')->setComment('记录添加时间'))
                ->addColumn(Column::integer('update_time')->setComment('记录修改时间'))
                ->addIndex('transaction_id', ['unique' => true])
                ->addIndex('mch_id')
                ->addIndex('project')
                ->addIndex('refund_time')
                ->addIndex('refund_success_time')
                ->addIndex('report_status')
                ->create();
        }
    }

    public function down()
    {
        if ($this->hasTable($this->paymentOrder)) {
            $this->table($this->paymentOrder)->drop()->save();
        }
        if ($this->hasTable($this->paymentOrderBranch)) {
            $this->table($this->paymentOrderBranch)->drop()->save();
        }
        if ($this->hasTable($this->refundOrder)) {
            $this->table($this->refundOrder)->drop()->save();
        }
    }

}
