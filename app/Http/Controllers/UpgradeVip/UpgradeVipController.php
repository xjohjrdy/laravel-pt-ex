<?php

namespace App\Http\Controllers\UpgradeVip;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\AlimamaInfoNew;
use App\Entitys\App\JsonConfig;
use App\Entitys\App\NewNewCollection;
use App\Entitys\App\OneGoAlimamaInfo;
use App\Entitys\App\UserOrderNew;
use App\Entitys\App\UserOrderTao;
use App\Exceptions\ApiException;
use App\Services\Alimama\AlimamaService;
use App\Services\Alimama\BigWashUser;
use App\Entitys\App\TaobaoBanner;
use App\Entitys\App\TaobaoZeroBuy;
use App\Services\Alimama\GoodWarehouse;
use App\Services\UpgradeVip\UpgradeVipService;
use App\Services\TbkCashCreate\TbkCashCreateServices;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class UpgradeVipController extends Controller
{
    /*
     * 升级vip商品搜索列表页
     */
    public function getUpgradeVipSearch(Request $request)
    {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
        $post_data = json_decode($request->data, true);
        $rules = [
            'keyword' => 'required',
            'min_id' => 'integer',
            'sort' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]),
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $service_alimama = new UpgradeVipService();

        $sort_val = [
            2 => 'total_sales_des', //销量降序
            3 => 'total_sales_asc', //销量升序
            4 => 'price_des',       //价格降序
            5 => 'price_asc',       //价格升序
            6 => 'tk_total_commi_des',       //佣金降序
            7 => 'tk_total_commi_asc',       //佣金升序
        ];

        $params = [
            'q' => $post_data['keyword'],
            'ip' => $request->ip(),
            'page_no' => $post_data['min_id'],
//            'has_coupon' => 'true',
        ];
        if (!empty($post_data['has_coupon'])) {
            $params['has_coupon'] = 'true';
        }

        if (in_array($post_data['sort'], [2, 3, 4, 5, 6, 7])) {
            $params['sort'] = $sort_val[$post_data['sort']];
        }

        //拼接参数为字符串 设置缓存
        $cache_str = $post_data['keyword'] . "_" . $post_data['min_id'] . "_" . $post_data['sort'];
        $cache_str = md5($cache_str);
        if (Cache::has($cache_str)) {
            $goods_data = Cache::get($cache_str);
            return $this->getResponse($goods_data);
        }

        $goods_data = $service_alimama->dgSearch($params);
        Cache::put($cache_str, $goods_data, 1);

        if (empty($goods_data)) {
            return $this->getInfoResponse('1001', '手速太快，请稍等片刻~');
        }
        return $this->getResponse($goods_data);
    }

    /*
     * 升级vip商品详情
     * 猜你喜欢
     */
    public function getUpgradeVipDetail(Request $request)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'itemid' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $app_id = empty($post_data['app_id']) ? 0 : $post_data['app_id'];
        $itemid = $post_data['itemid'];

        $service_dataoke = new UpgradeVipService();

        $params = [
            'goodsId' => $post_data['itemid'],
        ];

        $goods_info = $service_dataoke->goodsDetailsImg($params);

        if (empty($goods_info)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }
        //lc_taobao_new_new_collection
        //是否为收藏商品
//        $obj_new_new_Collection = new NewNewCollection();
//        $goods_info['is_collection'] = 0;
//        if (!empty($app_id)) {
//            $goods_info['is_collection'] = (int)$obj_new_new_Collection->where(['app_id' => $app_id, 'good_id' => $itemid])->value('id');
//        }

        //brand_name
        $params = [
            'q' => $goods_info['brand_name'],
            'ip' => $request->ip(),
            'page_no' => 1,
        ];

        $service_alimama = new UpgradeVipService();
        $goods_data = $service_alimama->dgSearch($params);
        $goods_info["guess_like"] = $goods_data;

        $goods_info["share_api_url"] = 'http://ax.k5it.com/h5_share/#/';

        return $this->getResponse($goods_info);


        //格式
//        $detail = [
//            'title' => '', //标题
//            'many_img' => '', //图片
//            'store_from' => '', //天猫还是淘宝
//            'sale_number' => '', //销量
//            'coupon_price' => '', //卷后价
//            'price' => '', //普通价
//            'tkmoney_general' => '', //预估报销
//            'tkmoney_vip' => '', //vip预估报销
//            'coupon' => '', //优惠卷金额
//            'coupon_start_time' => '', //优惠卷开始时间
//            'coupon_end_time' => '', //优惠卷结束时间
//            'detail_img' => '', //详情图
//            'guess_like' => [
//                [
//                    'title' => '', //标题
//                    'img' => '', //图片
//                    'store_from' => '', //天猫还是淘宝
//                    'coupon_price' => '', //卷后价
//                    'price' => '', //普通价
//                    'tkmoney_general' => '', //预估报销
//                    'tkmoney_vip' => '', //vip预估报销
//                    'sale_number' => '', //销量
//                ]
//            ]
//        ];
    }

    /*
     * 升级vip商品最新版转链20191105
     */
    public function getUpgradeVipGoodsUrl(Request $request, AlimamaInfoNew $alimamaInfoNew)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }
        $app_id = $request->get('app_id');
        if (empty($app_id)) {
            throw new ApiException('缺少必要参数！,错误信息', 3002);
        }

        $service_dataoke = new BigWashUser();

        $params = [
            'goodsId' => $itemid,
        ];
        $rid = $alimamaInfoNew->where('app_id', $app_id)->first();

        if (empty($rid)) {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
        }
        $share_url_change = $service_dataoke->newManyUrlChange($params, $rid->adzone_id);
        $joint_share_url_change = $share_url_change . '&relationId=' . $rid->relation_id;

        if (empty($share_url_change)) {
            return $this->getInfoResponse('4004', '该商品没有对应的链接！');
        }

        if (empty($joint_share_url_change)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        return $this->getResponse($joint_share_url_change);
    }

}
