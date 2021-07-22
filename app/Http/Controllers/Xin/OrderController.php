<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\App\TaobaoEnterOrder;
use App\Entitys\App\UserOrderNew;
use App\Entitys\App\UserOrderTao;
use App\Entitys\Xin\Config;
use App\Entitys\Xin\TaobaoData;
use App\Exceptions\ApiException;
use App\Services\Common\Time;
use App\Services\JPush\JPush;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /*
     * 提交淘宝订单号老表user_order
     */
    public function submitOrderNumber(Request $request, UserOrderTao $userOrderTao, UserOrderNew $userOrderNew, TaobaoData $taobaoData, Config $config)
    {
        try {
            return $this->getInfoResponse('1008', '由于系统升级，此处无法提交订单，请前往【淘报销】—点击右上角图标—【我的订单】—疑异订单导入页—进入提交订单；！');
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'order_number' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_number = (string)$arrRequest['order_number'];
            $is_normal = $arrRequest['is_normal'];
            /***********************************/
            $pattern_account = '/^\d{16,}$/i';
            if (!preg_match($pattern_account, $order_number)) {
                return $this->getInfoResponse('1007', '您的订单号格式输入错误！');
            }
            if ($userOrderNew->where(['order_number' => $order_number])->first()) {
                return $this->getInfoResponse('1002', '订单重复，提交失败');
            }
            if ($userOrderTao->where(['order_number' => $order_number])->first()) {
                return $this->getInfoResponse('1002', '订单重复，提交失败');
            }
            $int_today_timestamp = Time::getTodayTimestamp();

            $int_order_num = $userOrderTao->where('user_id', $arrRequest['app_id'])->where('create_time', '>=', $int_today_timestamp)->count();
            if ($int_order_num >= 20) {
                return $this->getInfoResponse('1003', '当天订单数不能超过20单，提交失败');
            }
            $order_data['is_normal'] = $is_normal;
            $order_data['order_number'] = (string)$arrRequest['order_number'];
            $order_data['user_id'] = $arrRequest['app_id'];
            $order_data['cashback_amount'] = 0.00;
            $order_data['reason'] = " ";
            $order_data['create_time'] = time();
            $order_data['update_time'] = time();
            $res = $userOrderTao->insert($order_data);
            if (!$res) {
                return $this->getInfoResponse('1001', '提交失败');
            }
            return $this->getResponse("提交成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 根据订单状态调整数据
     */
    protected function makeOrderData($order_data, $taobao, $cashback_percent, $is_normal)
    {
        $order_data['is_normal'] = $is_normal;
        $order_data['order_number'] = (string)$order_data['order_number'];
        $order_data['user_id'] = $order_data['app_id'];
        $order_data['cashback_amount'] = 0.00;
        $order_data['reason'] = " ";
        $order_data['create_time'] = time();
        $order_data['update_time'] = time();
        $order_data['status'] = empty($order_data['status']) ? 0 : $order_data['status'];
        if ($order_data['status'] == 0 && in_array($taobao['status'], [1, 2])) {
            $order_data['confirm_time'] = time();
        }
        if ($taobao['status'] == 1) {
            $cashback_amount = round($cashback_percent * $taobao['commission'], 2);
            $cashback_amount = $cashback_amount > 0 ? $cashback_amount : 0.01;
            $cashback_amount = $taobao['commission'] > 0 ? $cashback_amount : 1.6;
            $order_data['status'] = 3;
            $order_data['cashback_amount'] = $cashback_amount;
        }
        if ($taobao['status'] == 3) {
            $order_data['status'] = 2;
            $order_data['cashback_amount'] = 0;
            $order_data['reason'] = '淘宝返回数据,显示订单失效';
        }
        if ($taobao['status'] == 2) {
            $order_data['status'] = 4;
            $cashback_amount = round($cashback_percent * $taobao['commission'], 2);
            $cashback_amount = $cashback_amount > 0 ? $cashback_amount : 0.01;
            $cashback_amount = $taobao['commission'] > 0 ? $cashback_amount : 1.6;
            $order_data['cashback_amount'] = $cashback_amount;
            $order_data['confirm_receipt_time'] = $taobao['taobao_time'];
        }
        return $order_data;
    }

    /*
     * 批量提交订单
     */
    public function submitListOrderNumber(Request $request)
    {

    }

    /*
     * 提交淘宝订单号新表
     */
    public function newSubmitOrderNumber(Request $request, UserOrderTao $userOrderTao, UserOrderNew $userOrderNew, TaobaoData $taobaoData, Config $config)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'order_number' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_number = (string)$arrRequest['order_number'];
            $is_normal = $arrRequest['is_normal'];
            /***********************************/
            $pattern_account = '/^\d{16,}$/i';
            if (!preg_match($pattern_account, $order_number)) {
                return $this->getInfoResponse('1007', '您的订单号格式输入错误！');
            }

            if ($is_normal == 2) {
                $obj_enter = new TaobaoEnterOrder();
                $obj_order_info = $obj_enter->getOneOrders($order_number);
                if (empty($obj_order_info)) {
                    return $this->getInfoResponse('1002', '亲~ 很抱歉！ 该订单不符合报销条件，若下单后20分钟订单仍不符合报销条件，建议您尽快退 货，并重新通过我的浏览器下单；感谢您的理解与支持！');
                }
            }
            if ($userOrderNew->where(['order_number' => $order_number])->first()) {
                return $this->getInfoResponse('1002', '订单重复，提交失败');
            }
            if ($userOrderTao->where(['order_number' => $order_number])->first()) {
                return $this->getInfoResponse('1002', '订单重复，提交失败');
            }
            $int_today_timestamp = Time::getTodayTimestamp();

            $int_order_num = $userOrderNew->where('user_id', $arrRequest['app_id'])->where('create_time', '>=', $int_today_timestamp)->count();
            if ($int_order_num >= 20) {
                return $this->getInfoResponse('1003', '当天订单数不能超过20单，提交失败');
            }
            $order_data['is_normal'] = $is_normal;
            $order_data['order_number'] = (string)$arrRequest['order_number'];
            $order_data['user_id'] = $arrRequest['app_id'];
            $order_data['cashback_amount'] = 0.00;
            $order_data['reason'] = " ";
            $order_data['create_time'] = time();
            $order_data['update_time'] = time();
            $res = $userOrderNew->insert($order_data);
            if (!$res) {
                return $this->getInfoResponse('1001', '提交失败');
            }

            if ($is_normal == 2) {

                $user_order_status = [
                    '3' => 2,
                    '12' => 1,
                    '13' => 3,
                    '14' => 1
                ];
                $this->reviewOrder([
                    [
                        'status' => @$user_order_status[$obj_order_info->tk_status],
                        'order_number' => $obj_order_info->trade_id,
                        'commission' => $obj_order_info->pub_share_pre_fee,
                        'taobao_time' => strtotime($obj_order_info->create_time),
                    ]
                ]);

                return $this->getResponse("该订单符合报销条件，已加入到【审核成功】列表！");
            }


            return $this->getResponse("提交成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 根据订单状态调整数据
     */
    protected function newMakeOrderData($order_data, $taobao, $cashback_percent, $is_normal)
    {
        $order_data['is_normal'] = $is_normal;
        $order_data['order_number'] = (string)$order_data['order_number'];
        $order_data['user_id'] = $order_data['app_id'];
        $order_data['cashback_amount'] = 0.00;
        $order_data['reason'] = " ";
        $order_data['create_time'] = time();
        $order_data['update_time'] = time();
        $order_data['status'] = empty($order_data['status']) ? 0 : $order_data['status'];
        if ($order_data['status'] == 0 && in_array($taobao['status'], [1, 2])) {
            $order_data['confirm_time'] = time();
        }
        if ($taobao['status'] == 1) {
            $cashback_amount = round($cashback_percent * $taobao['commission'], 2);
            $cashback_amount = $cashback_amount > 0 ? $cashback_amount : 0.01;
            $cashback_amount = $taobao['commission'] > 0 ? $cashback_amount : 1.6;
            $order_data['status'] = 3;
            $order_data['cashback_amount'] = $cashback_amount;
        }
        if ($taobao['status'] == 3) {
            $order_data['status'] = 2;
            $order_data['cashback_amount'] = 0;
            $order_data['reason'] = '淘宝返回数据,显示订单失效';
        }
        if ($taobao['status'] == 2) {
            $order_data['status'] = 4;
            $cashback_amount = round($cashback_percent * $taobao['commission'], 2);
            $cashback_amount = $cashback_amount > 0 ? $cashback_amount : 0.01;
            $cashback_amount = $taobao['commission'] > 0 ? $cashback_amount : 1.6;
            $order_data['cashback_amount'] = $cashback_amount;
            $order_data['confirm_receipt_time'] = $taobao['taobao_time'];
        }
        return $order_data;
    }


    /*
     * review of the order
     */
    private function reviewOrder($real_data)
    {
        $post_api_data = [
            'code' => 200,
            'data' => $real_data,
            'msg' => 'RYT',
            'count' => '4',
            'total' => '4',
        ];
        $json_post_data = ($post_api_data);
        $url = "http://api.36qq.com/api/ali_sync_gather_order";
        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'tokk' => '64d8ea7cf1dc5710d61e373d34f69e23',
            ],
            'json' => $json_post_data
        ];
        $client = new Client();
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $arr_res = json_decode($json_res, true);
        return $arr_res;

    }
}
