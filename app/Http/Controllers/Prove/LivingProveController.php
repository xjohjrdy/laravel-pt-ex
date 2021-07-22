<?php

namespace App\Http\Controllers\Prove;

use App\Entitys\App\UserCheckAllFunction;
use App\Exceptions\ApiException;
use App\Services\Authentication\AuthenticationServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LivingProveController extends Controller
{
    /*
     * 发起认证请求
     */
    public function getVerifyToken(Request $request, AuthenticationServices $authenticationServices, UserCheckAllFunction $userCheckAllFunction)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $is_bind = $userCheckAllFunction->where('app_id', $arrRequest['app_id'])->value('is_bind');
            if (!is_null($is_bind)) {
                if ($is_bind == 1) {
                    return $this->getInfoResponse('1002', "您已提交认证无需再次提交！");
                }
                $my_order = $authenticationServices->guid();
                $res = $authenticationServices->getVerifyToken($my_order);
                $arr_res = json_decode($res, true);
                if ($arr_res['Code'] != 1) {
                    return $this->getInfoResponse('1001', $arr_res['Success']);
                }
                $arrRequest['my_order'] = $my_order;
                $arrRequest['you_order'] = $arr_res['RequestId'];
                $userCheckAllFunction->where('app_id', $arrRequest['app_id'])->update(['my_order' => $arrRequest['my_order'], 'you_order' => $arrRequest['you_order']]);
            } else {
                $my_order = $authenticationServices->guid();
                $res = $authenticationServices->getVerifyToken($my_order);
                $arr_res = json_decode($res, true);
                if ($arr_res['Code'] != 1) {
                    return $this->getInfoResponse('1001', $arr_res['Success']);
                }
                $arrRequest['my_order'] = $my_order;
                $arrRequest['you_order'] = $arr_res['RequestId'];
                $userCheckAllFunction->addProveSign($arrRequest);
            }
            return $this->getResponse($arr_res);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    public function getToken()
    {
    }

    /*
     * 获取认证状态
     */
    public function getStatus(Request $request, AuthenticationServices $authenticationServices, UserCheckAllFunction $userCheckAllFunction)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'you_order' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $my_order = $userCheckAllFunction->where('you_order', $arrRequest['you_order'])->value('my_order');
            if (empty($my_order)) {
                return $this->getInfoResponse('1001', '请求异常！');
            }
            $res = $authenticationServices->getStatus($my_order);
            $arr_res = json_decode($res, true);
            if ($arr_res['Code'] != 1) {
                return $this->getInfoResponse('1002', $arr_res['Success']);
            }
            $userCheckAllFunction->where('my_order', $my_order)->update(['is_bind' => $arr_res['Data']['StatusCode']]);
            return $this->getResponse('请求成功!');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
