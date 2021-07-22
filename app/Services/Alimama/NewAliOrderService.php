<?php

namespace App\Services\Alimama;

class NewAliOrderService
{

    private $list_auth_info = [
        [
            'app_key' => '25626319',
            'app_secret' => '05668c4eefc404c0cd175fb300b2723d',
            'pid' => 'mm_122930784_46170255_91593200288',
            'adzone_id' => '91593200288',
        ],
        [
            'app_key' => '25620531',
            'app_secret' => 'b12d3463ad8c0609c648202aad946ddb',
            'pid' => 'mm_123348922_46184097_98173200486',
            'adzone_id' => '98173200486',
        ],
        [
            'app_key' => '25821858',
            'app_secret' => '0676acbd3d38d1ceac4b476a25556eef',
            'pid' => 'mm_105946111_379150017_109081550149',
            'adzone_id' => '109081550149',
        ],
        [
            'app_key' => '25842871',
            'app_secret' => 'db9604b2acf693b95c7da990ad07b4f7',
            'pid' => 'mm_123640184_378350331_109080850313',
            'adzone_id' => '109080850313',
        ], [
            'app_key' => '25811684',
            'app_secret' => '7387f5ae77d61f8c6d2261f386d0c6d0',
            'pid' => '',
            'adzone_id' => '',
        ],
    ];

    private $wxl_list_auth_info = [
        [
            'app_key' => '25626319',
            'app_secret' => '05668c4eefc404c0cd175fb300b2723d',
            'pid' => 'mm_122930784_46170255_91593200288',
            'adzone_id' => '91593200288',
        ], [
            'app_key' => '25620531',
            'app_secret' => 'b12d3463ad8c0609c648202aad946ddb',
            'pid' => '',
            'adzone_id' => '',
        ], [
            'app_key' => '25821858',
            'app_secret' => '0676acbd3d38d1ceac4b476a25556eef',
            'pid' => '',
            'adzone_id' => '',
        ], [
            'app_key' => '25842871',
            'app_secret' => 'db9604b2acf693b95c7da990ad07b4f7',
            'pid' => '',
            'adzone_id' => '',
        ], [
            'app_key' => '25811684',
            'app_secret' => '7387f5ae77d61f8c6d2261f386d0c6d0',
            'pid' => '',
            'adzone_id' => '',
        ],
    ];


    /**
     * @param $type 1 2 3 4 5 | 0day 1day 5day 10day 15day
     * @return array
     */
    function getOrders($type = 0)
    {

        switch ($type) {
            case 1:
                $day = 0;
                break;
            case 2:
                $day = 1;
                break;
            case 3:
                $day = 5;
                break;
            case 4:
                $day = 10;
                break;
            case 5:
                $day = 15;
                break;
            default:
                $day = 0;
        }

        $res_list_orders = [];
        foreach ($this->list_auth_info as $key_item) {
            /**
             * @var $app_key string
             * @var $app_secret string
             * @var $pid string
             * @var $adzone_id string
             */
            extract($key_item);

            $res_orders = $this->getNewOrdersAgo($day, $app_key, $app_secret);
            $res_list_orders = array_merge($res_list_orders, $res_orders);
        }

        return $res_list_orders;
    }


    /*
     * 获取指定时间内全部正常订单
     */
    function getAssignTimeOrdersAll($start_time, $end_time)
    {
        $res_list_orders = [];
        foreach ($this->list_auth_info as $key_item) {
            /**
             * @var $app_key string
             * @var $app_secret string
             * @var $pid string
             * @var $adzone_id string
             */
            extract($key_item);

            $res_orders = $this->getAssignTimeOrders($start_time, $end_time, $app_key, $app_secret);
            $res_list_orders = array_merge($res_list_orders, $res_orders);
        }

        return $res_list_orders;
    }

    /**
     * @param $day
     * @param $app_key
     * @param $app_secret
     * @return array
     */
    private function getNewOrdersAgo($day, $app_key, $app_secret)
    {
        $c = new \TopClient();
        $c->appkey = $app_key;
        $c->secretKey = $app_secret;
        $c->format = 'json';
        $sim_time = strtotime("-{$day} day", time());
        $start_time = date("Y-m-d H:i:s", strtotime("-6 minute", $sim_time));
        $end_time = date("Y-m-d H:i:s", $sim_time);
        $req = new \TbkOrderDetailsGetRequest();
        $req->setQueryType("1");
        $req->setPageSize("100");
        $req->setStartTime($start_time);
        $req->setEndTime($end_time);

        $resp = $c->execute($req);

        if (empty($resp['data']['results'])) {
            return [];
        }
        return $resp['data']['results']['publisher_order_dto'];
    }

    /**渠道id查询
     * @param $type 1 2 3 4 5 | 0day 1day 5day 10day 15day
     * @return array
     */
    function getChannelOrders($type = 0)
    {

        switch ($type) {
            case 1:
                $day = 0;
                break;
            case 2:
                $day = 0.042; //一小时
                break;
            case 3:
                $day = 1;
                break;
            case 4:
                $day = 5;
                break;
            case 5:
                $day = 15;
                break;
            default:
                $day = 0;
        }

        $res_list_orders = [];
        foreach ($this->wxl_list_auth_info as $key_item) {
            /**
             * @var $app_key string
             * @var $app_secret string
             * @var $pid string
             * @var $adzone_id string
             */
            extract($key_item);

            $res_orders = $this->getChannelNewOrdersAgo($day, $app_key, $app_secret);
            $res_list_orders = array_merge($res_list_orders, $res_orders);
        }

        return $res_list_orders;
    }

    /**渠道id查询
     * @param $day
     * @param $app_key
     * @param $app_secret
     * @return array
     */
    private function getChannelNewOrdersAgo($day, $app_key, $app_secret)
    {
        $c = new \TopClient();
        $c->appkey = $app_key;
        $c->secretKey = $app_secret;
        $c->format = 'json';
        $day = (int)($day * 60 * 24); //天兑换成分钟
        $sim_time = strtotime("-{$day} minute", time());
        $start_time = date("Y-m-d H:i:s", strtotime("-6 minute", $sim_time));
        $end_time = date("Y-m-d H:i:s", $sim_time);
        $req = new \TbkOrderDetailsGetRequest();
        $req->setQueryType("1");
        $req->setPageSize("100");
        $req->setStartTime($start_time);
        $req->setEndTime($end_time);
        $req->setOrderScene(2);

        $resp = $c->execute($req);

        if (empty($resp['data']['results'])) {
            return [];
        }
        return $resp['data']['results']['publisher_order_dto'];
    }

    private function getAssignTimeOrders($start_time, $end_time, $app_key, $app_secret)
    {
        $c = new \TopClient();
        $c->appkey = $app_key;
        $c->secretKey = $app_secret;
        $c->format = 'json';
        $req = new \TbkOrderDetailsGetRequest();
        $req->setQueryType("1");
        $req->setPageSize("100");
        $req->setStartTime($start_time);
        $req->setEndTime($end_time);

        $resp = $c->execute($req);

        if (empty($resp['data']['results'])) {
            return [];
        }
        return $resp['data']['results']['publisher_order_dto'];
    }

}
