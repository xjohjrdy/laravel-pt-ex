<?php

namespace App\Http\Controllers\Pdd;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\PddEnterOrders;
use App\Entitys\App\PddMaidOld;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\PddCommodity\PddCommodityServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MyController extends Controller
{
    /**
     * 订单状态： -1 未支付; 0-已支付；1-已成团；2-确认收货；3-审核成功；4-审核失败（不可提现）；5-已经结算；8-非多多进宝商品（无佣金订单）
     * @var array
     */
    private $status_change_show = [
        '-1' => '未支付',
        '0' => '已支付',
        '1' => '已成团',
        '2' => '确认收货',
        '3' => '审核成功',
        '4' => '审核失败',
        '5' => '已经结算',
        '8' => '非报销商品（无佣金订单）',
    ];

    private $vip_percent = 0.645;
    private $common_percent = 0.42;

    /**
     * 获取京东的预估报销，以全部的形式
     */
    public function getPddPredictionLog(Request $request, PddMaidOld $pddMaidOld)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'need_time' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
//            $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2592000);

            if (date('m', $arrRequest['need_time']) == '2') {
                $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2419200);
            } else {
                $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2592000);
            }


            $prediction_now_2 = $pddMaidOld->getTime($arrRequest['app_id'], 2, $date_time);
            $prediction_now_1 = $pddMaidOld->getTime($arrRequest['app_id'], 1, $date_time);

            $my_special = $pddMaidOld->getTimeMySpecial($arrRequest['app_id'], 1, $date_time);

            return $this->getResponse([
                'pdd_two_person' => $prediction_now_2,
                'pdd_three_prediction' => empty($my_special) ? 0 : $my_special,
                'pdd_four_no_push' => $prediction_now_1 - $my_special,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * get pdd 根据订单状态获取用户订单
     */
    public function getOrders(Request $request, PddEnterOrders $pddEnterOrders)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = [0];
            if ($arrRequest['status'] == 0) {
                $status = [-1, 0];
            }

            if ($arrRequest['status'] == 1) {
                $status = [1, 2, 3, 5];
            }

            if ($arrRequest['status'] == 2) {
                $status = [4];
            }

            if ($arrRequest['status'] == 4) {
                $status = [8];
            }
            $orders = $pddEnterOrders->getUserOrders($arrRequest['app_id'], $status);
            $orders = $orders->toArray();
            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $arr_orders[$k]['pdd_id'] = $order['order_sn'];
                $arr_orders[$k]['good_id'] = $order['goods_id'];
                $arr_orders[$k]['status'] = $arrRequest['status'];
                $arr_orders[$k]['status_show'] = $order['order_status_desc'];
                $arr_orders[$k]['title'] = $order['goods_name'];
                $arr_orders[$k]['img'] = $order['goods_thumbnail_url'];
                $arr_orders[$k]['money'] = round($order['order_amount'] / 100, 2);
                $time = substr($order['order_create_time'], 0, 10);
                $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);
                $arr_orders[$k]['tkmoney_general'] = round(($order['promotion_amount'] / 100) * $this->common_percent, 2);
                $arr_orders[$k]['tkmoney_vip'] = round(($order['promotion_amount'] / 100) * $this->vip_percent, 2);

            }

            return $this->getResponse([
                'data' => $arr_orders,
                'lastPage' => $orders['last_page'],
                'currentPage' => $orders['current_page'],
            ]);

        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 团队订单
     */
    public function getTeamOrders(Request $request, PddMaidOld $pddMaidOld, PddEnterOrders $pddEnterOrders, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
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
            $orders = $pddMaidOld->getPddMaidAll($arrRequest['app_id'], $status);

            $orders = $orders->toArray();
            $arr_orders = [];

            foreach ($orders['data'] as $k => $order) {
                $enter_order = $pddEnterOrders->getPddOne($order['trade_id'], $order['father_id']);
                $app_user = $appUserInfo->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                $arr_orders[$k]['pdd_id'] = $enter_order['order_sn'];
                $arr_orders[$k]['status'] = $arrRequest['status'];
                $arr_orders[$k]['status_show'] = $enter_order['order_status_desc'];
                $arr_orders[$k]['title'] = $enter_order['goods_name'];
                $arr_orders[$k]['money'] = round($enter_order['order_amount'] / 100, 2);
                $time = substr($enter_order['order_create_time'], 0, 10);
                $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);

                $arr_orders[$k]['from_id'] = $order['father_id'];
                $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                $arr_orders[$k]['from_is_push'] = $app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];

            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );


        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
