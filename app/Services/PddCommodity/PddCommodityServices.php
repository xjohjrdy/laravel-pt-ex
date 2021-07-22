<?php

namespace App\Services\PddCommodity;

use GuzzleHttp\Client;

class PddCommodityServices
{
    protected $apikey = '584831d33eaca654';
    protected $url = 'http://api-gw.haojingke.com';

    /*
     * 拼多多商品列表
     */
    public function goodsList($data = [])
    {
        /**参数说明
         * 'page' => $page,                             #可空 第几页（默认第1页）
         * 'page_size' => $page_size,                   #可空 1-100 默认20
         * 'cat_id' => $cat_id,                         #可空 商品类目ID
         * 'opt_id' => $opt_id,                         #可空 商品标签类目ID
         * 'goods_id_list' => $goods_id_list,           #可空 商品ID，多个用英文逗号分隔
         * 'keyword' => $keyword,                       #可空 关键字
         * 'sort_type' => $sort_type,                   #可空 排序方式 详情http://www.haojingke.com/open-api/pdd
         * 'with_coupon' => $with_coupon,               #可空 是否只返回优惠券的商品，0返回所有商品，1只返回有优惠券的商品
         * 'minpgpirce' => $minpgpirce,                 #可空 最小拼购金额
         * 'maxpgpirce' => $maxpgpirce,                 #可空 最大拼购金额
         * 'minprice' => $minprice,                     #可空 最小金额
         * 'maxprice' => $maxprice,                     #可空 最大金额
         * 'mincommission' => $mincommission,           #可空 最小佣金比例
         * 'maxcommission' => $maxcommission,           #可空 最大佣金比例
         * 'mindiscount' => $mindiscount,               #可空 最小优惠券金额
         * 'maxdiscount' => $maxdiscount,               #可空 最大优惠券金额
         * 'mincommissionshare' => $mincommissionshare, #可空 最小佣金比例
         * 'maxcommissionshare' => $maxcommissionshare, #可空 最大佣金比例
         * 'minsale' => $minsale,                       #可空 最小销量
         * 'maxsale' => $maxsale,                       #可空 最大销量
         * 'ispg' => $ispg,                             #可空 是否拼购 1拼购
         * 'merchant_type' => $merchant_type,           #可空 调用拼多多超级搜索（isunion=1）才有效 店铺类型，1-个人，2-企业，3-旗舰店，4-专卖店，5-专营店，6-普通店（未传为全部）
         * 'isunion' => $isunion,                       #可空 isunion=1 表示返回拼多多原接口数据不做处理 返回值参考 官方文档
         */
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/pdd/goodslist';
        $post_api_data = [
            'apikey' => $this->apikey,         #蚂蚁星球apikey
        ];
        $post_api_data = array_merge($post_api_data, $data);
        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 拼多多商品详情
     */
    public function goodsDetail($goods_id, $isunion = '')
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/pdd/goodsdetail';
        @$post_api_data = [
            'apikey' => $this->apikey,  #蚂蚁星球apikey
            'goods_id' => $goods_id,    #goods_id 商品id
        ];
        if (!empty($isunion)) $post_api_data['isunion'] = $isunion; #可空 isunion=1 表示返回拼多多原接口数据不做处理 返回值参考 官方文档

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 拼多多分类列表
     */
    public function getCats($parent_cat_id)
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/pdd/cats';
        @$post_api_data = [
            'apikey' => $this->apikey,          #蚂蚁星球apikey
            'parent_cat_id' => $parent_cat_id   #父类目id(一级父类目为0)
        ];

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 拼多多标签列表
     */
    public function getOpt($parent_opt_id)
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/pdd/opt';
        @$post_api_data = [
            'apikey' => $this->apikey,          #蚂蚁星球apikey
            'parent_opt_id' => $parent_opt_id   #父类目id(一级父类目为0)
        ];

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 拼多多转链api
     */
    public function getUnionUrl($goods_id, $positionid = '')
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/pdd/getunionurl';
        @$post_api_data = [
            'apikey' => $this->apikey,  #蚂蚁星球apikey
            'goods_id' => $goods_id,    #商品id
        ];

        if (!empty($positionid)) $post_api_data['positionid'] = $positionid;#可空 自定义推广位id，字母数字 (50位以内)

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 拼多多订单查询
     */
    public function getOrder($start_update_time, $end_update_time, $page = 1, $page_size = 10)
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/pdd/getorder';
        @$post_api_data = [
            'apikey' => $this->apikey,                  #蚂蚁星球apikey
            'start_update_time' => $start_update_time,  #最近90天内多多进宝商品订单更新时间--查询时间开始。时间戳 （到秒）
            'end_update_time' => $end_update_time,      #查询结束时间，和开始时间相差不能超过24小时。时间戳 （到秒）
            'page' => $page,                            #可空 页码，返回第几页结果
            'page_size' => $page_size,                  #可空 每页包含条数，上限为100
        ];

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }
}
