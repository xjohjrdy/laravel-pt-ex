<?php


namespace App\Services\OneGo;


use GuzzleHttp\Client;

class OneGoService
{
    protected $client;
    protected $activeId = 15;
    protected $memberId = '1004023';
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.91fyt.com/index.php/api/v1/hd/',
            'timeout' => 2.0,
        ]);
    }


    /**
     * 获取一分购活动分类
     * @param $id 活动id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCategoryList()
    {
        $header_data = [
            'verify' => false
        ];
        $response = $this->client->request('POST', $this->getUrl('hdgoodscname', []), $header_data);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * 获取一分购商品列表
     */
    public function getGoodsListFromCid($data = [])
    {
        $header_data = [
            'verify' => false
        ];
        $response = $this->client->request('POST', $this->getUrl('hdgoodslist', $data), $header_data);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     *  一分购活动转链接口
     */
    public function getUnionUrlApi($data = [])
    {
        $header_data = [
            'verify' => false
        ];
        $response = $this->client->request('POST', $this->getUrl('hdgetunionurlapi', $data), $header_data);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     *  根据subunionid 即 app_id 查询用户关于本次活动的所有订单
     */
    public function getHdOrders($data = [])
    {
        $header_data = [
            'verify' => false
        ];
        $response = $this->client->request('POST', $this->getUrl('hdorderlistapi', $data), $header_data);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     *  根据orderId 订单号查询活动订单详情
     */
    public function getHdOrdersByOrdersId($orderId, $appId)
    {
        $header_data = [
            'verify' => false
        ];
        $data = [
            'orderid' => $orderId,
            'subunionid' => $appId,
            'yn' => 1,
        ];
        $response = $this->client->request('POST', $this->getUrl('hdorderlistapi', $data), $header_data);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * 获取一分购订单列表
     * @param $page 当前页
     * @param $pageSize 每页大小
     * @param $sTime 开始时间 时间戳（到秒）
     * @param $eTime 结束时间 时间戳（到秒）
     * @param int $type 1下单时间2完成时间 默认1
     * @param int $yn 0无效1有效
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOneGoOrders($page, $pageSize, $sTime, $eTime, $type = 1, $yn = 1)
    {
        $header_data = [
            'verify' => false
        ];
        $data = [
            'pageindex' => $page,
            'pagesize' => $pageSize,
            'yn' => $yn,
            'starttime' => $sTime,
            'endtime' => $eTime,
            'type' => $type,
        ];
        $response = $this->client->request('POST', $this->getUrl('hdorderlistapi', $data), $header_data);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * 拼接公共参数，返回最终请求地址
     * @param array $params 公共请求参数数组
     * @param $end_url 请求url 尾部
     * @return string
     */
    protected function getUrl($end_url, $params = [])
    {
        $params['memberid'] = $this->memberId;
        $params['hdid'] = $this->activeId;
        $str = '?';
        foreach ($params as $key => $value) {
            if(!empty($value)){
                $str = $str. '&' . $key . '=' . $value;
            }
        }
        return $end_url . $str;
    }
}