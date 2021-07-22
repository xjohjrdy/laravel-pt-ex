<?php


namespace App\Services\Other;


use GuzzleHttp\Client;

class OtherCountService
{
    protected $domain = 'http://pt.qmshidai.com/api/'; // 统计路由
    public function cardCount($order_id)
    {
        //拼接api
        $client = new Client();
        $login_url = $this->domain . 'card_order_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #主订单order_id
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

    public function pddCount($order_id)
    {
        //拼接api
        $client = new Client();
        $login_url = $this->domain . 'p_order_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #主订单order_id
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

    /**
     * 订单统计
     * @param $order_id
     * @param $sku_id
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function jdCount($order_id, $sku_id)
    {
        //拼接api
        $client = new Client();
        $login_url = $this->domain . 'j_order_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #主订单order_id
            'sku_id' => $sku_id,          #
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

    public function tbCount($order_id)
    {
        //拼接api
        $client = new Client();
        $login_url = $this->domain . 'tb_order_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #主订单order_id
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

    public function tbUnCount($order_id)
    {
        //拼接api
        $client = new Client();
        $login_url = $this->domain . 'tb_un_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #主订单order_id
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

    /**
     * 失效订单统计
     * @param $order_id
     * @param $sku_id
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function jdUnValidateCount($order_id, $sku_id)
    {
        //拼接api
        $client = new Client();
        $login_url = $this->domain . 'jd_un_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #主订单order_id
            'sku_id' => $sku_id,          #
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

    public function pddUnValidateCount($order_id)
    {
        //拼接api
        $client = new Client();
        $login_url = $this->domain . 'pdd_un_count';

        //所需参数
        $post_api_data = [
            'order_id' => $order_id,          #主订单order_id
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