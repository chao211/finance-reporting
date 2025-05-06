<?php


namespace finance\commands;

use finance\models\PaymentOrder;
use finance\models\RefundOrder;
use finance\request\Request;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class ReportCommand extends Command
{

    protected function configure()
    {
        $this->setName('finance:report')
            ->addArgument('method', Argument::OPTIONAL)
            ->setDescription('财务数据上报,payment,refund')
            ->addUsage('init')
            ->addUsage('payment')
            ->addUsage('refund');
    }

    protected function execute(Input $input, Output $output)
    {
        $method = $input->getArgument('method');
        if ($method) {
            echo '调用方法' . $method . PHP_EOL;
            return $this->$method();
        }
        return '请输入要调用的方法名' . PHP_EOL;
    }

    public function payment()
    {
        $request = new Request($this->app->request);
        PaymentOrder::where('report_status', PaymentOrder::REPORT_STATUS_INIT)
            ->chunk(100, function ($list) use ($request) {
                /** @var PaymentOrder $item */
                foreach ($list as $item) {
                    $result = $request->requestPayment([
                        'wechat_merchant_no' => $item->mch_id,
                        'wechat_order_no' => $item->transaction_id,
                        'data_type' => $item->data_type,
                        'platform_order_no' => $item->out_trade_no,
                        'batch_detail' => array_map(function ($branch) {
                            return [
                                'platform_order_no' => $branch['order_no'],
                                'order_amount' => $branch['order_amount']
                            ];
                        }, $item->getBranchDetail()->toArray()),
                        'order_amount' => $item->order_amount,
                        'base_cost_amount' => $item->base_cost,
                        'waste_cost_amount' => $item->waste_cost,
                        'project' => $item->project,
                        'order_time' => $item->order_time,
                        'pay_success_time' => $item->pay_success_time,
                    ]);
                    $item->reportSuccess($result);
                }
            });
        echo 'payment order reported successfully';
    }


    public function refund()
    {
        $request = new Request($this->app->request);
        RefundOrder::where('report_status', RefundOrder::REPORT_STATUS_INIT)
            ->chunk(100, function ($list) use ($request) {
                /** @var RefundOrder $item */
                foreach ($list as $item) {
                    $result = $request->requestRefund([
                        'wechat_merchant_no' => $item->mch_id,
                        'wechat_order_no' => $item->transaction_id,
                        'wechat_refund_no' => $item->refund_no,
                        'platform_order_no' => $item->out_trade_no,
                        'refund_amount' => $item->refund_amount,
                        'project' => $item->project,
                        'refund_time' => $item->refund_time,
                        'refund_success_time' => $item->refund_success_time,
                    ]);
                    $item->reportSuccess($result);
                }
            });
        echo 'refund order reported successfully';
    }


    protected function init()
    {
        $dir = $this->getApp()->getRootPath() . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            $this->output->error(
                sprintf(
                    '系统依赖数据迁移,请先确保迁移命令已在您的系统'
                )
            );

            return;
        }
        foreach (glob(__DIR__ . '/../../migrations/*.php') as $source) {
            $dest = $dir . date('YmdHis') . '_' . basename($source);
            //printf('将会把文件"%s"复制到"%s"'.PHP_EOL, $source, $dest);
            $pattern = $dir . '*_' . basename($source);
            $copy = true;
            foreach (glob($pattern) as $exist_dest) {
                $this->output->warning(sprintf(
                    '目标目录存在相似文件"%s",系统将会忽略', basename($exist_dest)
                ));
                $copy = false;
                break;
            }
            if ($copy) {
                copy($source, $dest);
            }
        }
    }


}
