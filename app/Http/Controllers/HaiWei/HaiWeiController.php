<?php

namespace App\Http\Controllers\HaiWei;

use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;
use App\Services\HaiWei\HaiWeiServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HaiWeiController extends Controller
{
    /*
     * 海威 APP首页
     */
    public function index(Request $request, HaiWeiServices $haiWeiServices, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            $url = $haiWeiServices->index($app_id);
            return $this->getResponse($url);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 海威 我的订单
     */
    public function myOrder(Request $request, HaiWeiServices $haiWeiServices, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

//            $url = $haiWeiServices->myOrder($app_id);
            $url = "http://api.36qq.com/coupon/#/Order?app_id=" . $app_id;
            return $this->getResponse($url);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
