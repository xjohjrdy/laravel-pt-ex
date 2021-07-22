<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/03/26
 * Time: 11:29
 */

namespace App\Services\Kfc;

use GuzzleHttp\Client;

class KfcServices
{
//    protected $url = 'https://live-test.qianzhu8.com'; #测试 域名
//    protected $platformId = '179';              #测试 分配的平台ID
//    protected $secret = 'AjxpHTrHgPqcNaOr';     #测试秘钥

    protected $url= 'https://live.qianzhu8.com';      #正式 域名
    protected $platformId = '10138';                     #正式 分配的平台ID
    protected $secret = '9COyn6vfMqNStd3j';            #正式 秘钥

    /*
     * 登录跳转接口(没有手机号码)
     */
    public function loginV2($app_id, $name)
    {
        //拼接api
        $url = $this->url . '/api/v2/platform/login';
        //所需参数
        $post_api_data = [
            /*系统参数*/
            'platformId' => $this->platformId,   #平台id(千猪分配给平台的平台id)
            'timestamp' => time(),               #unix时间戳(GMT)
            /*业务参数*/
            'platformUniqueId' => $app_id,       #用户唯一标识
            'nickname' => $name,                 #用户昵称
            'redirectUrl' => '',                 #可空 重定向地址（不用填，备用，但需要参与签名）
        ];

        $sign = $this->sign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $plaintext = http_build_query($post_api_data);
        $url = $url . '?' . $plaintext;

        return $url;
    }

    /*
     * 登录跳转接口(有手机号码)
     */
    public function loginV3($app_id, $name, $mobile)
    {
        //拼接api
        $url = $this->url . '/api/v3/platform/login';
        //所需参数
        $post_api_data = [
            /*系统参数*/
            'platformId' => $this->platformId,   #平台id(千猪分配给平台的平台id)
            'timestamp' => time(),               #unix时间戳(GMT)
            /*业务参数*/
            'platformUniqueId' => $app_id,       #用户唯一标识
            'nickname' => $name,                 #用户昵称
            'mobile' => $mobile,                 #手机号码
            'redirectUrl' => '',                 #可空 重定向地址（不用填，备用，但需要参与签名）
        ];

        $sign = $this->sign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $plaintext = http_build_query($post_api_data);
        $url = $url . '?' . $plaintext;

        return $url;
    }

    /*
     * 分页查询肯德基订单
     */
    public function pagedQuery($pageIndex, $pageSize, $updateTimeBeginTime, $updateTimeEndTime)
    {
        $client = new Client();
        //拼接api
        $url = $this->url . '/openApi/v1/kfcOrders/pagedQuery';
        //所需参数
        $post_api_data = [
            /*系统参数*/
            'platformId' => $this->platformId,   #平台id(千猪分配给平台的平台id)
            'timestamp' => time(),               #unix时间戳(GMT)
            /*业务参数*/
            'pageIndex' => $pageIndex,                      #分页页码，从1开始
            'pageSize' => $pageSize,                        #每页大小
            'updateTimeBeginTime' => $updateTimeBeginTime,  #最后更新时间开始时间
            'updateTimeEndTime' => $updateTimeEndTime,      #最后更新时间结束时间
        ];

        $sign = $this->sign($post_api_data);
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
     * 根据订单号查询订单
     */
    public function getByOrderNo($orderNo)
    {
        $client = new Client();
        //拼接api
        $url = $this->url . '/openApi/v1/kfcOrders/getByOrderNo';
        //所需参数
        $post_api_data = [
            /*系统参数*/
            'platformId' => $this->platformId,   #平台id(千猪分配给平台的平台id)
            'timestamp' => time(),               #unix时间戳(GMT)
            /*业务参数*/
            'orderNo' => $orderNo,  #订单号
        ];

        $sign = $this->sign($post_api_data);
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
    function sign($params)
    {
        ksort($params);
        $stringA = urldecode(http_build_query($params));
        $stringB = $stringA . $this->secret;
        $sign = md5($stringB);
        return $sign;
    }

}