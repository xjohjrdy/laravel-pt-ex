<?php

namespace App\Http\Controllers\Jd;

use App\Entitys\App\JdGetOneShow;
use App\Entitys\App\JdNewActiveOrders;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class NewController extends Controller
{
    private $member_id = '1004023';
    private $hdid = '7';

    /**
     * 拉类型
     */
    public function getType(Client $client)
    {
        if (Cache::has('jd_yfg_get_type')) {
            return $this->getResponse(Cache::get('jd_yfg_get_type'));
        }


        $url = "https://api.91fyt.com/index.php/api/v1/hd/hdgoodscname?hdid=" . $this->hdid;
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);
        $res_url = @$json_res['data'];
        /**
         * 4740 食品饮料
         * 4726 居家生活
         * 4731 内衣饰品
         * 4727 美妆个护
         * 4733 母婴玩具
         * 4747 箱包鞋品
         * 4738 运动户外
         * 4732 数码家电
         * 4729 医药保健
         * 4737 汽车用品
         */
        $img = [
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%AE%B6%E5%B1%85%E7%94%A8%E5%93%81.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%AE%B6%E5%BA%AD%E6%B8%85%E6%B4%811.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E6%9C%8D%E9%A5%B0%E5%86%85%E8%A1%A3.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%8C%BB%E8%8D%AF%E4%BF%9D%E5%81%A5.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%AE%B6%E8%A3%85%E5%BB%BA%E6%9D%90.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E6%88%B7%E5%A4%96%E8%BF%90%E5%8A%A8.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E6%89%8B%E6%9C%BA%E9%80%9A%E8%AE%AF.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%8E%A8%E5%85%B7.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E6%AF%8D%E5%A9%B4.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E7%8E%A9%E5%85%B7%E4%B9%90%E5%99%A8.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E7%8F%A0%E5%AE%9D%E9%A6%96%E9%A5%B0.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E7%AE%B1%E5%8C%85%E7%9A%AE%E5%85%B7.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E7%BE%8E%E5%AE%B9%E6%8A%A4%E8%82%A4.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E9%A3%9F%E5%93%81%E9%A5%AE%E6%96%99.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%AE%B6%E5%B1%85%E7%94%A8%E5%93%81.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%AE%B6%E5%BA%AD%E6%B8%85%E6%B4%811.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E6%9C%8D%E9%A5%B0%E5%86%85%E8%A1%A3.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%8C%BB%E8%8D%AF%E4%BF%9D%E5%81%A5.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%AE%B6%E8%A3%85%E5%BB%BA%E6%9D%90.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E6%88%B7%E5%A4%96%E8%BF%90%E5%8A%A8.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E6%89%8B%E6%9C%BA%E9%80%9A%E8%AE%AF.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E5%8E%A8%E5%85%B7.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E6%AF%8D%E5%A9%B4.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E7%8E%A9%E5%85%B7%E4%B9%90%E5%99%A8.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E7%8F%A0%E5%AE%9D%E9%A6%96%E9%A5%B0.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E7%AE%B1%E5%8C%85%E7%9A%AE%E5%85%B7.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E7%BE%8E%E5%AE%B9%E6%8A%A4%E8%82%A4.png',
            'http://a119112.oss-cn-beijing.aliyuncs.com/shop/ok/%E9%A3%9F%E5%93%81%E9%A5%AE%E6%96%99.png',
        ];
        foreach ($res_url as $k => $model) {
            $res_url[$k]['img_url'] = $img[$k];
        };
        $resq = [
            'type' => $res_url,
            'time' => 1560700800 - time(),
        ];

        Cache::put('jd_yfg_get_type', $resq, 10);

        return $this->getResponse($resq);
    }

    /**
     * 拉列表
     */
    public function getList(Request $request, Client $client)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'pageindex' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        if (Cache::has('jd_yfg_get_list_' . $arrRequest['pageindex'] . '_' . @$arrRequest['cid'])) {
            return $this->getResponse(Cache::get('jd_yfg_get_list_' . $arrRequest['pageindex'] . '_' . @$arrRequest['cid']));
        }

        if (empty($arrRequest['cid'])) {
            $url = "https://api.91fyt.com/index.php/api/v1/hd/hdgoodscname?hdid=" . $this->hdid;
            $obj_res = $client->request('POST', $url, ['verify' => false]);
            $json_res = json_decode((string)$obj_res->getBody(), true);
            $res_url = @$json_res['data'];
            $arrRequest['cid'] = $res_url[0]['cid'];
        }
        $url = "https://api.91fyt.com/index.php/api/v1/hd/hdgoodslist?hdid=" . $this->hdid . "&pagesize=10&pageindex=" . $arrRequest['pageindex'] . "&cid=" . $arrRequest['cid'];
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);
        $res_url = @$json_res['data'];
        $arr = [];
        foreach ($res_url['data'] as $k => $item) {
            $arr[$k]['img'] = $item['picurl'];
            $arr[$k]['title'] = $item['goods_name'];
            $arr[$k]['sell_number'] = $item['sales'];
            $arr[$k]['goods_id'] = $item['goods_id'];
            $arr[$k]['price_before'] = $item['price'];
            $arr[$k]['price_after'] = $item['price_after'];
            $arr[$k]['easy_price'] = 9.89;
        }
        Cache::put('jd_yfg_get_list_' . $arrRequest['pageindex'] . '_' . @$arrRequest['cid'], $arr, 10);
        return $this->getResponse($arr);
    }

    /**
     * 改变链接
     */
    public function changeUrl(Request $request, Client $client)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'goods_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        if ($arrRequest['app_id'] == 'undefined' || empty($arrRequest['app_id'])) {
            return "<h1>请更新最新版本再使用，否则订单将无效！</h1>";
        }
        $url = "https://api.91fyt.com/index.php/api/v1/hd/hdgetunionurlapi?memberid=" . $this->member_id . "&hdid=" . $this->hdid . "&goods_id=" . $arrRequest['goods_id'] . "&subunionid=" . $arrRequest['app_id'];
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);

        return $this->getResponse($json_res);
    }

    /**
     * 校验订单
     */
    public function checkOrders(Request $request, Client $client, JdNewActiveOrders $jdNewActiveOrders)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'orderid' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $url = "https://api.91fyt.com/index.php/api/v1/hd/hdorderlistapi?orderid=" . $arrRequest['orderid'] . "&yn=1&memberid=" . $this->member_id . "&hdid=" . $this->hdid . "&subunionid=" . $arrRequest['app_id'];
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);
        if (!empty($json_res['data']['total'])) {
            if ($json_res['data']['total'] > 0) {
                $jdNewActiveOrders->addInfo([
                    'app_id' => $arrRequest['app_id'],
                    'orders' => $arrRequest['orderid'],
                ]);
                return $this->getResponse("提交成功");
            }
        }

        return $this->getResponse("订单未找到，需要等一段时间再提交哦！");
    }

    /**
     * 获取所有订单
     * @param Request $request
     * @param Client $client
     */
    public function getOrders(Request $request, Client $client)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $url = "https://api.91fyt.com/index.php/api/v1/hd/hdorderlistapi?memberid=" . $this->member_id . "&hdid=" . $this->hdid . "&subunionid=" . $arrRequest['app_id'];
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);

        $url_h = "https://api.91fyt.com/index.php/api/v1/hd/hdorderlistapi?yn=1&memberid=" . $this->member_id . "&hdid=" . $this->hdid . "&subunionid=" . $arrRequest['app_id'];
        $obj_res_h = $client->request('POST', $url_h, ['verify' => false]);
        $json_res_h = json_decode((string)$obj_res_h->getBody(), true);

        if ($arrRequest['app_id'] == 'undefined' || empty($arrRequest['app_id'])) {
            $json_res['data']['data'] = [];
            $json_res['data']['total'] = 0;
            $json_res_h['data']['total'] = 0;
        }

        return $this->getResponse(
            [
                'can' => $json_res['data']['total'],
                'can_use' => $json_res_h['data']['total'],
                'money' => $json_res_h['data']['total'] * 9.89,
                'get_money' => 0,
                'orders' => $json_res['data']['data']
            ]
        );
    }


    /**
     * 获取所有订单
     * @param Request $request
     * @param Client $client
     */
    public function getOldOrders(Request $request, Client $client)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $url = "https://api.91fyt.com/index.php/api/v1/hd/hdorderlistapi?memberid=" . $this->member_id . "&hdid=3&subunionid=" . $arrRequest['app_id'];
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);

        $url_h = "https://api.91fyt.com/index.php/api/v1/hd/hdorderlistapi?yn=1&memberid=" . $this->member_id . "&hdid=3&subunionid=" . $arrRequest['app_id'];
        $obj_res_h = $client->request('POST', $url_h, ['verify' => false]);
        $json_res_h = json_decode((string)$obj_res_h->getBody(), true);

        if ($arrRequest['app_id'] == 'undefined' || empty($arrRequest['app_id'])) {
            $json_res['data']['data'] = [];
            $json_res['data']['total'] = 0;
            $json_res_h['data']['total'] = 0;
        }


        $new_model = new JdGetOneShow();
        $number_new = $new_model->where(['subunionid' => $arrRequest['app_id']])->count();

        return $this->getResponse(
            [
                'can' => $json_res['data']['total'],
                'can_use' => $json_res_h['data']['total'],
                'money' => $json_res_h['data']['total'] * 9.89,
                'get_money' => $json_res_h['data']['total'] * 9.9,
                'orders' => $json_res['data']['data']
            ]
        );
    }


}
