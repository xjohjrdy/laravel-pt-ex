<?php

namespace App\Services\JdCommodity;

use GuzzleHttp\Client;

class JdCommodityServices
{
    protected $apikey = '584831d33eaca654';
    protected $url = 'http://api-gw.haojingke.com';

    /*
     * 京东商品列表
     */
    public function goodsList($data = [])
    {
        /**参数说明
         * 'pageindex' => $pageindex,         #可空 第几页（默认第1页）
         * 'pagesize' => $pagesize,           #可空 1-100 默认20
         * 'cid1' => $cid1,                   #可空 一级类目id
         * 'cid2' => $cid2,                   #可空 二级类目id
         * 'cid3' => $cid3,                   #可空 三级类目id
         * 'goods_ids' => $goods_ids,         #可空 京东商品ID，多个用英文逗号分隔
         * 'keyword' => $keyword,             #可空 关键字
         * 'minprice' => $minprice,           #可空 最小金额
         * 'maxprice' => $maxprice,           #可空 最大金额
         * 'mincommission' => $mincommission, #可空 最小佣金比例
         * 'maxcommission' => $maxcommission, #可空 最大佣金比例
         * 'sortname' => $sortname,           #可空 1单价 2佣金比例 3佣金 4销量
         * 'sort' => $sort,                   #可空 asc 升序 desc 降序 默认降序
         * 'ispg' => $ispg,                   #可空 是否拼购 1拼购
         * 'iscoupon' => $iscoupon,           #可空 是否有券 1只查有券 其他查全部
         * 'minpgpirce' => $minpgpirce,       #可空 最小拼购金额
         * 'maxpgpirce' => $maxpgpirce,       #可空 最大拼购金额
         * 'ishot' => $ishot,                 #可空 1爆品0非爆品
         * 'brandcode' => $brandcode,         #可空 品牌code
         * 'shopid' => $shopid,               #可空 店铺Id
         * 'isunion' => $isunion,             #可空 isunion=1 表示返回京东原接口数据不做处理 返回值参考京东联盟 官方文档
         */
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/jd/goodslist';
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
     * 京东商品详情
     */
    public function goodsDetail($goods_id, $isunion = '')
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/jd/goodsdetail';
        @$post_api_data = [
            'apikey' => $this->apikey,  #蚂蚁星球apikey
            'goods_id' => $goods_id,    #skuid 商品id
        ];
        if (!empty($isunion)) $post_api_data['isunion'] = $isunion; #可空 isunion=1 表示返回京东原接口数据不做处理 返回值参考京东联盟 官方文档

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 京东分类列表
     */
    public function getCategory($parentId, $grade)
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/jd/getcategory';
        @$post_api_data = [
            'apikey' => $this->apikey, #蚂蚁星球apikey
            'parentId' => $parentId,   #父类目id(一级父类目为0)
            'grade' => $grade,       #类目级别(类目级别 0，1，2 代表一、二、三级类目)
        ];

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 京东转链api
     */
    public function getUnionUrl($goods_id, $positionid, $type, $couponurl = '')
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/jd/getunionurl';
        @$post_api_data = [
            'apikey' => $this->apikey,  #蚂蚁星球apikey
            'goods_id' => $goods_id,    #商品id
            'couponurl' => $couponurl,  #可空 优惠券链接，需要urlencode
            'positionid' => $positionid,#自定义推广位id，整型数字
            'type' => $type,            #type=1 goods_id=商品ID，type=2 goods_id=店铺id，type=3 自定义链接
        ];

        if (empty($couponurl)) {
            unset($post_api_data['couponurl']);
        }

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 京东订单查询
     */
    public function getOrder($time, $type = 3, $pageNo = 1, $pageSize = 10)
    {
        $client = new Client();
        $login_url = $this->url . '/index.php/v1/api/jd/getorder';
        @$post_api_data = [
            'apikey' => $this->apikey,#蚂蚁星球apikey
            'time' => $time,          #查询时间，建议使用分钟级查询，格式：yyyyMMddHH、yyyyMMddHHmm或yyyyMMddHHmmss，如201811031212 的查询范围从12:12:00--12:12:59
            'type' => $type,          #订单时间查询类型(1：下单时间，2：完成时间，3：更新时间) 默认3
            'pageNo' => $pageNo,      #可空 页码，返回第几页结果
            'pageSize' => $pageSize,  #可空 每页包含条数，上限为500
        ];

        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();
    }
}
