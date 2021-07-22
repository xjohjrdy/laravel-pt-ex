<?php

namespace App\Services\Alimama;

use GuzzleHttp\Client;

/*
 * dataoke change
 */

class BigWashUser
{
    private $new_auth_info = [
        [
            'app_secret' => 'f8eb11cc1a32624e5fbe93c53ffe31f8',
            'app_key' => '5d0cae6092a79',
            'pid' => 'mm_122930784_46170255_109375250125',
        ],
    ];

    private $new_can_change_auth_info = [
        '109375250125' => [
            'app_secret' => 'f8eb11cc1a32624e5fbe93c53ffe31f8',
            'app_key' => '5d0cae6092a79',
            'pid' => 'mm_122930784_46170255_109375250125',
        ],
        '109469450037' => [
            'app_secret' => 'ca5f4c30e3c2cae21b231b997adbcb03',
            'app_key' => '5d134d25b55fa',
            'pid' => 'mm_123640184_378350331_109469450037',
        ],
        '109467850460' => [
            'app_secret' => '3d17731df2f98c3375fb3789648cc2fd',
            'app_key' => '5d108a7b59448',
            'pid' => 'mm_123348922_46184097_109467850460',
        ],
        '109467900491' => [
            'app_secret' => '7625fccbf5229ff6f746f03219cf0140',
            'app_key' => '5d141c01e326f',
            'pid' => 'mm_105946111_379150017_109467900491',
        ],
        '109551050001' => [
            'app_secret' => '3afa4a8aeaeda74cda533f0cb8816734',
            'app_key' => '5d6ce53a87c71',
            'pid' => 'mm_123184147_379150041_109551050001',
        ],
    ];

    private $auth_info = [
        [
            'app_secret' => '480acd3d45aadac3953e2b5ea7363acf',
            'app_key' => '5d0b6724c8ee3',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '4e8eb7a0049eb49f0083966a4ceb57d5',
            'app_key' => '5d0c4f772c794',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '28bff7a0739142249e72163eb0402f70',
            'app_key' => '5d0c7bcb78448',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '035d4165325fcfab403b55dff3b5194e',
            'app_key' => '5d0c964140418',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => 'c359ff9fd38741fd660f85524fdd9ea5',
            'app_key' => '5d0c98a0021cd',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => 'e1276e1aee4c2f24e58a2a82ed7a7129',
            'app_key' => '5d0c98ff1bf42',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '54f811d78c213d01e90d3dd2791ea609',
            'app_key' => '5d0c995e1f673',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '768f26443131f68fa0a521c99b723b35',
            'app_key' => '5d0c9997cecc3',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => 'a56b20f831e947f9548d9b76d6ecc174',
            'app_key' => '5d0cadb2c3c18',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '07171eba8ba1867f81270ab22e540493',
            'app_key' => '5d134f664e839',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => '711ec8c51f5f3fdf3a0ff28e15b2a0aa',
            'app_key' => '5d14172d39cb1',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'ee4d875edee6e1975ae99bf77e8af44e',
            'app_key' => '5d1417f1a42f7',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => '99e23284f03d519231918904ba65f451',
            'app_key' => '5d14187c94540',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'bb31201175a6acf8c1a565d4300fe507',
            'app_key' => '5d14192a5d389',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'dfbdd7752f1fdf46f96871466383b892',
            'app_key' => '5d1419a78ef22',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => '0992ff8af52d19f592966dd9c6a5ecee',
            'app_key' => '5d141a2a9824f',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'c99f98b03555b2a4c25c5059562dc579',
            'app_key' => '5d141ad188877',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'e2330cdd3a6ca266221dc3b30424b609',
            'app_key' => '5d141b5246b9d',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'ae73dc4000c7811dd66703b5140eef32',
            'app_key' => '5d108306dbda8',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => 'e666ef1c339a334a2d4f0e3acc7bdb24',
            'app_key' => '5d10841a48ca6',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => 'a7f3d5564fc0f019d75c166464a01aa5',
            'app_key' => '5d1084991136b',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => 'cd0513b122d3a0aca5a8586320fc5798',
            'app_key' => '5d10854456d39',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '239b526961b826605e48f5795b8d9826',
            'app_key' => '5d1085d812189',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '24511392864ac9c31d5bf8b0d6d07184',
            'app_key' => '5d10869b18d0d',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => 'c2d42839c62d44d0a0900e27f8e0ed48',
            'app_key' => '5d10870c7d471',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '7fa3756ab09fedaa947423a86da8218c',
            'app_key' => '5d108897e9b4a',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '2983fe131077118e4d04f95d9708d043',
            'app_key' => '5d1089dd45840',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '1bae1fb5470013e8f6d89bfbaaad47d9',
            'app_key' => '5d13482b43d0b',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '8f9513b8469fb32346f8bc30ad7577f5',
            'app_key' => '5d134904538d6',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '413addf10c173426a79a4c8de91db680',
            'app_key' => '5d1349c9b949d',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '78d514c37145c61c8deaa014ee3362f2',
            'app_key' => '5d134a3e7468e',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '0ed4d4833a9210b2dead152ed4641d7a',
            'app_key' => '5d134ab29d944',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '45598e278a906c6374473e967eea821c',
            'app_key' => '5d134b230d835',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '864fec89c0e6599f7c0730c1e87f765c',
            'app_key' => '5d134bc69e269',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => 'aaf8d6e37d926308d13e7415de29a79d',
            'app_key' => '5d134c4319595',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '221d3d7eddf9a3788e91eb47c95977b4',
            'app_key' => '5d134cb00b03c',
            'pid' => 'mm_123640184_378350331_109080850313',
        ]
    ];
    private $auth_info_high = [
        [
            'app_secret' => '480acd3d45aadac3953e2b5ea7363acf',
            'app_key' => '5d0b6724c8ee3',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '4e8eb7a0049eb49f0083966a4ceb57d5',
            'app_key' => '5d0c4f772c794',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '28bff7a0739142249e72163eb0402f70',
            'app_key' => '5d0c7bcb78448',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '035d4165325fcfab403b55dff3b5194e',
            'app_key' => '5d0c964140418',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => 'c359ff9fd38741fd660f85524fdd9ea5',
            'app_key' => '5d0c98a0021cd',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => 'e1276e1aee4c2f24e58a2a82ed7a7129',
            'app_key' => '5d0c98ff1bf42',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '54f811d78c213d01e90d3dd2791ea609',
            'app_key' => '5d0c995e1f673',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => '768f26443131f68fa0a521c99b723b35',
            'app_key' => '5d0c9997cecc3',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => 'a56b20f831e947f9548d9b76d6ecc174',
            'app_key' => '5d0cadb2c3c18',
            'pid' => 'mm_122930784_46170255_91593200288',
        ], [
            'app_secret' => 'ae73dc4000c7811dd66703b5140eef32',
            'app_key' => '5d108306dbda8',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => 'e666ef1c339a334a2d4f0e3acc7bdb24',
            'app_key' => '5d10841a48ca6',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => 'a7f3d5564fc0f019d75c166464a01aa5',
            'app_key' => '5d1084991136b',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => 'cd0513b122d3a0aca5a8586320fc5798',
            'app_key' => '5d10854456d39',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '239b526961b826605e48f5795b8d9826',
            'app_key' => '5d1085d812189',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '24511392864ac9c31d5bf8b0d6d07184',
            'app_key' => '5d10869b18d0d',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => 'c2d42839c62d44d0a0900e27f8e0ed48',
            'app_key' => '5d10870c7d471',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '7fa3756ab09fedaa947423a86da8218c',
            'app_key' => '5d108897e9b4a',
            'pid' => 'mm_123348922_46184097_98173200486',
        ], [
            'app_secret' => '2983fe131077118e4d04f95d9708d043',
            'app_key' => '5d1089dd45840',
            'pid' => 'mm_123348922_46184097_98173200486',
        ]
    ];
    private $auth_info_low = [
        [
            'app_secret' => '07171eba8ba1867f81270ab22e540493',
            'app_key' => '5d134f664e839',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => '711ec8c51f5f3fdf3a0ff28e15b2a0aa',
            'app_key' => '5d14172d39cb1',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'ee4d875edee6e1975ae99bf77e8af44e',
            'app_key' => '5d1417f1a42f7',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => '99e23284f03d519231918904ba65f451',
            'app_key' => '5d14187c94540',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'bb31201175a6acf8c1a565d4300fe507',
            'app_key' => '5d14192a5d389',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'dfbdd7752f1fdf46f96871466383b892',
            'app_key' => '5d1419a78ef22',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => '0992ff8af52d19f592966dd9c6a5ecee',
            'app_key' => '5d141a2a9824f',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'c99f98b03555b2a4c25c5059562dc579',
            'app_key' => '5d141ad188877',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => 'e2330cdd3a6ca266221dc3b30424b609',
            'app_key' => '5d141b5246b9d',
            'pid' => 'mm_105946111_379150017_109081550149',
        ], [
            'app_secret' => '1bae1fb5470013e8f6d89bfbaaad47d9',
            'app_key' => '5d13482b43d0b',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '8f9513b8469fb32346f8bc30ad7577f5',
            'app_key' => '5d134904538d6',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '413addf10c173426a79a4c8de91db680',
            'app_key' => '5d1349c9b949d',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '78d514c37145c61c8deaa014ee3362f2',
            'app_key' => '5d134a3e7468e',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '0ed4d4833a9210b2dead152ed4641d7a',
            'app_key' => '5d134ab29d944',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '45598e278a906c6374473e967eea821c',
            'app_key' => '5d134b230d835',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '864fec89c0e6599f7c0730c1e87f765c',
            'app_key' => '5d134bc69e269',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => 'aaf8d6e37d926308d13e7415de29a79d',
            'app_key' => '5d134c4319595',
            'pid' => 'mm_123640184_378350331_109080850313',
        ], [
            'app_secret' => '221d3d7eddf9a3788e91eb47c95977b4',
            'app_key' => '5d134cb00b03c',
            'pid' => 'mm_123640184_378350331_109080850313',
        ]
    ];

    //private $vip_percent = 0.325;
    //private $common_percent = 0.2;
    private $vip_percent = 0.645;
    private $common_percent = 0.42;
    private $share_vip_percent = 0.1;
    private $share_common_percent = 0.05;

    /*
     * API请求速度：200次 / 分钟
     * 当前应用内的所有API每分钟请求总和不得超过200次。
     * API请求量：10万次/天
     */

    /**
     * Super search
     * @param $params
     * @return array|bool
     */
    function superSearch($params)
    {
        $host = 'https://openapi.dataoke.com/api/tb-service/get-tb-service';
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.0.1';
        $params['pageSize'] = '20';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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

        $arr_results = [];
        foreach ($obj_results->data as $item) {
            @$item_results = [
                'good_id' => $item->item_id,
                'title' => $item->title,
                'img' => $item->pict_url,
                'coupon' => $item->coupon_amount,
                'coupon_price' => $item->zk_final_price,
                'sale_number' => $item->tk_total_sales,
                'tkmoney_general' => (string)round($item->tkmoney * $this->common_percent, 2),
                'tkmoney_vip' => (string)round($item->tkmoney * $this->vip_percent, 2),
                'store' => $item->shop_title,
                'store_from' => $item->user_type ? 'B' : 'C',
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
    function urlChange($params)
    {
        $host = 'https://openapi.dataoke.com/api/tb-service/get-privilege-link';
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.1.0';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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

        if (empty($obj_results->data->couponClickUrl)) {
            $goods_url = $obj_results->data->itemUrl;
        } else {
            $goods_url = $obj_results->data->couponClickUrl;
        }

        return $goods_url;
    }


    /**
     * @param $params
     * @return bool
     */
    function newUrlChange($params)
    {
        $host = 'https://openapi.dataoke.com/api/tb-service/get-privilege-link';
        $key_info = $this->new_auth_info[array_rand($this->new_auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.1.0';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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

        if (empty($obj_results->data->couponClickUrl)) {
            $goods_url = $obj_results->data->itemUrl;
        } else {
            $goods_url = $obj_results->data->couponClickUrl;
        }

        return $goods_url;
    }


    /**
     * @param $params
     * @return bool
     */
    function newManyUrlChange($params, $key = '109375250125')
    {
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
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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

        if (empty($obj_results->data->couponClickUrl)) {
            $goods_url = $obj_results->data->itemUrl;
        } else {
            $goods_url = $obj_results->data->couponClickUrl;
        }

        return $goods_url;
    }

    /**
     * 零元购
     * @param $params
     * @return bool
     */
    function zeroBuyUrlChange($params, $key = '109551050001')
    {
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
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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

        if (empty($obj_results->data->couponClickUrl)) {
            $goods_url = $obj_results->data->itemUrl;
        } else {
            $goods_url = $obj_results->data->couponClickUrl;
        }

        return $goods_url;
    }


    /**
     * goods id
     * @param $params
     * @return array|bool
     */
    function goodsDetailsImg($params)
    {
        $host = 'https://openapi.dataoke.com/api/goods/get-goods-details';
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.1.0';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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
            'title' => $obj_results->data->title,
            'good_id' => $obj_results->data->goodsId,
            'many_img' => $arr_imgs,
            'store_from' => $obj_results->data->shopType ? 'B' : 'C',
            'sale_number' => $obj_results->data->monthSales,
            'coupon_price' => $obj_results->data->actualPrice - @$obj_results->data->couponPrice,
            'price' => $obj_results->data->actualPrice,
            'tkmoney_general' => round($commission_val * $this->common_percent, 2),
            'tkmoney_vip' => round($commission_val * $this->vip_percent, 2),
            'share_tkmoney_general' => round($commission_val * $this->share_common_percent, 2),
            'share_tkmoney_vip' => round($commission_val * $this->share_vip_percent, 2),
            'coupon' => empty($obj_results->data->couponPrice) ? 0 : $obj_results->data->couponPrice,
            'coupon_start_time' => strtotime($obj_results->data->couponStartTime),
            'coupon_end_time' => strtotime($obj_results->data->couponEndTime),
            'detail_img' => $obj_results->data->detailPics,
            'brand_name' => $obj_results->data->brandName,
            'shop_name' => $obj_results->data->shopName,
        ];
        foreach ($goods_info as &$val) {
            if (is_array($val)) continue;
            $val = (string)$val;
        }
        return $goods_info;
    }

    /**
     * goods id
     * @param $params
     * @return array|bool
     */
    function goodsDetails($params)
    {
        $host = 'https://openapi.dataoke.com/api/goods/get-goods-details';
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.1.0';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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

        @$commission_val = $obj_results->data->actualPrice * ($obj_results->data->commissionRate / 100);
        @$goods_info = [
            'title' => $obj_results->data->title,
            'good_id' => $obj_results->data->goodsId,
            'many_img' => [$obj_results->data->mainPic],
            'store_from' => $obj_results->data->shopType ? 'B' : 'C',
            'sale_number' => $obj_results->data->monthSales,
            'coupon_price' => $obj_results->data->actualPrice - @$obj_results->data->couponPrice,
            'price' => $obj_results->data->actualPrice,
            'tkmoney_general' => round($commission_val * $this->common_percent, 2),
            'tkmoney_vip' => round($commission_val * $this->vip_percent, 2),
            'share_tkmoney_general' => round($commission_val * $this->share_common_percent, 2),
            'share_tkmoney_vip' => round($commission_val * $this->share_vip_percent, 2),
            'coupon' => empty($obj_results->data->couponPrice) ? 0 : $obj_results->data->couponPrice,
            'coupon_start_time' => strtotime($obj_results->data->couponStartTime),
            'coupon_end_time' => strtotime($obj_results->data->couponEndTime),
            'detail_img' => $obj_results->data->detailPics,
            'brand_name' => $obj_results->data->brandName,
        ];
        foreach ($goods_info as &$val) {
            if (is_array($val)) continue;
            $val = (string)$val;
        }
        return $goods_info;
    }

    /**
     * goods id
     * @param $params
     * @return array|bool
     */
    function shareGoodsDetails($params)
    {
        $host = 'https://openapi.dataoke.com/api/goods/get-goods-details';
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.1.0';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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

        @$goods_info = [
            'title' => $obj_results->data->title,
            'many_img' => $obj_results->data->mainPic,
            'coupon_price' => $obj_results->data->actualPrice,
            'price' => $obj_results->data->originalPrice,
            'coupon' => empty($obj_results->data->couponPrice) ? 0 : $obj_results->data->couponPrice,
        ];
        foreach ($goods_info as &$val) {
            if (is_array($val)) continue;
            $val = (string)$val;
        }
        return $goods_info;
    }

    /*
     * 大淘客搜索
     */
    function getDtkSearchGoods($params)
    {
        $host = 'https://openapi.dataoke.com/api/goods/get-dtk-search-goods';
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v2.1.1';
        $params['pageSize'] = '10';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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
            return [];
        }

        if ($obj_results->code != 0) {
            return [];
        }

        $arr_results = [];
        foreach ($obj_results->data->list as $item) {
            @$commission_val = $item->actualPrice * ($item->commissionRate / 100);
            @$item_results = [
                'good_id' => $item->goodsId,                                                                              #用来传输的商品id
                'title' => $item->title,                                                                                  #商品标题
                'img' => $item->mainPic,                                                                                  #图片
                'coupon' => $item->couponPrice,                                                                           #优惠卷金额
                'coupon_price' => $item->actualPrice,                                                                     #卷后价
                'price' => $item->originalPrice,                                                                          #原价
                'sale_number' => $item->monthSales,                                                                       #销量
                'tkmoney_general' => (string)round($commission_val * $this->common_percent, 2),            #预估报销
                'tkmoney_vip' => (string)round($commission_val * $this->vip_percent, 2),                   #vip预估报销
                'share_tkmoney_general' => (string)round($commission_val * $this->share_common_percent, 2),#分享预估报销
                'share_tkmoney_vip' => (string)round($commission_val * $this->share_vip_percent, 2),       #vip分享预估报销
                'store' => $item->shopName,                                                                               #商店名字
                'store_from' => $item->shopType ? 'B' : 'C',                                                              #天猫还是淘宝 B=>天猫
            ];
            foreach ($item_results as &$val) $val = (string)$val;
            $arr_results[] = $item_results;
        }
        return $arr_results;
    }

    /**
     * 参数加密
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

    /**
     * type for big new
     */
    public function bigType()
    {
        $big_type = [];
        $host = 'https://openapi.dataoke.com/api/category/get-super-category';
        $arr = $this->auth_info[array_rand($this->auth_info, 1)];
        $appKey = $arr['app_key'];
        $appSecret = $arr['app_secret'];
        $pid = $arr['pid'];
        $data = [
            'appKey' => $appKey,
            'version' => 'v1.1.0',
        ];
        $data['sign'] = $this->makeSign($data, $appSecret);
        $url = $host . '?' . http_build_query($data);
        $client = new Client();
        $res_head_classification = $client->request('get', $url, ['verify' => false]);
        $json_res_head_classification = (string)$res_head_classification->getBody();
        $head_classification = json_decode($json_res_head_classification, true);

        if (empty($head_classification['data'])) {
            return 0;
        }

        foreach ($head_classification['data'] as $k => $head) {
            $big_type[$k]['name'] = $head['cname'];
            $big_type[$k]['ico'] = $head['cpic'];
            foreach ($head['subcategories'] as $key => $son) {
                $big_type[$k]['son'][$key]['name'] = $son['subcname'];
                $big_type[$k]['son'][$key]['ico'] = $son['scpic'];
            }
        }

        return $big_type;
    }

    /*
     * 大淘客 热门推荐rankType=3 漏洞单rankType=2
     */
    function getRankingList($params)
    {
        $host = 'https://openapi.dataoke.com/api/goods/get-ranking-list';
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.1.2';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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
            return [];
        }

        if ($obj_results->code != 0) {
            return [];
        }

        $arr_results = [];
        foreach ($obj_results->data as $item) {
            @$commission_val = $item->actualPrice * ($item->commissionRate / 100);
            @$item_results = [
                'good_id' => $item->goodsId,                                                                              #用来传输的商品id
                'title' => $item->title,                                                                                  #商品标题
                'img' => $item->mainPic,                                                                                  #图片
                'coupon' => $item->couponPrice,                                                                           #优惠卷金额
                'coupon_price' => $item->actualPrice,                                                                     #卷后价
                'price' => $item->originalPrice,                                                                          #原价
                'sale_number' => $item->monthSales,                                                                       #销量
                'store_from' => $item->shopType ? 'B' : 'C',                                                              #天猫还是淘宝 B=>天猫
                'tkmoney_general' => (string)round($commission_val * $this->common_percent, 2),            #预估报销
                'tkmoney_vip' => (string)round($commission_val * $this->vip_percent, 2),                   #vip预估报销
                'share_tkmoney_general' => (string)round($commission_val * $this->share_common_percent, 2),#分享预估报销
                'share_tkmoney_vip' => (string)round($commission_val * $this->share_vip_percent, 2),       #vip分享预估报销
            ];
            foreach ($item_results as &$val) $val = (string)$val;
            $arr_results[] = $item_results;
        }
        return $arr_results;
    }

    /*
     * 大淘客 限时抢购
     */
    function ddqGoodsList($params = [])
    {
        $host = 'https://openapi.dataoke.com/api/category/ddq-goods-list';
        $key_info = $this->auth_info[array_rand($this->auth_info)];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        /** 临时测试用*/

        $params['appKey'] = $app_key;
        $params['version'] = 'v1.2.0';
        $params['sign'] = $this->makeSign($params, $app_secret);
        $url = $host . '?' . http_build_query($params);
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
            return [];
        }

        if ($obj_results->code != 0) {
            return [];
        }

        $arr_results = [];
        @$arr_results['ddqTime'] = $obj_results->data->ddqTime;#场次时间
        @$arr_results['status'] = $obj_results->data->status;#场次状态 0-已开始，1-当前场次，2-未开始
        foreach ($obj_results->data->roundsList as $item) {#场次列表
            @$arr_results['roundsList'][] = [
                'ddqTime' => $item->ddqTime,
                'status' => $item->status
            ];
        }
        foreach ($obj_results->data->goodsList as $item) {#场次抢购商品
            @$commission_val = $item->actualPrice * ($item->commissionRate / 100);
            @$arr_results['goodsList'][] = [
                'good_id' => $item->goodsId,                                                                              #用来传输的商品id
                'title' => $item->title,                                                                                  #商品标题
                'img' => $item->mainPic,                                                                                  #图片
                'coupon' => (string)$item->couponPrice,                                                                   #优惠卷金额
                'coupon_price' => (string)$item->actualPrice,                                                             #卷后价
                'price' => (string)$item->originalPrice,                                                                  #原价
                'sale_number' => (string)$item->monthSales,                                                               #销量
                'tkmoney_general' => (string)round($commission_val * $this->common_percent, 2),            #预估报销
                'tkmoney_vip' => (string)round($commission_val * $this->vip_percent, 2),                   #vip预估报销
                'share_tkmoney_general' => (string)round($commission_val * $this->share_common_percent, 2),#分享预估报销
                'share_tkmoney_vip' => (string)round($commission_val * $this->share_vip_percent, 2),       #vip分享预估报销
                'store' => $item->shopName,                                                                               #商店名字
            ];
        }
        return $arr_results;
    }
}
