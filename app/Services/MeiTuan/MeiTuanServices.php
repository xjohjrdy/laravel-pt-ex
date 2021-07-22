<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/03/09
 * Time: 15:43
 */

namespace App\Services\MeiTuan;

use GuzzleHttp\Client;

class MeiTuanServices
{
    protected $key = 'qOQ5tLIyyIdiL7yD'; #秘钥
    protected $utmSource = '2101';       #渠道ID
    protected $activity = '0vli5dldzb';  #外卖活动ID

    /*
     * 得到有二级返佣的投放链接
     */
    public function getYesRebateUrl($utmMedium)
    {
        $time = time();
        $data = [
            'timestamp' => $time,                                             #渠道生成 当前时间戳 秒或毫秒
            'utmSource' => $this->utmSource,                                  #渠道ID
            'utmMedium' => $this->encrypt($utmMedium),                        #渠道自定义 用于标记分佣用户ID 需要使用 AES 加密
            'access_token' => $this->encrypt($this->utmSource . $time), #渠道生成 utmSource与时间戳拼接起来 然后使用 AES 加密生成
            'requestId' => $this->utmSource . $time,                          #渠道生成 utmSource+时间戳拼接
            'activity' => $this->activity,                                    #外卖活动ID-商务给出
            'version' => 1.0                                                  #版本号 默认1.0
        ];

        //data为投放链接所需参数
        $plaintext = urldecode(http_build_query($data));
        $url = 'https://act.meituan.com/clover/page/adunioncps/second_cashback?' . $plaintext;

        return $url;
    }

    /*
     * 得到没有二级返佣的投放链接
     */
    public function getNoRebateUrl($utmMedium)
    {
        $time = time();
        $data = [
            'timestamp' => $time,                                             #渠道生成 当前时间戳 秒或毫秒
            'utmSource' => $this->utmSource,                                  #渠道ID
            'utmMedium' => $this->encrypt($utmMedium),                        #渠道自定义 用于标记分佣用户ID 需要使用 AES 加密
            'access_token' => $this->encrypt($this->utmSource . $time), #渠道生成 utmSource与时间戳拼接起来 然后使用 AES 加密生成
            'requestId' => $this->utmSource . $time,                          #渠道生成 utmSource+时间戳拼接
            'activity' => $this->activity,                                    #外卖活动ID-商务给出
            'version' => 1.0                                                  #版本号 默认1.0
        ];

        //data为投放链接所需参数
        $plaintext = urldecode(http_build_query($data));
        $url = 'https://act.meituan.com/clover/page/adunioncps/share_coupon?' . $plaintext;

        return $url;
    }

    /*
     * 查询美团分销订单数据
     */
    public function getVerifyOrderData($page, $size, $startVerifyDate, $endVerifyDate)
    {
        $time = time();
        //查询订单结果api
        $client = new Client();
        $url = 'https://union.dianping.com/data/promote/verify/item';
        //所需参数 通用
        $post_api_data = [
            'timestamp' => $time,                                            #渠道生成 当前时间戳 秒或毫秒
            'utmSource' => $this->utmSource,                                 #渠道ID
            'accessToken' => $this->encrypt($this->utmSource . $time), #渠道生成 utmSource与时间戳拼接起来 然后使用 AES 加密生成
            'requestId' => $this->utmSource . $time,                         #渠道生成 utmSource+时间戳拼接
            'version' => 1.0,                                                #版本号 默认1.0
            'page' => $page,                                                #页数 从1开始
            'size' => $size                                                  #每页数据
        ];
        if (!empty($startVerifyDate)) $post_api_data['startVerifyDate'] = $startVerifyDate;  #核验订单⽇期，起始⽇期，eg:2019-04-04
        if (!empty($endVerifyDate)) $post_api_data['endVerifyDate'] = $endVerifyDate;        #核验订单⽇期，结束⽇期，eg:2019-04-04

        $plaintext = urldecode(http_build_query($post_api_data));
        $url = $url . '?' . $plaintext;

        $res = $client->request('get', $url, ['verify' => false]);
        $data = json_decode((string)$res->getBody(), true);
        return $data;
    }

    /*
     * AES/CBC/PKCS5Padding 加密
     */
    public function encrypt($data)
    {
        return strtoupper(bin2hex((openssl_encrypt($data, 'AES-128-ECB', $this->key, OPENSSL_PKCS1_PADDING))));
    }

    /*
     * AES/CBC/PKCS5Padding 解密
     */
    public function decrypt($data)
    {
        $data = $this->hex2bin(strtolower($data));
        return openssl_decrypt($data, 'AES-128-ECB', $this->key, OPENSSL_PKCS1_PADDING);
    }

    /*
     * AES/CBC/PKCS5Padding 解密数据处理
     */
    function hex2bin($hexData)
    {
        $binData = "";
        for ($i = 0; $i < strlen($hexData); $i += 2) {
            $binData .= chr(hexdec(substr($hexData, $i, 2)));
        }
        return $binData;
    }

}