<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\App\ApplyCash;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\UserOrderTao;
use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApplyCashController extends Controller
{
    /*
     * 检查申请提现资格
     */
    public function checkApplyCash(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            /***********************************/
            $obj_user_info = $appUserInfo->getUserAssignInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');
            }
            if (empty($obj_user_info->alipay)) {
                return [
                    'code' => 1002,
                    'msg' => '您的支付宝账号未填写',
                    'data' => [
                        'apply_cash_amount' => $obj_user_info->apply_cash_amount,
                        'bonus_amount' => $obj_user_info->bonus_amount,
                        'order_amount' => $obj_user_info->order_amount,
                    ],
                ];
            }
            if ($obj_user_info->apply_cash_amount < 5) {
                return [
                    'code' => 1003,
                    'msg' => '提现金额不能小于5元',
                    'data' => [
                        'apply_cash_amount' => $obj_user_info->apply_cash_amount,
                        'bonus_amount' => $obj_user_info->bonus_amount,
                        'order_amount' => $obj_user_info->order_amount,
                    ],
                ];
            }
            if ($obj_user_info->order_amount > $obj_user_info->order_can_apply_amount)
                return [
                    'code' => 1004,
                    'msg' => '您的报销额度不足,请联系客服提高额度',
                    'data' => [
                        'apply_cash_amount' => $obj_user_info->apply_cash_amount,
                        'bonus_amount' => $obj_user_info->bonus_amount,
                        'order_amount' => $obj_user_info->order_amount,
                    ],
                ];
            return [
                'code' => 200,
                'msg' => '成功',
                'data' => [
                    'apply_cash_amount' => $obj_user_info->apply_cash_amount,
                    'bonus_amount' => $obj_user_info->bonus_amount,
                    'order_amount' => $obj_user_info->order_amount,
                ],
            ];
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 申请提现
     */
    public function applyCash(Request $request, WechatInfo $wechatInfo, AppUserInfo $appUserInfo, ApplyCash $applyCash, UserOrderTao $userOrderTao)
    {
        return $this->getInfoResponse('4112', '为了提升用户体验，提现功能升级中，带来的不便敬请谅解!');
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'bonus_amount' => 'required',
                'order_amount' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $bonus_amount = $arrRequest['bonus_amount'];
            $order_amount = $arrRequest['order_amount'];
            if (!empty($arrRequest['from_to'])) {
                if ($arrRequest['from_to'] == 1) {
                    return $this->getInfoResponse('5001', '暂不支持支付宝提现！');
                }
            }
            /***********************************/
            $wechat_user_info = $wechatInfo->getAppId($app_id);
            if (empty($wechat_user_info)) {
                return $this->getInfoResponse('4004', '即日起提现需要绑定微信，以微信入账的形式支付，请前往绑定微信');
            }
            $int_check_apply = $appUserInfo->getCheckApply($app_id);
            if (empty($int_check_apply)) {
                return $this->getInfoResponse('1001', '您的支付宝账号未填写');
            }
            $int_dispose_apply_cash = $applyCash->getDisposeApplyCash($app_id);
            if (!empty($int_dispose_apply_cash)) {
                return $this->getInfoResponse('1002', '当前有正在处理中的提现申请,请勿重复申请');
            }
            if (Cache::has('apply_cash_' . $app_id)) {
                return $this->getInfoResponse('1005', '申请频繁，请稍后再试！');
            }
            Cache::put('apply_cash_' . $app_id, 1, 0.25);
            if (($bonus_amount + $order_amount) < 5) {
                return $this->getInfoResponse('1006', '金额小于5元，提现失败');
            }
            if (($bonus_amount + $order_amount) >= 5000) {
                return $this->getInfoResponse('1006', '金额大于5000元，提现失败');
            }
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if ($bonus_amount > $obj_user_info->bonus_amount) {
                return $this->getInfoResponse('1003', '申请失败！');
            }
            if ($order_amount > $obj_user_info->order_amount) {
                return $this->getInfoResponse('1004', '申请失败！');
            }
            $verify_order_total_number = $userOrderTao->getOrderTotalByStatus($obj_user_info->id, 3);
            $confirm_order_total_number = $userOrderTao->getOrderTotalByStatus($obj_user_info->id, 4);
            $arr_apply_cash_data = [
                'user_id' => $obj_user_info->id,
                'real_name' => $obj_user_info->real_name,
                'alipay' => $obj_user_info->alipay,
                'cash_amount' => $bonus_amount + $order_amount,
                'bonus_amount' => $bonus_amount,
                'order_amount' => $order_amount,
                'verify_order_total_number' => $verify_order_total_number,
                'confirm_order_total_number' => $confirm_order_total_number,
                'create_time' => time(),
                'status' => 0,
                'reason' => "",
            ];
            $obj_user_info->apply_status = 1;
            $obj_user_info->bonus_amount -= $arr_apply_cash_data['bonus_amount'];
            $obj_user_info->order_amount -= $arr_apply_cash_data['order_amount'];
            $obj_user_info->apply_cash_amount -= $arr_apply_cash_data['cash_amount'];
            $obj_user_info->order_can_apply_amount -= $arr_apply_cash_data['order_amount'];
            DB::beginTransaction();
            try {
                $applyCash->create($arr_apply_cash_data);
                $obj_user_info->save();
                DB::commit();
                return [
                    'code' => 200,
                    'msg' => '申请成功',
                ];
            } catch (\Exception $e) {
                DB::rollback();
                return [
                    'code' => 1006,
                    'msg' => '申请失败',
                ];
            }
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 提现分红列表
     */
    public function getApplyBonusList(Request $request, ApplyCash $applyCash)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            /***********************************/
            $data['total_bonus_amount'] = $applyCash->getTotalBonusAmount($app_id);
            $data['list'] = $applyCash->getApplyBonusList($app_id);
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
     * 提现明细列表
     */
    public function getApplyCashList(Request $request, ApplyCash $applyCash)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            /***********************************/
            $user_level = AppUserInfo::where('id', $app_id)->value('level');
            $data['level'] = $user_level;
            $data['total_cash_amount'] = $applyCash->getTotalCashAmount($app_id);
            $data['list'] = $applyCash->getApplyCashList($app_id);
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
     * 提现订单报销列表
     */
    public function getApplyOrderList(Request $request, ApplyCash $applyCash)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            /***********************************/
            $data['total_order_amount'] = $applyCash->getTotalOrderAmount($app_id);
            $data['list'] = $applyCash->getApplyOrderList($app_id);
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
