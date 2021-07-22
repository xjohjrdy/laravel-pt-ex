<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LogController extends Controller
{
    //

    public function getLog(Request $request, TaobaoChangeUserLog $taobaoChangeUserLog)
    {
        try {
//            仅用于测试兼容旧版
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $change_user_log = $taobaoChangeUserLog->getPageAll($arrRequest['app_id']);

            if (empty($change_user_log)) {
                return $this->getInfoResponse('4004', '没有记录');
            }

            $change_user_log = $change_user_log->toArray();

            foreach ($change_user_log['data'] as $k => $value) {
                if($value['from_type'] == '99999'){
                    $change_user_log['data'][$k]['dispaly_info'] = $value['from_info'];
                } else {
                    $change_user_log['data'][$k]['dispaly_info'] = $taobaoChangeUserLog->change[$value['from_type']];
                }
                if ($value['before_next_money'] > 0) {
                    $change_user_log['data'][$k]['before_next_money'] = '+' . $value['before_next_money'];
                }
            }

            return $this->getResponse($change_user_log);

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     *
     */
    public function changeUserName(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            //仅用于测试兼容旧版
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'real_name' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_user_info = $appUserInfo->getUserInfo($arrRequest['app_id']);

            if (empty($app_user_info)) {
                return $this->getInfoResponse('4004', '用户不存在');
            }

            if (!empty($app_user_info->real_name)) {
                return $this->getInfoResponse('4004', '已经存在真实姓名，不需要修改');
            }

            $appUserInfo->where(['id' => $arrRequest['app_id']])->update([
                'real_name' => $arrRequest['real_name']
            ]);

            return $this->getResponse('修改成功');

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
