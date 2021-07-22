<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/04/23
 * Time: 15:31
 */

namespace App\Services\HaiWei;

class HaiWeiServices
{
    protected $url= 'http://tq.jfshou.cn';                       #域名
    protected $id = '780';                                       #代理商id
    protected $secretKey = 'Kyj2RkttJzB7ksSE76Xs8ybcRjsFXAfh';   #签名验证

    /*
     * APP首页
     */
    public function index($app_id)
    {
        //拼接api
        $url = $this->url . '/seller/app/classify';
        //所需参数
        $post_api_data = [
            'machineCode' => $app_id,       #用户唯一码
            'agentId' => $this->id,         #代理商id
            'timestamp' => time(),          #请求时间戳，5分钟内有效
        ];

        $sign = $this->sign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $plaintext = http_build_query($post_api_data);
        $url = $url . '?' . $plaintext;

        return $url;
    }


    /*
     * 我的订单页面
     */
    public function myOrder($app_id)
    {
        //拼接api
        $url = $this->url . '/seller/app/myOrder';
        //所需参数
        $post_api_data = [
            'machineCode' => $app_id,       #用户唯一码
            'agentId' => $this->id,         #代理商id
            'timestamp' => time(),          #请求时间戳，5分钟内有效
        ];

        $sign = $this->sign($post_api_data);
        $post_api_data['sign'] = $sign;         #签名

        $plaintext = http_build_query($post_api_data);
        $url = $url . '?' . $plaintext;

        return $url;
    }

    /*
     * 生成签名
     */
    function sign($params)
    {
        ksort($params);
        $stringA = urldecode(http_build_query($params));
        $stringB = $stringA . '&secretKey=' .$this->secretKey;
        $sign = strtoupper(md5($stringB));
        return $sign;
    }

}