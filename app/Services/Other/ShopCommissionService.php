<?php


namespace App\Services\Other;


use GuzzleHttp\Client;

class ShopCommissionService
{
    /*
     * vip商品分佣
     */
    public function vipShopCommission($order_id, $count_partner)
    {
        //拼接api
        $client = new Client();
        $login_url = 'http://pt.qmshidai.com/api/vip_shop_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #主订单order_id
            'count_partner' => $count_partner,#合伙人数量
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
     * 普通商品分佣
     */
    public function generalShopCommission($order_id, $all_profit_value, $parent_id, $count_partner)
    {
        //拼接api
        $client = new Client();
        $login_url = 'http://pt.qmshidai.com/api/general_shop_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,                 #主订单order_id
            'all_profit_value' => $all_profit_value, #商品总利润值
            'parent_id' => $parent_id,               #该用户的父id
            'count_partner' => $count_partner,       #合伙人数量
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
     * 商城经理分佣
     */
    public function shopManagerCommission($order_id, $all_profit_value, $parent_id, $count_partner)
    {
        //拼接api
        $client = new Client();
        $login_url = 'http://pt.qmshidai.com/callback/general_shop_count_maid_manager';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,                 #主订单order_id
            'all_profit_value' => $all_profit_value, #商品总利润值
            'parent_id' => $parent_id,               #该用户的父id
            'count_partner' => $count_partner,       #合伙人数量
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
     * 饿了么分佣
     */
    public function eleMoreCommission($order_id, $app_id = 1, $commission = 1)
    {
        //拼接api
        $client = new Client();
        $login_url = 'http://pt.qmshidai.com/api/ali_ele_more_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,                 #主订单order_id
            'app_id' => $app_id,                     #用户id
            'commission' => $commission,             #待分金额
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
     * 饿了么假分佣变真分佣 (废弃)
     */
    public function eleMoreCommissionAddMoney($last_month_time)
    {
        //拼接api
        $client = new Client();
        $login_url = 'http://pt.qmshidai.com/api/ali_ele_more_count_add_money';

        //所需参数
        $post_api_data = [
            'last_month_time' => $last_month_time,        #上月时间范围
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