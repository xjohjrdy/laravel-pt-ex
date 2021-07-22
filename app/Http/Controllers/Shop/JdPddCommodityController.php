<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\App\GrowthUserValueConfig;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\JdCommodity\JdCommodityServices;
use App\Services\PddCommodity\PddCommodityServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class JdPddCommodityController extends Controller
{
    private $vip_percent = 0.325;
    private $common_percent = 0.2;
    private $share_vip_percent = 0.1;
    private $share_common_percent = 0.05;

    /*
     * 京东商品列表
     */
    public function jdCommodityList(Request $request, JdCommodityServices $jdCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $data = [];
            if (!empty($arrRequest['pageindex'])) $data['pageindex'] = $arrRequest['pageindex'];
            if (!empty($arrRequest['keyword'])) $data['keyword'] = $arrRequest['keyword'];
            if (!empty($arrRequest['sortname'])) $data['sortname'] = $arrRequest['sortname'];
            if (!empty($arrRequest['sort'])) $data['sort'] = $arrRequest['sort'];
            $res = $jdCommodityServices->goodsList($data);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }
            $data = @$arr_res['data']['data'];
            if ($data) {
                foreach ($data as &$v) {
                    $v['icon'] = '';
                    $v['tkmoney_general'] = (string)round($v['commission'] * $this->common_percent, 2);
                    $v['tkmoney_vip'] = (string)round($v['commission'] * $this->vip_percent, 2);
                    $v['discount'] = (string)($v['discount'] * 1);
                    $v['price_after'] = (string)($v['price_after'] * 1);
                }
            } else {
                $data = [];
            }
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 京东商品详情
     */
    public function jdGoodsDetail(Request $request, JdCommodityServices $jdCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'goods_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $res = $jdCommodityServices->goodsDetail($arrRequest['goods_id']);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }
            $data = @$arr_res['data'];
            $data['icon'] = '';
            $data['tkmoney_general'] = (string)round($data['commission'] * $this->common_percent, 2);
            $data['tkmoney_vip'] = (string)round($data['commission'] * $this->vip_percent, 2);
            $data['share_tkmoney_general'] = (string)round($data['commission'] * $this->share_common_percent, 2);
            $data['share_tkmoney_vip'] = (string)round($data['commission'] * $this->share_vip_percent, 2);
            $data['price'] = (string)$data['price'];
            $data['price_pg'] = (string)$data['price_pg'];
            $data['price_after'] = (string)$data['price_after'];
            $data['discount'] = (string)$data['discount'];
            $obj_growth_user_value_Config = new GrowthUserValueConfig();
            $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
            $data['growth_value_new_vip'] = round($data['tkmoney_vip'] / $num_growth_value, 2);
            $data['growth_value_new_normal'] = round($data['tkmoney_general'] / $num_growth_value, 2);

            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 京东商品转链
     */
    public function jdUnionUrl(Request $request, JdCommodityServices $jdCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
                'goods_id' => 'required',
                'type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $couponurl = empty($arrRequest['couponurl']) ? '' : urlencode($arrRequest['couponurl']);
            $res = $jdCommodityServices->getUnionUrl($arrRequest['goods_id'], $arrRequest['app_id'], $arrRequest['type'], $couponurl);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }
            return $this->getResponse(@$arr_res['data']);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
    /*
       * 京东商品转链
       */
    public function jdUnionUrlMini(Request $request, JdCommodityServices $jdCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
                'goods_id' => 'required',
                'type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $couponurl = empty($arrRequest['couponurl']) ? '' : $arrRequest['couponurl'];
            $res = $jdCommodityServices->getUnionUrl($arrRequest['goods_id'], $arrRequest['app_id'], $arrRequest['type'], $couponurl);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }
            return $this->getResponse(@$arr_res);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 拼多多商品列表
     */
    public function pddCommodityList(Request $request, PddCommodityServices $pddCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $data = [];
            if (!empty($arrRequest['page'])) $data['page'] = $arrRequest['page'];
            if (!empty($arrRequest['keyword'])) $data['keyword'] = $arrRequest['keyword'];
            if (!empty($arrRequest['sort_type'])) $data['sort_type'] = $arrRequest['sort_type'];
            $res = $pddCommodityServices->goodsList($data);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }

            $data = @$arr_res['data']['goods_list'];
            if ($data) {
                foreach ($data as &$v) {
                    $v['icon'] = '';
                    $v['tkmoney_general'] = (string)round($v['commission'] * $this->common_percent, 2);
                    $v['tkmoney_vip'] = (string)round($v['commission'] * $this->vip_percent, 2);
                    $v['discount'] = (string)($v['discount'] * 1);
                    $v['commission'] = (string)$v['commission'];
                    $v['price'] = (string)$v['price'];
                    $v['price_pg'] = (string)$v['price_pg'];
                    $v['price_after'] = (string)$v['price_after'];
                    $v['discount'] = (string)$v['discount'];
                    if (is_array($v['picurls'])) {
                        $v['picurls'] = implode(',', $v['picurls']);
                    }
                }
            } else {
                $data = [];
            }
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 拼多多商品详情
     */
    public function pddGoodsDetail(Request $request, PddCommodityServices $pddCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'goods_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $res = $pddCommodityServices->goodsDetail($arrRequest['goods_id']);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }
            $data = @$arr_res['data'];
            $data['icon'] = '';
            $data['commission'] = (string)$data['commission'];
            $data['tkmoney_general'] = (string)round($data['commission'] * $this->common_percent, 2);
            $data['tkmoney_vip'] = (string)round($data['commission'] * $this->vip_percent, 2);
            $data['share_tkmoney_general'] = (string)round($data['commission'] * $this->share_common_percent, 2);
            $data['share_tkmoney_vip'] = (string)round($data['commission'] * $this->share_vip_percent, 2);
            $data['price'] = (string)$data['price'];
            $data['price_pg'] = (string)$data['price_pg'];
            $data['price_after'] = (string)$data['price_after'];
            $data['discount'] = (string)$data['discount'];
            $data['picurls'] = implode(',', $data['picurls']);
            $obj_growth_user_value_Config = new GrowthUserValueConfig();
            $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
            $data['growth_value_new_vip'] = round($data['tkmoney_vip'] / $num_growth_value, 2);
            $data['growth_value_new_normal'] = round($data['tkmoney_general'] / $num_growth_value, 2);

            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 拼多多商品转链
     */
    public function pddUnionUrl(Request $request, PddCommodityServices $pddCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
                'goods_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $res = $pddCommodityServices->getUnionUrl($arrRequest['goods_id'], $arrRequest['app_id']);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }
            return $this->getResponse(@$arr_res['data']['data']);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
    /*
        * 拼多多商品转链
        */
    public function pddUnionUrlMini(Request $request, PddCommodityServices $pddCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
                'goods_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $res = $pddCommodityServices->getUnionUrl($arrRequest['goods_id'], $arrRequest['app_id']);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }
            return $this->getResponse(@$arr_res['data']);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
    /*
     * 拼多多订单查询
     */
    public function pddGetOrder(Request $request, PddCommodityServices $pddCommodityServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
                'start_update_time' => 'required',
                'end_update_time' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $page = empty($arrRequest['page']) ? '' : $arrRequest['page'];
            $page_size = empty($arrRequest['page_size']) ? '' : $arrRequest['page_size'];
            $res = $pddCommodityServices->getOrder($arrRequest['start_update_time'], $arrRequest['start_update_time'], $page, $page_size);
            $arr_res = json_decode($res, true);
            if ($arr_res['status_code'] != 200) {
                return $this->getInfoResponse('1001', $arr_res['message']);
            }
            return $this->getResponse(@$arr_res['data']);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}