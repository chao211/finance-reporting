## 财务数据上报

[thinkphp6.0的demo下载](https://gitee.com/thans/jwt-auth/attach_files/306748/download)

## 环境要求

1. php ^7.0 || ^8.0
2. thinkphp ^6.0.0

## 安装

第一步:

```shell
$ composer require finance/report
```

第二步:

```shell
$ php think finance:report init
```

第三步:

```shell
$ php think migrate:run
```

执行数据迁移命令,生成三个数据表,finance_payment_order,finance_payment_order_branch,finance_refund_order

## 使用方式

示例：

```php
use finance\facade\Finance;

//保存支付订单数据
$result = Finance::addPaymentOrder($content, $message);//content参数为自行填写,message参数为微信支付成功回调的数据
//content 数据需包含:
// project 项目名称 最大长度 55 位字符串,
// order_time 下单时间,
// base_cost 基础成本金额(分),
//waste_cost 耗损成本金额(分),
//data_type 数据类型(1 单笔付款 2 批量付款 3 批量补单,不传该字段默认为1进行处理),
// batch_detail 合并支付详情	(data_type值为2或3时必传,具体描述参照下方字段说明),

//message 数据需包含:
//mch_id 微信商户号,
//transaction_id 微信交易号,
//out_trade_no 商户订单号,
//total_fee 交易金额(分),
//time_end 交易时间
//保存退款订单数据
$result = Finance::addRefundOrder($content, $message);//content参数为自行填写,message参数为微信退款成功回调的数据
//content 数据需包含:
// mch_id 商户号,
// project 项目名称 最大长度 55 位字符串,
// refund_time 退款时间,

//message 数据需包含:
//transaction_id 微信交易号,
//out_trade_no 商户订单号,
//refund_id 微信退款单号,
//refund_fee 退款金额(分)
//success_time 退款成功时间

```

上传命令：

```shell
$ php think finance:report payment  //执行支付订单上传
```

```shell
$ php think finance:report refund  //执行退款订单上传
```

## License

MIT
