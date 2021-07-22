<?php

namespace App\Http\Controllers\App;

use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use App\Services\Wechat\WechatPay;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yansongda\Pay\Pay;

class InController extends Controller
{
    /**
     *
     */
    public function getWechatIn(Request $request, WechatInfo $wechatInfo, WechatPay $wechatPay)
    {

        $arr = [
            '39.106.6.68',
            '127.0.0.1',
            '110.87.32.189',
        ];
        if (!in_array($request->ip(), $arr)) {
            throw new ApiException('您不符合我们的安全机制！');
        }

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'real_name' => 'required',
                'price' => 'required',
                'type' => 'required',
                'order_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $wechat_user_info = $wechatInfo->where(['app_id' => $arrRequest['app_id']])->first();

            if (empty($wechat_user_info)) {
                return $this->getInfoResponse('4004', '无对应微信账号');
            }

            $order_id = 0;

            if ($arrRequest['type'] == 1) {
                $desc = "提现";
                $order_id = "PTBTX" . $arrRequest['order_id'];
            }
            if ($arrRequest['type'] == 2) {
                $desc = "我的浏览器余额提现";
                $order_id = "BXTX" . $arrRequest['order_id'];
            }
            if ($arrRequest['type'] == 3) {
                $desc = "分红报销提现";
                $order_id = "FHBXTX" . $arrRequest['order_id'];
            }
            if ($arrRequest['type'] == 4) {
                $desc = "我的商城退款";
                $order_id = "TKTX" . $arrRequest['order_id'];
            }

            if (empty($order_id)) {
                return $this->getInfoResponse('4004', '结构订单异常');
            }

            $res = $wechatPay->pay($wechat_user_info->openid, $arrRequest['real_name'], $arrRequest['price'], $order_id, $desc);


            if ($res['return_code'] == 'FAIL') {
                return $this->getInfoResponse('5000', $res['return_msg']);
            }

            if ($res['result_code'] == 'FAIL') {
                if ($res['err_code'] == 'SYSTEMERROR') {
                    $res = $wechatPay->pay($wechat_user_info->openid, $arrRequest['real_name'], $arrRequest['price'], $order_id, $desc);
                    Storage::disk('local')->append('callback_document/wuhang_no_wechat_info_in_error.txt', var_export(
                        [
                            'app_id' => $arrRequest['app_id'],
                            'real_name' => $arrRequest['real_name'],
                            'openid' => $wechat_user_info->openid,
                            'price' => $arrRequest['price'],
                            'order_id' => $order_id,
                            'desc' => $desc,
                            'res_two' => $res,
                        ],
                        true));
                    return $this->getResponse('1');
                } else {
                    return $this->getInfoResponse('5000', $res['err_code_des']);
                }
            }

            if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
                return $this->getResponse('1');
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }

    /**
     *
     */
    public function resolution(Request $request)
    {

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $obj_wechat_info = new WechatInfo();
            $is_wechat_info_value = $obj_wechat_info->where('app_id', $arrRequest['app_id'])->first();
            if ($is_wechat_info_value) {
                $is_wechat_info = 1;
            } else {
                $is_wechat_info = 0;
            }

            return $this->getResponse([
                'is_wechat_info' => $is_wechat_info,
                'user_name' => empty($is_wechat_info_value->nickname) ? '无昵称' : $is_wechat_info_value->nickname,
                'ico_img' => empty($is_wechat_info_value->headimgurl) ? 0 : $is_wechat_info_value->headimgurl,
                'openid' => empty($is_wechat_info_value->openid) ? 0 : $is_wechat_info_value->openid,
                'access_token' => empty($is_wechat_info_value->access_token) ? 0 : $is_wechat_info_value->access_token,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

}
