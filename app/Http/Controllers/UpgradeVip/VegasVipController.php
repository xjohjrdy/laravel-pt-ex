<?php

namespace App\Http\Controllers\UpgradeVip;

use App\Entitys\App\VipAlimamaInfo;
use App\Services\UpgradeVip\VegasVipService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Validator;

class VegasVipController extends Controller
{
    /*
     * 阿里妈妈 - 状态校验
     */
    public function statusVerify(Request $request)
    {
        try {
            //仅用于测试兼容旧版
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }

            $app_id = $arrRequest['app_id'];

            $obj_vip_alimama_info = new VipAlimamaInfo();

            $info_exists = $obj_vip_alimama_info->where('app_id', $app_id)->exists();

            if ($info_exists) { //不允许用户重复绑定
                return $this->getInfoResponse(1001, '已绑定淘宝账号');
            }

            $url = 'https://oauth.m.taobao.com/authorize?response_type=code&client_id=25919216&redirect_uri=http://api.36qq.com/taobao_authorisation_vip&view=wap&state=' . $app_id;

            return $this->getResponse($url);

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }


    /*
 * 阿里妈妈 - 用户信息存储
 */
    public function authorisation(Request $request, VegasVipService $vegasVipService)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = $request->all();


            $rules = [
                'state' => 'required',
                'code' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                return 101;
            }

            $app_id = $arrRequest['state'];
            $taobao_code = $arrRequest['code'];
            $redirect_uri = $request->fullUrl();

            $obj_vip_alimama_info = new VipAlimamaInfo();

            $info_exists = $obj_vip_alimama_info->where('app_id', $app_id)->exists();

            if ($info_exists) { //不允许用户重复绑定
//                return 102;
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=不允许重复绑定');
            }

            $resq_taobao = $vegasVipService->getAccessToken($taobao_code);

            if (empty(@$resq_taobao['token_result'])) {
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=淘宝不允许生成Token');
            }

            $arr_token_result = json_decode($resq_taobao['token_result'], true);

            $resq_auth = @$vegasVipService->publisherInfoSave('QXVFL6', $arr_token_result['access_token']);

            if (empty(@$resq_auth['data'])) {
                $error_msg = empty(@$resq_auth['sub_msg']) ? '绑定渠道失败' : $resq_auth['sub_msg'];
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=' . $error_msg);
            }

            //创建私域用户
            @$params = [
                'app_id' => $app_id,
                'grant_type' => 'authorization_code',
                'code' => $taobao_code,
                'redirect_uri' => $redirect_uri,
                'access_token' => $arr_token_result['access_token'],
                'token_type' => $arr_token_result['token_type'],
                'expires_in' => $arr_token_result['expires_in'],
                'refresh_token' => $arr_token_result['refresh_token'],
                're_expires_in' => $arr_token_result['re_expires_in'],
                'r1_expires_in' => $arr_token_result['r1_expires_in'],
                'r2_expires_in' => $arr_token_result['r2_expires_in'],
                'w1_expires_in' => $arr_token_result['w1_expires_in'],
                'w2_expires_in' => $arr_token_result['w2_expires_in'],
                'taobao_user_nick' => $arr_token_result['taobao_user_nick'],
                'taobao_user_id' => $arr_token_result['taobao_user_id'],
                'relation_id' => $resq_auth['data']['relation_id'],
                'account_name' => $resq_auth['data']['account_name'],
                'adzone_id' => '109771100415'
            ];

            $obj_vip_alimama_info->create($params);

//            return 200;
            return redirect('http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/kaifazhong/app-h5/pages/authorize.html', 301);
        } catch (\Throwable $e) {
//            return 500;
            return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=500:' . $e->getMessage());
        }
    }

}
