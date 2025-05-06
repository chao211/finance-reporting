<?php

namespace finance\request;


use GuzzleHttp\Client;
use think\facade\Config;
use think\facade\Log;

class Request
{
    protected $request;
    protected $config;
    protected $client;

    public function __construct(\think\Request $request)
    {
        $this->request = $request;
        $this->config = Config::get('finance_config') ?? [];
        $this->client = new Client([
            'timeout' => 10,
            'headers' => [
                'Content_type' => 'application/json',
                'charset' => 'UTF-8',
            ],
        ]);
    }

    public function requestPayment($requestParams = [])
    {
        $url = $this->getConfigValue('report.payment_url');
        return $this->request($url, $requestParams) ?: [];
    }

    public function requestRefund($requestParams = [])
    {
        $url = $this->getConfigValue('report.refund_url');
        return $this->request($url, $requestParams) ?: [];
    }


    protected function request($url, $requestParams = [])
    {
        // 验证配置项是否存在
        $secret = $this->getConfigValue('report.key');

        if (empty($url) || empty($secret)) {
            Log::error("Configuration 'report_url' or 'report_key' is missing.");
            return false;
        }

        // 校验并生成签名
        if (!is_array($requestParams)) {
            Log::error("Invalid request parameters, must be an array.");
            return false;
        }
        $requestParams['sign'] = $this->generateSign($requestParams, $secret);
        try {
            // 发起 HTTP 请求
            $response = $this->client->request('post', $url, [
                'json' => $requestParams
            ]);
            // 解析响应内容
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to decode JSON response: " . $body);
                return false;
            }
            return $result;
        } catch (\Exception $e) {
            // 捕获异常并记录日志
            Log::error("Request failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取配置项值，并验证其存在性
     *
     * @param string $key 配置项键名
     * @return mixed 配置项值
     */
    private function getConfigValue($key)
    {
        if (!isset($this->config[$key])) {
            Log::error("Missing configuration key: $key");
            return false;
        }
        return $this->config[$key];
    }

    /**
     * 生成签名
     *
     * @param array $params 请求参数
     * @param string $secret 签名密钥
     * @return string 签名值
     */
    private function generateSign(array $params, $secret)
    {
        // 假设签名逻辑为简单排序后拼接密钥并计算 MD5 值
        ksort($params);
        //& 连接参数，为空的不参与签名
        $signStr = '';
        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $signStr .= $key . '=' . $value . '&';
        }
        //拼接key
        $signStr .= 'key=' . $secret;
        //MD5加密、转为大写
        return strtoupper(md5($signStr));
    }


}