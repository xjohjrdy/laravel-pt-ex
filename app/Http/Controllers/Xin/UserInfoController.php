<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\App\ApplyCash;
use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class UserInfoController extends Controller
{
    /*
     * 修改用户昵称
     */
    public function updateUserName(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'user_name' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $user_name = $arrRequest['user_name'];
            $app_id = $arrRequest['app_id'];
            /***********************************/
            if (strstr($user_name, '我的')) {
                return $this->getInfoResponse('1001', '该名称暂时不支持使用，请重新修改。');
            }
            $res = $appUserInfo->updateUserInfoWithIM($app_id, $user_name);
            if (!$res) {
                return $this->getInfoResponse('1002', '昵称修改失败！');
            }
            return $this->getResponse('昵称修改成功');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 修改用户头像
     */
    public function updateAvatar(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'avatar' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $avatar = $arrRequest['avatar'];
            /***********************************/
            $res = $appUserInfo->updateUserInfoAvatar($app_id, $avatar);
            if (!$res) {
                return $this->getInfoResponse('1001', '头像修改失败！');
            }
            return $this->getResponse('头像修改成功');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     *修改用户真实姓名
     */
    public function updateRealName(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'real_name' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $real_name = $arrRequest['real_name'];
            /***********************************/
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if ($obj_user_info->apply_status == 1) {
                return $this->getInfoResponse('1001', '提现中,不可修改相关信息！');
            }
            $obj_user_info->real_name = $real_name;
            $res = $obj_user_info->save();
            if (!$res) {
                return $this->getInfoResponse('1002', '真实姓名修改失败！');
            }
            return $this->getResponse('真实姓名修改成功');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 修改用户支付宝
     */
    public function updateAlipay(Request $request, ApplyCash $applyCash, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'alipay' => 'required',
                'real_name' => 'required',
                'sms_code' => 'required',
                'phone' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $alipay = $arrRequest['alipay'];
            $real_name = $arrRequest['real_name'];
            $sms_code = $arrRequest['sms_code'];
            $phone = $arrRequest['phone'];
            /***********************************/
            $pattern_account = '/^(\d{2,3}-\d{8,9}|\d{11}|\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14})$/u';
            if (!preg_match($pattern_account, $alipay)) {
                return $this->getInfoResponse('1001', '您的提现支付宝账号有误');
            }
            $pattern = '/^[\x{4e00}-\x{9fa5}·]+$/u';
            if (!preg_match($pattern, $real_name)) {
                return $this->getInfoResponse('1002', '您的名字请不要输入特殊符号');
            }
            if (!Cache::has($phone)) {
                return $this->getInfoResponse('1006', '手机验证码不存在！');
            }
            $r_code = Cache::get($phone);
            if ($r_code != $sms_code) {
                return $this->getInfoResponse('1005', '验证码错误或过期，请重新获取！');
            }
            $res = $applyCash->isWithdraw($app_id);
            if ($res) {
                return $this->getInfoResponse('1003', '提现中,不可修改相关信息');
            }
            $int_id = $appUserInfo->where('alipay', $alipay)->where('id', '<>', $app_id)->value("id");
            if ($int_id) {
                return $this->getInfoResponse('1006', '支付宝账号重复,请重新输入！');
            }
            $bol_update_alipay_info = $appUserInfo->updateAlipayInfo($app_id, $alipay, $real_name);
            if (!$bol_update_alipay_info) {
                return $this->getInfoResponse('1004', '支付宝账号修改失败');
            }
            return $this->getResponse('支付宝账号修改成功');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
