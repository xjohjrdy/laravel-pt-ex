<?php

namespace App\Services\Alimama;

use GuzzleHttp\Client;


class AlimamaService
{

    private $auth_info = [
        [
            'app_key' => '25626319',
            'app_secret' => '05668c4eefc404c0cd175fb300b2723d',
            'pid' => 'mm_122930784_46170255_91593200288',
            'adzone_id' => '91593200288',
        ],
    ];

    private $auth_info_low = [
        [
            'app_key' => '25821858',
            'app_secret' => '0676acbd3d38d1ceac4b476a25556eef',
            'pid' => 'mm_105946111_379150017_109081550149',
            'adzone_id' => '109081550149',
        ],
    ];
    private $auth_info_bk = [
        [
            'app_key' => '25842871',
            'app_secret' => 'db9604b2acf693b95c7da990ad07b4f7',
            'pid' => 'mm_123640184_378350331_109080850313',
            'adzone_id' => '109080850313',
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
                'good_id' => $item['item_id'],
                'title' => $item['title'],
                'img' => $item['pict_url'],
                'price' => $item['zk_final_price'],
                'coupon' => $item['coupon_amount'] ?: 0,
                'coupon_price' => $item['zk_final_price'] - $item['coupon_amount'],
                'sale_number' => $item['tk_total_sales'],
                'tkmoney_general' => (string)round($commission_val * $this->common_percent, 2),
                'tkmoney_vip' => (string)round($commission_val * $this->vip_percent, 2),
                'share_tkmoney_general' => round($commission_val * $this->share_common_percent, 2),
                'share_tkmoney_vip' => round($commission_val * $this->share_vip_percent, 2),
                'store' => $item['shop_title'],
                'store_from' => $item['user_type'] ? 'B' : 'C',
            ];
            foreach ($item_results as &$val) $val = (string)$val;
            $arr_results[] = $item_results;
        }


        return $arr_results;
    }

}
