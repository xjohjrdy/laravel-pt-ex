<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleMessage;
use App\Entitys\App\CircleNoPainNoSay;
use App\Entitys\App\CircleNotify;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * @param Request $request
     * @param CircleNotify $notify
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleNotify $notify)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }
            $app_id = $arrRequest['app_id'];

            $msg_list = $notify->getMsgList($app_id);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

        return $this->getResponse($msg_list);
    }

    /**
     * @param Request $request
     * @param CircleMessage $message
     * @param CircleNotify $notify
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getItem(Request $request, CircleMessage $message, CircleNotify $notify)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'to_app_id' => 'required',
                'page' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }
            $app_id = $arrRequest['app_id'];
            $to_app_id = $arrRequest['to_app_id'];
            $page = $arrRequest['page'];
            $notify->read($app_id, $to_app_id, 2);

            $msg_list = $message->getItemList($app_id, $to_app_id, $page);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
        return $this->getResponse($msg_list);
    }

    /**
     * @param Request $request
     * @param CircleMessage $message
     * @param CircleNotify $notify
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function sendMsg(Request $request, CircleMessage $message, CircleNotify $notify)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'msg' => 'required',
                'app_id' => 'required',
                'to_app_id' => 'required',

            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $obj_ad_user_info = new AdUserInfo();
            $ad_user_info = $obj_ad_user_info->where(['pt_id' => $arrRequest['app_id']])->value('groupid');
            if ($ad_user_info == 10){
                return $this->getResponse('发送成功');
            }
            $obj_circle_no_say = new CircleNoPainNoSay();
            $arr = $obj_circle_no_say->where('app_id', $arrRequest['app_id'])->first();
            if ($arr) {
                return $this->getInfoResponse('4005', '您已被禁言,无法发送消息！');
            }

            /* 手动取库里面的
                'username' => 'required',
                'ico' => 'required',
             */

            $red_user_info = AppUserInfo::find($arrRequest['app_id']);
            $msg = $arrRequest['msg'];
            $app_id = $arrRequest['app_id'];
            $to_app_id = $arrRequest['to_app_id'];
            $username = $red_user_info->user_name ? $red_user_info->user_name : 'ID:' . $app_id;
            $ico = $red_user_info->avatar ? $red_user_info->avatar : '0';
            if (empty($notify->upMsg($username, $ico, $app_id, $to_app_id))) {
                return $this->getInfoResponse('4004', '对方不是您的好友');
            }
            if (empty($message->addMsg($app_id, $to_app_id, $msg))) {
                return $this->getInfoResponse('4000', '发送失败');
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
        return $this->getResponse('发送成功');

    }

    public function read(Request $request, CircleNotify $notify)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }

            $id = $arrRequest['id'];
            $notify->readById($id);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
        return $this->getResponse('ok');
    }

}