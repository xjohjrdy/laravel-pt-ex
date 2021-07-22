<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/05/06
 * Time: 15:19
 */

namespace App\Services\FuLu;

use GuzzleHttp\Client;

class FuLuServices
{
//    protected $url = 'https://pre-openapi.fulu.com/api/getway';                                 #测试 域名
//    protected $AppKey = 'i4esv1l+76l/7NQCL3QudG90Fq+YgVfFGJAWgT+7qO1Bm9o/adG/1iwO2qXsAXNB';     #测试 key
//    protected $AppSecret = '0a091b3aa4324435aab703142518a8f7';                                  #测试 secret
    protected $MemberCode = '9000358';                                                          #测试 暂时没用

    protected $url = 'https://openapi.fulu.com/api/getway';                                     #正式 域名
    protected $AppKey = 'uDrdHOxVgVA20AiExzUmbUA4w3p1W6g02EXqpeV7xrBNq903hnrQImM5KkJ0MHq/';     #正式 key
    protected $AppSecret = '60cbdb665b884bd9becce01644d340a1';                                  #正式 secret

    /*
     * 获取商品列表
     */
    public function getGoodsList()
    {
        $client = new Client();
        //拼接api
        $url = $this->url;
        //所需参数
        $timestamp = date('Y-m-d H:i:s');
        $biz_content = '{}';
        $post_api_data = [
            /*公共参数*/
            'app_key' => $this->AppKey,         #开放平台分配给商户的app_key
            'method' => 'fulu.goods.list.get',  #接口方法名称
            'timestamp' => $timestamp,               #时间戳，格式为：yyyy-MM-dd HH:mm:ss
            'version' => '2.0',                 #调用的接口版本
            'format' => 'json',                 #接口请求或响应格式
            'charset' => 'utf-8',               #请求使用的编码格式，如utf-8等
            'sign_type' => 'md5',               #签名加密类型，目前仅支持md5
            'app_auth_token' => '',             #授权码，固定值为“”
            'biz_content' => $biz_content,      #请求参数集合（注意：该参数是以json字符串的形式传输）
        ];

        $sign = $this->getSign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];

        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 获取商品信息
     */
    public function getGoodsInfo($product_id)
    {
        $client = new Client();
        //拼接api
        $url = $this->url;
        //所需参数
        $timestamp = date('Y-m-d H:i:s');
        $biz_content = '{"product_id": "' . $product_id . '"}';#商品编号
        $post_api_data = [
            /*公共参数*/
            'app_key' => $this->AppKey,         #开放平台分配给商户的app_key
            'method' => 'fulu.goods.info.get',  #接口方法名称
            'timestamp' => $timestamp,          #时间戳，格式为：yyyy-MM-dd HH:mm:ss
            'version' => '2.0',                 #调用的接口版本
            'format' => 'json',                 #接口请求或响应格式
            'charset' => 'utf-8',               #请求使用的编码格式，如utf-8等
            'sign_type' => 'md5',               #签名加密类型，目前仅支持md5
            'app_auth_token' => '',             #授权码，固定值为“”
            'biz_content' => $biz_content,      #请求参数集合（注意：该参数是以json字符串的形式传输）
        ];

        $sign = $this->getSign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];

        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 获取商品模板
     */
    public function getGoodsTemplate($template_id)
    {
        $client = new Client();
        //拼接api
        $url = $this->url;
        //所需参数
        $timestamp = date('Y-m-d H:i:s');
        $biz_content = '{"template_id": "' . $template_id . '"}';#商品模板编号
        $post_api_data = [
            /*公共参数*/
            'app_key' => $this->AppKey,             #开放平台分配给商户的app_key
            'method' => 'fulu.goods.template.get',  #接口方法名称
            'timestamp' => $timestamp,              #时间戳，格式为：yyyy-MM-dd HH:mm:ss
            'version' => '2.0',                     #调用的接口版本
            'format' => 'json',                     #接口请求或响应格式
            'charset' => 'utf-8',                   #请求使用的编码格式，如utf-8等
            'sign_type' => 'md5',                   #签名加密类型，目前仅支持md5
            'app_auth_token' => '',                 #授权码，固定值为“”
            'biz_content' => $biz_content,          #请求参数集合（注意：该参数是以json字符串的形式传输）
        ];

        $sign = $this->getSign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];

        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 福禄 直充下单接口
     */
    public function addDirectOrder($product_id, $customer_order_no, $charge_account, $buy_num)
    {
        $client = new Client();
        //拼接api
        $url = $this->url;
        //所需参数
        $timestamp = date('Y-m-d H:i:s');
        //商品编号 外部订单号 充值账号 购买数量
        $biz_content = '{"product_id": "' . $product_id . '","customer_order_no": "' . $customer_order_no . '","charge_account": "' . $charge_account . '","buy_num": "' . $buy_num . '"}';
        $post_api_data = [
            /*公共参数*/
            'app_key' => $this->AppKey,         #开放平台分配给商户的app_key
            'method' => 'fulu.order.direct.add',  #接口方法名称
            'timestamp' => $timestamp,          #时间戳，格式为：yyyy-MM-dd HH:mm:ss
            'version' => '2.0',                 #调用的接口版本
            'format' => 'json',                 #接口请求或响应格式
            'charset' => 'utf-8',               #请求使用的编码格式，如utf-8等
            'sign_type' => 'md5',               #签名加密类型，目前仅支持md5
            'app_auth_token' => '',             #授权码，固定值为“”
            'biz_content' => $biz_content,      #请求参数集合（注意：该参数是以json字符串的形式传输）
        ];

        $sign = $this->getSign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];

        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 福禄 卡密下单接口
     */
    public function addCardOrder($product_id, $buy_num, $customer_order_no)
    {
        $client = new Client();
        //拼接api
        $url = $this->url;
        //所需参数
        $timestamp = date('Y-m-d H:i:s');
        //商品编号 购买数量 外部订单号
        $biz_content = '{"product_id": "' . $product_id . '","buy_num": "' . $buy_num . '","customer_order_no": "' . $customer_order_no . '"}';
        $post_api_data = [
            /*公共参数*/
            'app_key' => $this->AppKey,         #开放平台分配给商户的app_key
            'method' => 'fulu.order.card.add',  #接口方法名称
            'timestamp' => $timestamp,          #时间戳，格式为：yyyy-MM-dd HH:mm:ss
            'version' => '2.0',                 #调用的接口版本
            'format' => 'json',                 #接口请求或响应格式
            'charset' => 'utf-8',               #请求使用的编码格式，如utf-8等
            'sign_type' => 'md5',               #签名加密类型，目前仅支持md5
            'app_auth_token' => '',             #授权码，固定值为“”
            'biz_content' => $biz_content,      #请求参数集合（注意：该参数是以json字符串的形式传输）
        ];

        $sign = $this->getSign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];

        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 福禄 话费下单接口
     */
    public function addMobileOrder($charge_phone, $charge_value, $customer_order_no)
    {
        $client = new Client();
        //拼接api
        $url = $this->url;
        //所需参数
        $timestamp = date('Y-m-d H:i:s');
        //充值手机号 充值金额 外部订单号
        $biz_content = '{"charge_phone": "' . $charge_phone . '","charge_value": "' . $charge_value . '","customer_order_no": "' . $customer_order_no . '"}';
        $post_api_data = [
            /*公共参数*/
            'app_key' => $this->AppKey,         #开放平台分配给商户的app_key
            'method' => 'fulu.order.mobile.add',  #接口方法名称
            'timestamp' => $timestamp,          #时间戳，格式为：yyyy-MM-dd HH:mm:ss
            'version' => '2.0',                 #调用的接口版本
            'format' => 'json',                 #接口请求或响应格式
            'charset' => 'utf-8',               #请求使用的编码格式，如utf-8等
            'sign_type' => 'md5',               #签名加密类型，目前仅支持md5
            'app_auth_token' => '',             #授权码，固定值为“”
            'biz_content' => $biz_content,      #请求参数集合（注意：该参数是以json字符串的形式传输）
        ];

        $sign = $this->getSign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];

        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 福禄 订单详情
     */
    public function getOrderInfo($customer_order_no)
    {
        $client = new Client();
        //拼接api
        $url = $this->url;
        //所需参数
        $timestamp = date('Y-m-d H:i:s');
        //充值手机号 充值金额 外部订单号
        $biz_content = '{"customer_order_no": "' . $customer_order_no . '"}';
        $post_api_data = [
            /*公共参数*/
            'app_key' => $this->AppKey,         #开放平台分配给商户的app_key
            'method' => 'fulu.order.info.get',  #接口方法名称
            'timestamp' => $timestamp,          #时间戳，格式为：yyyy-MM-dd HH:mm:ss
            'version' => '2.0',                 #调用的接口版本
            'format' => 'json',                 #接口请求或响应格式
            'charset' => 'utf-8',               #请求使用的编码格式，如utf-8等
            'sign_type' => 'md5',               #签名加密类型，目前仅支持md5
            'app_auth_token' => '',             #授权码，固定值为“”
            'biz_content' => $biz_content,      #请求参数集合（注意：该参数是以json字符串的形式传输）
        ];

        $sign = $this->getSign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];

        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 生成签名
     */
    public function getSign($Parameters)
    {
        //签名步骤一：把字典json序列化
        $json = json_encode($Parameters, 320);
        //签名步骤二：转化为数组
        $jsonArr = $this->mb_str_split($json);
        //签名步骤三：排序
        sort($jsonArr);
        //签名步骤四：转化为字符串
        $string = implode('', $jsonArr);
        //签名步骤五：在string后加入secret
        $string = $string . $this->AppSecret;
        //签名步骤六：MD5加密
        $result_ = strtolower(md5($string));
        return $result_;
    }

    /*
     * 可将字符串中中文拆分成字符数组
     */
    function mb_str_split($str)
    {
        return preg_split('/(?<!^)(?!$)/u', $str);
    }

    public function decode($enpass)
    {
        $encryptString = base64_decode($enpass);
        $decryptedpass = rtrim(openssl_decrypt($encryptString, 'aes-256-ecb', $this->AppSecret, OPENSSL_RAW_DATA));
        return trim($decryptedpass);
    }
}