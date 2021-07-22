<?php

namespace App\Http\Controllers\Jd;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\JdEnterOrders;
use App\Entitys\App\JdEnterOrdersFirst;
use App\Entitys\App\JdMaidOld;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\JdCommodity\JdCommodityServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PredictionController extends Controller
{

    private $status_change_show = [
        '2' => '无效-拆单',
        '3' => '无效-取消',
        '4' => '无效-京东帮帮主订单',
        '5' => '无效-账号异常',
        '6' => '无效-赠品类目不返佣',
        '7' => '无效-校园订单',
        '8' => '无效-企业订单',
        '9' => '无效-团购订单',
        '10' => '无效-开增值税专用发票订单',
        '11' => '无效-乡村推广员下单',
        '12' => '无效-自己推广自己下单',
        '13' => '无效-违规订单',
        '14' => '无效-来源与备案网址不符',
        '15' => '待付款',
        '-1' => '未知',
        '16' => '已付款',
        '17' => '已完成',
        '18' => '已结算',
    ];

    private $vip_percent = 0.645;
    private $common_percent = 0.42;
    //

    /**
     * 获取京东的预估报销，以全部的形式
     */
    public function getPredictionLog(Request $request, JdMaidOld $jdMaidOld)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'need_time' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //maybe bug
//            $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2592000);

            if (date('m', $arrRequest['need_time']) == '2') {
                $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2419200);
            } else {
                $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2592000);
            }

            $prediction_now_2 = $jdMaidOld->getTime($arrRequest['app_id'], 2, $date_time);
            $prediction_now_1 = $jdMaidOld->getTime($arrRequest['app_id'], 1, $date_time);

            $my_special = $jdMaidOld->getTimeMySpecial($arrRequest['app_id'], 1, $date_time);

            return $this->getResponse([
                'jd_two_person' => $prediction_now_2,
                'jd_three_prediction' => empty($my_special) ? 0 : $my_special,
                'jd_four_no_push' => $prediction_now_1 - $my_special,
            ]);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * get jd 订单以全部的形式
     */
    public function getOrders(Request $request, JdEnterOrders $jdEnterOrders, JdCommodityServices $jdCommodityServices)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required', //0,1,2,3
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = [0];
            if ($arrRequest['status'] == 0) {
                $status = [16];
            }

            if ($arrRequest['status'] == 1) {
                $status = [17, 18];
            }

            if ($arrRequest['status'] == 2) {
                $status = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
            }

            if ($arrRequest['status'] == 4) {
                $status = [-1, 15];
            }


            $orders = $jdEnterOrders->getUserOrders($arrRequest['app_id'], $status);

            $orders = $orders->toArray();

            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {

                $arr_orders[$k]['jd_id'] = $order['orderId'];
                $arr_orders[$k]['good_id'] = $order['skuId'];
                $arr_orders[$k]['status'] = $arrRequest['status'];
                $arr_orders[$k]['status_show'] = $this->status_change_show[$order['validCode']];
                $arr_orders[$k]['title'] = $order['skuName'];

                $jd_good_detail = $jdCommodityServices->goodsDetail($order['skuId'], 0);
                $json_res = json_decode($jd_good_detail, true);
                if (!empty($json_res) && $json_res['status_code'] == 200) {
                    $img = $json_res['data']['picurl'];
                } else {
                    $img = 0;
                }
                $arr_orders[$k]['img'] = $img;
                $arr_orders[$k]['money'] = (string)round($order['estimateCosPrice'], 2);
                $time = substr($order['orderTime'], 0, 10);
                $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);

                $arr_orders[$k]['tkmoney_general'] = (string)round($order['estimateFee'] * $this->common_percent, 2);
                $arr_orders[$k]['tkmoney_vip'] = (string)round($order['estimateFee'] * $this->vip_percent, 2);

            }

            return $this->getResponse([
                'data' => $arr_orders,
                'lastPage' => $orders['last_page'],
                'currentPage' => $orders['current_page'],
            ]);

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * get jd 订单以全部的形式小程序
     */
    public function getOrdersMini(Request $request, JdEnterOrdersFirst $jdEnterOrders, JdCommodityServices $jdCommodityServices)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required', //0,1,2,3
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = [0];
            if ($arrRequest['status'] == 0) {
                $status = [16];
            }

            if ($arrRequest['status'] == 1) {
                $status = [17, 18];
            }

            if ($arrRequest['status'] == 2) {
                $status = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
            }

            if ($arrRequest['status'] == 4) {
                $status = [-1, 15];
            }


            $orders = $jdEnterOrders->getUserOrders($arrRequest['app_id'], $status);

            $orders = $orders->toArray();

            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {

                $arr_orders[$k]['jd_id'] = $order['orderId'];
                $arr_orders[$k]['good_id'] = $order['skuId'];
                $arr_orders[$k]['status'] = $arrRequest['status'];
                $arr_orders[$k]['status_show'] = $this->status_change_show[$order['validCode']];
                $arr_orders[$k]['title'] = $order['skuName'];

                $jd_good_detail = $jdCommodityServices->goodsDetail($order['skuId'], 0);
                $json_res = json_decode($jd_good_detail, true);
                if (!empty($json_res) && $json_res['status_code'] == 200) {
                    $img = $json_res['data']['picurl'];
                } else {
                    $img = 0;
                }
                $arr_orders[$k]['img'] = $img;
                $arr_orders[$k]['money'] = (string)round($order['estimateCosPrice'], 2);
                $time = substr($order['orderTime'], 0, 10);
                $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);

                $arr_orders[$k]['tkmoney_general'] = (string)round($order['estimateFee'] * $this->common_percent, 2);
                $arr_orders[$k]['tkmoney_vip'] = (string)round($order['estimateFee'] * $this->vip_percent, 2);

            }

            return $this->getResponse([
                'data' => $arr_orders,
                'lastPage' => $orders['last_page'],
                'currentPage' => $orders['current_page'],
            ]);

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 团队订单
     * @param Request $request
     * @throws ApiException
     */
    public function getTeamOrders(Request $request, JdMaidOld $jdMaidOld, JdEnterOrders $jdEnterOrders, AppUserInfo $appUserInfo, JdCommodityServices $jdCommodityServices)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required', //0,1,2
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = 0;

            if ($arrRequest['status'] == 0) {
                $status = 0;
            }

            if ($arrRequest['status'] == 1) {
                $status = 1;
            }

            if ($arrRequest['status'] == 2) {
                $status = 2;
            }

            $orders = $jdMaidOld->getTaobaoMaidAll($arrRequest['app_id'], $status);

            $orders = $orders->toArray();
            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $jdEnterOrders->getJdOne($order['trade_id'], $order['sku_id']);
                $app_user = $appUserInfo->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                //理论上不可能出现enter_order不存在的情况
                $arr_orders[$k]['jd_id'] = $enter_order['orderId'];
                $arr_orders[$k]['good_id'] = $enter_order['skuId'];
                $arr_orders[$k]['title'] = $enter_order['skuName'];
                $arr_orders[$k]['money'] = (string)round($enter_order['estimateCosPrice'], 2);
                $time = substr($enter_order['orderTime'], 0, 10);
                $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);
                $arr_orders[$k]['status'] = $arrRequest['status'];

                $arr_orders[$k]['from_id'] = $order['father_id'];
                $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                $arr_orders[$k]['from_is_push'] = $app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                $arr_orders[$k]['tkmoney_general'] = (string)$order['maid_money'];
                $arr_orders[$k]['tkmoney_vip'] = (string)$order['maid_money'];

            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );


        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
