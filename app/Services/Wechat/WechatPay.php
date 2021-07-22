<?php

namespace App\Services\Wechat;


use App\Entitys\App\AppUserInfo;
use App\Services\Crypt\RsaUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class WechatPay
{
    protected $app_id = 'wxd2d9077a3072b5db';
    protected $mchid = '1521224461';
    protected $key = 'wuhang1231wuhang7890wuhang886655';
    protected $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    /**
     * 支付接口
     * @param $openid 用户openid
     * @param $real_name 用户真实姓名
     * @param $amount 付款金额 单位元 最低 0.3元
     * @param $order_id 本地平台订单号
     * @param string $desc 打款描述 （可空）
     * @param string $ip IP地址 可空
     * @return mixed
     */
    public function pay($openid, $real_name, $amount, $order_id, $desc = '我的浏览器用户打款', $ip = '192.168.0.1')
    {

        $params = [
            'mch_appid' => $this->app_id,
            'mchid' => $this->mchid,
            'nonce_str' => uniqid('pt', true),
            'partner_trade_no' => $order_id,
            'openid' => $openid,
            'check_name' => 'FORCE_CHECK',
            're_user_name' => $real_name,
            'amount' => $amount * 100,
            'desc' => $desc,
            'spbill_create_ip' => $ip,
        ];
        $sign = $this->sign($params);
        $params['sign'] = $sign;

        $xml = $this->arrayToXml($params);

        $client_params = [
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'cert' => '/data/Website/wechat/ssl/cert.pem',
            'ssl_key' => ['/data/Website/wechat/ssl/key.pem', $this->mchid],
            'body' => $xml,
        ];

        $client = new Client();
        $res = $client->request('POST', $this->url, $client_params);
        $resp_xml = $res->getBody();


        $arr_res = $this->xmlToArray($resp_xml);
        return $arr_res;
    }

    function sign($params)
    {
        ksort($params);
        $stringA = '';
        foreach ($params as $key => $val) $stringA .= $key . '=' . $val . '&';
        $stringA = trim($stringA, '&');
        $stringSignTemp = $stringA . "&key=" . $this->key;
        $sign = strtoupper(md5($stringSignTemp));
        return $sign;
    }


    function arrayToXml($arr, $dom = 0, $item = 0)
    {
        if (!$dom) {
            $dom = new \DOMDocument("1.0");
        }
        if (!$item) {
            $item = $dom->createElement("root");
            $dom->appendChild($item);
        }
        foreach ($arr as $key => $val) {
            $itemx = $dom->createElement(is_string($key) ? $key : "item");
            $item->appendChild($itemx);
            if (!is_array($val)) {
                $text = $dom->createTextNode($val);
                $itemx->appendChild($text);

            } else {
                $this->arrayToXml($val, $dom, $itemx);
            }
        }
        return $dom->saveXML();
    }

    function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

}
