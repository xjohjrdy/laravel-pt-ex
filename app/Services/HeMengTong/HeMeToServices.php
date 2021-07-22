<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/03/18
 * Time: 10:02
 */

namespace App\Services\HeMengTong;

use App\Services\Common\CommonFunction;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Logger;

class HeMeToServices
{
//    protected $fkey = 'Z1IUbWoXFcN50aWoZOF7iLYk9MP1n3ke';        #测试 签名秘钥
//    protected $fzgyno = '1911041041220205';                      #测试 商户号

    protected $fkey = 'HYxC6qyMqEZDM0h38QopjAdiiOq4gLc9';        #线上 签名秘钥
    protected $fzgyno = '2003121504106350';                      #线上 商户号

//    protected $fkey = 'S62pBNLc5TBSog36LUQg8ZVoYL6Nc2E7';        #测试 升级版 签名秘钥
//    protected $fzgyno = '2003121506317775';                      #测试 升级版 商户号


    protected $wxapp_fkey = 'dIZw7aeYpW82hVSkNeAroV98BaODqgrK';        #小程序 签名秘钥
    protected $wxapp_fzgyno = '2003131419148001';                      #小程序 商户号

    protected $getkey = 'https://api.hmtfu.com/api/okey.html';   #获取密钥地址
    protected $postapi = 'https://api.hmtfu.com/api/odata.html'; #POST接口地址
    protected $getapi = 'https://api.hmtfu.com/api/opage.html';  #GET接口地址

    /*
     * 获取支付秘钥
     */
    public function getPaykey($faccount, $fpasswd)
    {
        //获取秘钥api
        $client = new Client();
        $full_url = $this->getkey;
        //所需参数
        $post_api_data = [
            'faccount' => $faccount,      #平台营业员账号
            'fpasswd' => md5($fpasswd)    #平台营业员密码，MD5加密后的值
        ];

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * app 商城购物 支付宝支付
     */
    public function appPay($fmoney, $famount, $fapino, $fpaytype = 1)
    {
        $logger = new Logger('HttpLog');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('app/callback_document/http/' . date('Y-m-d') . '.log')), Logger::INFO);
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $logger,
                new MessageFormatter('{url} : {request} - {response}')
            )
        );

        //app支付api
        $client = new Client([
            'handler' => $stack,
        ]);
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,                                     #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'famount' => $famount,                                         #应收金额（单位元），精确到小数点后两位如：1.23
            'fapino' => uniqid('ahmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/he_meng_tong_pay_call_back',#支付结果异步通知地址
//            'furl' => 'http://cc.k5.hk:28080/bing_notify',                 #测试 支付结果异步通知地址
            'fpaytype' => $fpaytype,                                       #支付类型1=支付宝 2=微信
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '010';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * app 商城购物 微信支付 返回加密字符串
     */
    public function appWxPay($fmoney, $fapino, $app_id)
    {
        //所需参数
        $post_api_data = [
            'fno' => $this->fzgyno,                                        #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fkey' => $this->fkey,                                         #开发者秘钥
            'fapino' => uniqid('whmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/he_meng_tong_pay_call_back',#支付结果异步通知地址
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
            'fname' => '葡萄浏览器',                                       #名称
        ];
        $data = http_build_query($post_api_data);
        $data_all = 'pages/index/index?' . $data;
        $sign_data = $this->aesEncode($data_all, md5($app_id));
        return $sign_data;
    }

    /*
     * app 圈子发红包 支付宝支付
     */
    public function appPayCircleSend($fmoney, $famount, $fapino, $fpaytype = 1)
    {
        //app支付api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,                                     #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'famount' => $famount,                                         #应收金额（单位元），精确到小数点后两位如：1.23
            'fapino' => uniqid('ahmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/api/he_meng_tong_circle_send_call_back',#支付结果异步通知地址
//            'furl' => 'http://cc.k5.hk:28080/bing_notify',                 #测试 支付结果异步通知地址
            'fpaytype' => $fpaytype,                                       #支付类型1=支付宝 2=微信
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '010';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * app 圈子发红包 微信支付 返回加密字符串
     */
    public function appWxPayCircleSend($fmoney, $fapino, $app_id)
    {
        //所需参数
        $post_api_data = [
            'fno' => $this->fzgyno,                                        #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fkey' => $this->fkey,                                         #开发者秘钥
            'fapino' => uniqid('whmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/api/he_meng_tong_circle_send_call_back',#支付结果异步通知地址
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
            'fname' => '葡萄浏览器',                                       #名称
        ];
        $data = http_build_query($post_api_data);
        $data_all = 'pages/index/index?' . $data;
        $sign_data = $this->aesEncode($data_all, md5($app_id));
        return $sign_data;
    }

    /*
     * app 圈子购买 支付宝支付
     */
    public function appPayCircleBuy($fmoney, $famount, $fapino, $area, $fpaytype = 1)
    {
        $fapino = bin2hex($fapino . '---' . $area);

        //app支付api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,                                     #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'famount' => $famount,                                         #应收金额（单位元），精确到小数点后两位如：1.23
            'fapino' => uniqid('ahmt', true),                                     #开发者订单号
            'furl' => 'http://api.36qq.com/api/he_meng_tong_circle_buy_call_back',#支付结果异步通知地址
//            'furl' => 'http://cc.k5.hk:28080/bing_notify',                 #测试 支付结果异步通知地址
            'fpaytype' => $fpaytype,                                       #支付类型1=支付宝 2=微信
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fapino,                               #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '010';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * app 圈子购买 微信支付 返回加密字符串
     */
    public function appWxPayCircleBuy($fmoney, $fapino, $app_id, $area)
    {
        $fapino = bin2hex($fapino . '---' . $area);

        //所需参数
        $post_api_data = [
            'fno' => $this->fzgyno,                                        #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fkey' => $this->fkey,                                         #开发者秘钥
            'fapino' => uniqid('whmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/api/he_meng_tong_circle_buy_call_back',#支付结果异步通知地址
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
            'fname' => '葡萄浏览器',                                       #名称
        ];
        $data = http_build_query($post_api_data);
        $data_all = 'pages/index/index?' . $data;
        $sign_data = $this->aesEncode($data_all, md5($app_id));
        return $sign_data;
    }

    /*
     * app 圈子加入 支付宝支付
     */
    public function appPayCircleJoin($fmoney, $famount, $fapino, $fpaytype = 1)
    {
        //app支付api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,                                     #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'famount' => $famount,                                         #应收金额（单位元），精确到小数点后两位如：1.23
            'fapino' => uniqid('ahmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/api/he_meng_tong_circle_join_call_back',#支付结果异步通知地址
//            'furl' => 'http://cc.k5.hk:28080/bing_notify',                 #测试 支付结果异步通知地址
            'fpaytype' => $fpaytype,                                       #支付类型1=支付宝 2=微信
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '010';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * app 圈子加入 微信支付 返回加密字符串
     */
    public function appWxPayCircleJoin($fmoney, $fapino, $app_id)
    {
        //所需参数
        $post_api_data = [
            'fno' => $this->fzgyno,                                        #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fkey' => $this->fkey,                                         #开发者秘钥
            'fapino' => uniqid('whmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/api/he_meng_tong_circle_join_call_back',#支付结果异步通知地址
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
            'fname' => '葡萄浏览器',                                       #名称
        ];
        $data = http_build_query($post_api_data);
        $data_all = 'pages/index/index?' . $data;
        $sign_data = $this->aesEncode($data_all, md5($app_id));
        return $sign_data;
    }

    /*
     * app 医疗 支付宝支付
     */
    public function appPayMedical($fmoney, $famount, $fapino, $fpaytype = 1)
    {
        //app支付api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,                                     #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'famount' => $famount,                                         #应收金额（单位元），精确到小数点后两位如：1.23
            'fapino' => uniqid('ahmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/he_meng_tong_medical_call_back',#支付结果异步通知地址
//            'furl' => 'http://cc.k5.hk:28080/bing_notify',                 #测试 支付结果异步通知地址
            'fpaytype' => $fpaytype,                                       #支付类型1=支付宝 2=微信
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '010';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * app 医疗 微信支付 返回加密字符串
     */
    public function appWxPayMedical($fmoney, $fapino, $app_id)
    {
        //所需参数
        $post_api_data = [
            'fno' => $this->fzgyno,                                        #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fkey' => $this->fkey,                                         #开发者秘钥
            'fapino' => uniqid('whmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/he_meng_tong_medical_call_back',#支付结果异步通知地址
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
            'fname' => '葡萄浏览器',                                       #名称
        ];
        $data = http_build_query($post_api_data);
        $data_all = 'pages/index/index?' . $data;
        $sign_data = $this->aesEncode($data_all, md5($app_id));
        return $sign_data;
    }

    /*
     * app 福禄平台商品 支付宝支付
     */
    public function appPayFulu($fapino, $fmoney, $famount, $fadd)
    {
        //app支付api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,                                     #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'famount' => $famount,                                         #应收金额（单位元），精确到小数点后两位如：1.23
            'fapino' => $fapino,        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/fulu_pay_call_back', #支付结果异步通知地址
//            'furl' => 'http://dns88.zicp.vip/callback/fulu_pay_call_back', #支付结果异步通知地址
            'fpaytype' => 1,                                       #支付类型1=支付宝 2=微信
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fadd,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '010';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        CommonFunction::log('订单号：' . $fadd . '--' . (string)$res_login_data->getBody(), 'HeMengTong/fulu');
        return (string)$res_login_data->getBody();
    }

    /*
     * app 福禄平台商品 微信支付 返回加密字符串
     */
    public function appWxPayFulu($fapino, $fmoney, $fadd, $app_id)
    {
        //所需参数
        $post_api_data = [
            'fno' => $this->fzgyno,                                        #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fkey' => $this->fkey,                                         #开发者秘钥
            'fapino' => $fapino,        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/fulu_pay_call_back',#支付结果异步通知地址
//            'furl' => 'http://dns88.zicp.vip/callback/fulu_pay_call_back',#支付结果异步通知地址
            'fadd' => $fadd,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
            'fname' => '葡萄浏览器',                                       #名称
        ];
        $data = http_build_query($post_api_data);
        $data_all = 'pages/index/index?' . $data;
        CommonFunction::log('订单号：' . $fadd . '--' . $data_all, 'HeMengTong/fulu');
        $sign_data = $this->aesEncode($data_all, md5($app_id));
        return $sign_data;
    }

    /*
     * app 福禄平台商品 支付宝支付
     */
    public function appPayRobot($fapino, $fmoney, $famount, $fadd)
    {
        //app支付api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,                                     #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'famount' => $famount,                                         #应收金额（单位元），精确到小数点后两位如：1.23
            'fapino' => $fapino,        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/wx_robot_pay', #支付结果异步通知地址
//            'furl' => 'http://dns88.zicp.vip/callback/wx_robot_pay', #支付结果异步通知地址
            'fpaytype' => 1,                                       #支付类型1=支付宝 2=微信
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fadd,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '010';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        CommonFunction::log('订单号：' . $fadd . '--' . (string)$res_login_data->getBody(), 'HeMengTong/robot');
        return (string)$res_login_data->getBody();
    }

    /*
     * app 福禄平台商品 微信支付 返回加密字符串
     */
    public function appWxPayRobot($fapino, $fmoney, $fadd, $app_id)
    {
        //所需参数
        $post_api_data = [
            'fno' => $this->fzgyno,                                        #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fkey' => $this->fkey,                                         #开发者秘钥
            'fapino' => $fapino,        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/wx_robot_pay',#支付结果异步通知地址
//            'furl' => 'http://dns88.zicp.vip/callback/wx_robot_pay',#支付结果异步通知地址
            'fadd' => $fadd,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
            'fname' => '葡萄浏览器',                                       #名称
        ];
        $data = http_build_query($post_api_data);
        $data_all = 'pages/index/index?' . $data;
        CommonFunction::log('订单号：' . $fadd . '--' . $data_all, 'HeMengTong/robot');
        $sign_data = $this->aesEncode($data_all, md5($app_id));
        return $sign_data;
    }

    /*
     * app 金币商城 支付宝支付
     */
    public function appPayCoinShop($fmoney, $famount, $fapino, $fpaytype = 1)
    {
        //app支付api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,                                     #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'famount' => $famount,                                         #应收金额（单位元），精确到小数点后两位如：1.23
            'fapino' => uniqid('ahcoin', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/he_meng_tong_coin_shop_call_back',#支付结果异步通知地址
            'fpaytype' => $fpaytype,                                       #支付类型1=支付宝 2=微信
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '010';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * app 金币商城 微信支付 返回加密字符串
     */
    public function appWxPayCoinShop($fmoney, $fapino, $app_id)
    {
        //所需参数
        $post_api_data = [
            'fno' => $this->fzgyno,                                        #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fkey' => $this->fkey,                                         #开发者秘钥
            'fapino' => uniqid('whcoin', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/he_meng_tong_coin_shop_call_back',#支付结果异步通知地址
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
            'fname' => '葡萄浏览器',                                       #名称
        ];
        $data = http_build_query($post_api_data);
        $data_all = 'pages/index/index?' . $data;
        $sign_data = $this->aesEncode($data_all, md5($app_id));
        return $sign_data;
    }

    /*
     * 微信小程序支付
     */
    public function wxAppPay($fmoney, $fopenid, $fapino)
    {
        //小程序支付api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->wxapp_fzgyno,                               #平台商户号
            'fmoney' => $fmoney,                                           #实收金额（单位元），精确到小数点后两位如：1.23
            'fopenid' => $fopenid,                                         #微信小程序用户唯一标识
            'fapino' => uniqid('mhmt', true),        #开发者订单号
            'furl' => 'http://api.36qq.com/callback/he_meng_tong_pay_call_back',#支付结果异步通知地址
//            'furl' => 'http://cc.k5.hk:28080/bing_notify',               #测试 支付结果异步通知地址
            'faccount' => '',                                              #可空 营业员账号（在平台商户系统获取：登录-->收银-->营业员-->账号）
            'fadd' => $fapino,                                             #可空 附加参数（非空时支付成功异步回调原值返回）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->wxapp_fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '009';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 订单撤销
     */
    public function orderRevocation($fno)
    {
        //订单撤销api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $this->fzgyno,       #平台商户号
            'fno' => $fno,                   #开发者订单号
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $this->fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '005';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 订单退款
     */
    public function orderRefund($fno, $fmoney)
    {
        //判定是app还是小程序下的单
        $is_app = substr($fno, 0, 1);
        if ($is_app == 'a') {
            $fzgyno = $this->fzgyno;
            $fkey = $this->fkey;
        } elseif ($is_app == 'w') {
            $fzgyno = $this->fzgyno;
            $fkey = $this->fkey;
        } elseif ($is_app == 'm') {
            $fzgyno = $this->wxapp_fzgyno;
            $fkey = $this->wxapp_fkey;
        } else {
            return false;
        }

        //订单退款api
        $client = new Client();
        $full_url = $this->postapi;

        //所需参数
        $post_api_data = [
            'fzgyno' => $fzgyno,                 #平台商户号
            'fno' => $fno,                       #开发者订单号
            'fmoney' => $fmoney,                 #退款金额（单位元，不超过订单金额）
        ];

        $sign = urldecode(http_build_query($post_api_data) . '&fkey=' . $fkey);
        $sign = strtolower(md5($sign));
        $post_api_data['task'] = '003';               #接口编号
        $post_api_data['fsign'] = $sign;              #签名字符串

        $data = [
            'headers' => ['Content-Type' => 'text/html', 'charset' => 'UTF-8'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /**
     * AES 加密
     * @param $data
     * @param $secret_key
     * @param string $iv
     * @return string
     */
    public function aesEncode($data, $secret_key, $iv = '1234567891234567')
    {
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $secret_key, OPENSSL_RAW_DATA, $iv));
    }

    /**
     * AES解密
     * @param $data
     * @param $secret_key
     * @param string $iv
     * @return string
     */
    public function aesDecode($data, $secret_key, $iv = '1234567891234567')
    {
        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $secret_key, OPENSSL_RAW_DATA, $iv);
    }

}