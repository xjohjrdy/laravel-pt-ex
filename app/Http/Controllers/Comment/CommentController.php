<?php

namespace App\Http\Controllers\Comment;

use App\Entitys\App\CommentList;
use App\Entitys\App\OpinionReply;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    //

    /**
     * 消息通知数量
     */
    public function msgAll(Request $request, OpinionReply $opinionReply)
    {

        //拉一下lc_opinion_reply表的未读信息总数

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $number = $opinionReply->pushAll($arrRequest['app_id']);

            return $this->getResponse($number);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 发送意见(尽量兼容小程序)
     */
    public function sendMsg(Request $request, CommentList $commentList)
    {

        //刚提交状态0

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'from' => 'required',//0:app反馈，1：小程序反馈
                'type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            if ($arrRequest['type'] == 1 || $arrRequest['type'] == 2) {
                $rules = [
                    'all' => 'required',
//                    'phone' => 'required',
                ];
                $validator = Validator::make($arrRequest, $rules);
                if ($validator->fails()) {
                    throw new ApiException('缺少参数,错误信息：' . $validator->errors(), 3002);
                }
            }

            if ($arrRequest['type'] == 3) {
                $rules = [
                    'wechat' => 'required',
//                    'all' => 'required',
//                    'phone' => 'required',
                    'img' => 'required',
                ];
                $validator = Validator::make($arrRequest, $rules);
                if ($validator->fails()) {
                    throw new ApiException('缺少参数,错误信息：' . $validator->errors(), 3002);
                }
            }

            if ($arrRequest['type'] == 4) {
                $rules = [
                    'all' => 'required',
                    'phone' => 'required',
                    'img' => 'required',
                ];
                $validator = Validator::make($arrRequest, $rules);
                if ($validator->fails()) {
                    throw new ApiException('缺少参数,错误信息：' . $validator->errors(), 3002);
                }
            }


            $commentList->addInfo([
                'app_id' => $arrRequest['app_id'],
                'all' => empty($arrRequest['all']) ? 0 : $arrRequest['all'],
                'phone' => empty($arrRequest['phone']) ? 0 : $arrRequest['phone'],
                'wechat' => empty($arrRequest['wechat']) ? '' : $arrRequest['wechat'],
                'img' => empty($arrRequest['img']) ? '' : $arrRequest['img'],
                'status' => 0,
                'type' => $arrRequest['type'],
                'from' => $arrRequest['from'],
                'ok_time' => time(),
                'start' => 1,
            ]);

            return $this->getResponse('提交成功');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 拉出反馈记录
     */
    public function msgList(Request $request, CommentList $commentList)
    {
        //拉出反馈记录之前
        //如果记录的更新时间距离现在已经超过24小时，而且对象是客服，而且状态不能为2,3，则进行关闭状态更新//更新成3，再去拉取

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $commentList->updateEnd($arrRequest['app_id']);
            $res = $commentList->getAllInfo($arrRequest['app_id']);

            return $this->getResponse($res);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 拉出反馈的具体信息
     */
    public function msgReply(Request $request, CommentList $commentList, OpinionReply $opinionReply)
    {
        //用户查看回复以后的更新行为
        //针对单个意见记录的更新对应的所有聊天

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'opinion_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $res_list = $commentList->getFirst($arrRequest['opinion_id']);

            if ($res_list->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('4000', '该问题目标不一致!');
            }

            $opinionReply->updateRead($arrRequest['opinion_id']);

            $res_msg = $opinionReply->getAllInfo($arrRequest['opinion_id']);

            return $this->getResponse($res_msg);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 发送回复
     */
    public function sendReply(Request $request, CommentList $commentList, OpinionReply $opinionReply)
    {
        //新增记录

        //更新当前反馈记录的反馈时间以及对应的发起标记
        //回复一下更新成1

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'opinion_id' => 'required',
                'content' => 'required',
                'is_mini' => 'required', //是否小程序 1:是
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            if ($arrRequest['is_mini'] == 1) {
                $name = '心选购小助手';
                $header = 'http://cdnwhwy.36qq.com/circle/images/logo.png';
            } else {
                $name = '我的小助手';
                $header = 'http://cdnwhwy.36qq.com/circle/images/logo.png';
            }


            $res_list = $commentList->getFirst($arrRequest['opinion_id']);

            if ($res_list->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('4000', '该问题目标不一致!');
            }

            if ($res_list->status == 2 || $res_list->status == 3) {
                return $this->getInfoResponse('3000', '该问题已经关闭!');
            }

            $opinionReply->addInfo([
                'app_id' => $arrRequest['app_id'],
                'opinion_id' => $arrRequest['opinion_id'],
                'header' => $header,
                'name' => $name,
                'content' => $arrRequest['content'],
                'type' => '2',
                'status' => '0',
            ]);

            $commentList->updateNow($arrRequest['opinion_id'], time(), 1);
            $commentList->updateStatus($arrRequest['opinion_id'], 1);

            return $this->getResponse('发送成功！请等待回复');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }

    /**
     * 已解决
     */
    public function end(Request $request, CommentList $commentList)
    {
        //更新反馈记录状态
        //更新成2

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'opinion_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $commentList->updateStatus($arrRequest['opinion_id'], 2);

            return $this->getResponse('感谢您的反馈！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
