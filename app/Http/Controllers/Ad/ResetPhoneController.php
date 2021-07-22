<?php

namespace App\Http\Controllers\Ad;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\VoipAccount;
use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ResetPhoneController extends Controller
{
    public function resetPhone(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'code' => 'required',
                'app_id' => 'required',
                'phone' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $p_code = $arrRequest['code'];
            $p_app_id = $arrRequest['app_id'];
            $p_phone = $arrRequest['phone'];
            if (!Cache::has($arrRequest['phone'])) {
                return $this->getInfoResponse('4004', '手机不存在验证码！');
            }
            $r_code = Cache::get($arrRequest['phone']);
            if ($r_code != $arrRequest['code']) {
                return $this->getInfoResponse('4000', '验证码错误或过期，请重新获取！');
            }

            $obj_app_user = new AppUserInfo();

            if ($obj_app_user->where('phone', $p_phone)->exists()) {
                return $this->getInfoResponse('4002', '该手机号已被绑定，请更新其他手机号！');
            }

            $obj_ad_user = new AdUserInfo();
            if (!$obj_ad_user->where('pt_id', $p_app_id)->exists()) {
                return $this->getInfoResponse('4006', '您还没有注册过广告联盟，不允许修改手机号！');
            }
            if ($obj_ad_user->where('username', $p_phone)->exists()) {
                return $this->getInfoResponse('4002', '该手机号已被其他广告联盟账户绑定，请更新其他手机号！');
            }

            $obj_voip_account = new VoipAccount();
            if (!$obj_voip_account->where('app_id', $p_app_id)->exists()) {
                return $this->getInfoResponse('4006', '请先点击葡萄通讯后，再修改手机号');
            }

            if ($obj_voip_account->where('phone', $p_phone)->exists()) {
                return $this->getInfoResponse('4002', '该手机号已被其他通讯账户绑定，请更新其他手机号！');
            }
            $obj_ad_user->where('pt_id', $p_app_id)->update(['username' => $p_phone]);
            $obj_app_user->where('id', $p_app_id)->update(['phone' => $p_phone]);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

        return $this->getResponse('请求成功');

    }

}
