<?php

namespace App\Http\Controllers\Kfc;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;
use App\Services\Kfc\KfcServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class KfcController extends Controller
{

    /*
     * 得到肯德基没有手机号码登陆url
     */
    public function getLoginV2(Request $request, KfcServices $kfcServices, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
                'name' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $name = $arrRequest['name'];
            /***********************************/
//            $groupid = AdUserInfo::where(['pt_id' => $app_id])->value('groupid');
//            if ($groupid == 10) {
//                return $this->getResponse('https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/staticHtml/pages/vip_kfc.html');//正常返回数据
//            }

            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            $url = $kfcServices->loginV2($app_id, $name);
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
     * 得到肯德基没有手机号码登陆url
     */
    public function getLoginV3(Request $request, KfcServices $kfcServices, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
//                'name' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $name = empty($arrRequest['name']) ? $app_id : $arrRequest['name'];
            /***********************************/
//            $groupid = AdUserInfo::where(['pt_id' => $app_id])->value('groupid');
//            if ($groupid == 10) {
//                return $this->getResponse('https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/staticHtml/pages/vip_kfc.html');//正常返回数据
//            }

            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            $phone = @$obj_user_info->phone;
            $url = $kfcServices->loginV3($app_id, $name, $phone);
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
     * 肯德基订单事件通知(废弃)
     */
    function kfcOrderCallBack(Request $request)
    {
        //接受回调数据
        $str_post_data = $request->getContent();
//        $post_data = json_decode($str_post_data, true);
        $this->log($str_post_data);
        try {
            return 'success';
        } catch (\Throwable $e) {
            $this->log($e->getMessage());
            return "success";
        }
    }

    /*
     * 记录日志
     */
    private function log($msg)
    {
        Storage::disk('local')->append('callback_document/kfc/' . date('Ymd') . '.txt', date('Y-m-d H:i:s') . '  ' . var_export($msg, true));
    }
}
