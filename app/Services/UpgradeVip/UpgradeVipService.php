<?php

namespace App\Services\UpgradeVip;

use GuzzleHttp\Client;

class UpgradeVipService
{
    private $auth_info = [
        [
            'app_key' => '25919216',
            'app_secret' => '9aac454e0b538ee6c0504d48446be023',
            'pid' => 'mm_123634218_377750453_109771100415',
            'adzone_id' => '109771100415',
        ],
    ];

    private $details_auth_info = [
        [
            'app_secret' => 'c6ccf493b7f1d498433b1bea5c51e2d8',
            'app_key' => '5dd3a7067c79a',
            'pid' => 'mm_123634218_377750453_109771100415',
        ],
    ];

    private $new_can_change_auth_info = [
        //woxiaoli
        '109375250125' => [
            'app_secret' => 'c6ccf493b7f1d498433b1bea5c51e2d8',
            'app_key' => '5dd3a7067c79a',
            'pid' => 'mm_123634218_377750453_109771100415',
        ],
    ];

    private $vip_percent = 0.645;
    private $common_percent = 0.42;
    private $share_vip_percent = 0.1;
    private $share_common_percent = 0.05;


    /**
     * Super search
     * @param $params
     * @return array|bool
     */
    function dgSearch($params)
    {
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         * @var $adzone_id string
         */
        extract($key_info);
        $c = new \TopClient();
        $c->appkey = $app_key;
        $c->secretKey = $app_secret;
        $c->format = 'json';
        $req = new \TbkDgMaterialOptionalRequest();
        $req->setAdzoneId($adzone_id);
        $req->setQ($params['q']);
        if (!empty($params['page_no'])) $req->setPageNo($params['page_no']);
        if (!empty($params['sort'])) $req->setSort($params['sort']);
        if (!empty($params['size'])) $req->setPageSize($params['size']);
        if (!empty($params['ip'])) $req->setIp($params['ip']);
        if (!empty($params['has_coupon'])) $req->setHasCoupon($params['has_coupon']);

        $resp = $c->execute($req);

        if (empty($resp['result_list'])) {
            return [];
        }

        $arr_results = [];

        foreach ($resp['result_list']['map_data'] as $item) {
            @$commission_val = ($item['zk_final_price'] - $item['coupon_amount']) * ($item['commission_rate'] / 10000);
            @$item_results = [
                'good_id' => $item['item_id'],                //?????????????????????id
                'title' => $item['title'],                    //??????
                'img' => $item['pict_url'],                   //??????
                'price' => $item['zk_final_price'],           //??????????????????
                'coupon' => $item['coupon_amount'] ?: 0,           //???????????????
                'coupon_price' => $item['zk_final_price'] - $item['coupon_amount'],    //?????????
                'sale_number' => $item['tk_total_sales'],     //??????
                'tkmoney_general' => (string)round($commission_val * $this->common_percent, 2),     //????????????
                'tkmoney_vip' => (string)round($commission_val * $this->vip_percent, 2),            //vip????????????
                'share_tkmoney_general' => round($commission_val * $this->share_common_percent, 2), //??????????????????
                'share_tkmoney_vip' => round($commission_val * $this->share_vip_percent, 2), //vip??????????????????
                'store' => $item['shop_title'],               //????????????
                'store_from' => $item['user_type'] ? 'B' : 'C',//??????????????????
            ];
            foreach ($item_results as &$val) $val = (string)$val;
            $arr_results[] = $item_results;
        }
        return $arr_results;
    }

    /**
     * goods id
     * @param $params
     * @return array|bool
     */
    function goodsDetailsImg($params)
    {
        //????????????
        $host = 'https://openapi.dataoke.com/api/goods/get-goods-details';
        $key_info = $this->details_auth_info[array_rand($this->details_auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.1.0';
        //???????????????
        $params['sign'] = $this->makeSign($params, $app_secret);
        //??????????????????
        $url = $host . '?' . http_build_query($params);
        //????????????????????????
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        curl_close($ch);
        $obj_results = json_decode($output);

        if (empty($obj_results)) {
            return false;
        }

        if ($obj_results->code != 0) {
            return false;
        }

        $str_imgs = $obj_results->data->imgs;
        $arr_imgs = explode(',', $str_imgs);

        @$commission_val = $obj_results->data->actualPrice * ($obj_results->data->commissionRate / 100);
        @$goods_info = [
            'title' => $obj_results->data->title, //??????
            'good_id' => $obj_results->data->goodsId, //??????id
            'many_img' => $arr_imgs, //??????
            'store_from' => $obj_results->data->shopType ? 'B' : 'C',//??????????????????, //??????????????????
            'sale_number' => $obj_results->data->monthSales, //??????
            'coupon_price' => $obj_results->data->actualPrice - @$obj_results->data->couponPrice, //?????????
            'price' => $obj_results->data->actualPrice, //?????????
            'tkmoney_general' => round($commission_val * $this->common_percent, 2), //????????????
            'tkmoney_vip' => round($commission_val * $this->vip_percent, 2), //vip????????????
            'share_tkmoney_general' => round($commission_val * $this->share_common_percent, 2), //??????????????????
            'share_tkmoney_vip' => round($commission_val * $this->share_vip_percent, 2), //vip??????????????????
            'coupon' => empty($obj_results->data->couponPrice) ? 0 : $obj_results->data->couponPrice, //???????????????
            'coupon_start_time' => strtotime($obj_results->data->couponStartTime), //?????????????????????
            'coupon_end_time' => strtotime($obj_results->data->couponEndTime), //?????????????????????
            'detail_img' => $obj_results->data->detailPics, //?????????
            'brand_name' => $obj_results->data->brandName, //??????
//            'couponurl' => $obj_results->data->couponLink //??????
        ];
        foreach ($goods_info as &$val) {
            if (is_array($val)) continue;
            $val = (string)$val;
        }
        return $goods_info;
    }

    /**
     * @param $params
     * @return bool
     */
    function newManyUrlChange($params, $key = '109375250125')
    {
        //????????????
        $host = 'https://openapi.dataoke.com/api/tb-service/get-privilege-link';
        $key_info = $this->new_can_change_auth_info[$key];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.1.0';
        //???????????????
        $params['sign'] = $this->makeSign($params, $app_secret);
        //??????????????????
        $url = $host . '?' . http_build_query($params);
        //????????????????????????
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        curl_close($ch);
        $obj_results = json_decode($output);

        var_dump($obj_results);
        exit();

        if (empty($obj_results)) {
            return false;
        }

        if ($obj_results->code != 0) {
            return false;
        }

        if (empty($obj_results->data->couponClickUrl)) {
            $goods_url = $obj_results->data->itemUrl;
        } else {
            $goods_url = $obj_results->data->couponClickUrl;
        }

        return $goods_url;
    }

    /**
     * ????????????
     * @param $data
     * @param $appSecret
     * @return string
     */
    function makeSign($data, $appSecret)
    {
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            $str .= '&' . $k . '=' . $v;
        }
        $str = trim($str, '&');
        $sign = strtoupper(md5($str . '&key=' . $appSecret));
        return $sign;
    }
}
