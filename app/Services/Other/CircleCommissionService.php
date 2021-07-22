<?php

namespace App\Services\Other;


use GuzzleHttp\Client;

class CircleCommissionService
{
    /*
     * 加入圈子分佣
     */
    public function newBonusOther($order_id)
    {
        //拼接api
        $client = new Client();
        $login_url = 'http://pt.qmshidai.com/api/get_info_circle_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #订单order_id
        ];
        $post_api_data = [
            'data' => json_encode($post_api_data),
        ];
        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 团队会员购买圈子分佣
     */
    public function buyCircleCommission($order_id)
    {
        //拼接api
        $client = new Client();
        $login_url = 'http://pt.qmshidai.com/api/buy_circle_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,                 #订单order_id
        ];
        $post_api_data = [
            'data' => json_encode($post_api_data),
        ];
        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 团队会员竞价圈子分佣
     */
    public function biddingCircleCommission($order_id)
    {
        //拼接api
        $client = new Client();
        $login_url = 'http://pt.qmshidai.com/api/bidding_circle_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,                 #订单order_id
        ];
        $post_api_data = [
            'data' => json_encode($post_api_data),
        ];
        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }
}