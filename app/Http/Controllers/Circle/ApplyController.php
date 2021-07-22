<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleApply;
use App\Entitys\App\CircleFriend;
use App\Entitys\App\CircleMessage;
use App\Entitys\App\CircleNotify;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ApplyController extends Controller
{
    /**
     * 申请列表
     * @param Request $request
     * @param CircleApply $circleApply
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleApply $circleApply)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }
            $apply = $circleApply->getALLApply($arrRequest['app_id']);
            return $this->getResponse($apply);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 发起申请
     * @param Request $request
     * @param CircleApply $circleApply
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function create(Request $request, CircleApply $circleApply, AppUserInfo $appUserInfo, CircleNotify $circleNotify, CircleFriend $circleFriend)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'from_app_id' => 'required',
                'to_app_id' => 'required',
                'content' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }
            if ($arrRequest['from_app_id'] == $arrRequest['to_app_id']) {
                return $this->getInfoResponse('5000', '请不要自己添加自己为好友！');
            }
            $friend = $circleFriend->getUser($arrRequest['from_app_id'], $arrRequest['to_app_id']);

            if (!empty($friend)) {
                return $this->getInfoResponse('4001', '您已经和他是好友了！');
            }

            $user = $appUserInfo->getUserById($arrRequest['from_app_id']);
            $res = $circleApply->toApply($arrRequest['to_app_id'], $arrRequest['from_app_id'], $user->real_name, $user->avatar, $arrRequest['content']);
            $circleNotify->putMsg($arrRequest['to_app_id'], $arrRequest['from_app_id'], $user->real_name, $user->avatar, true);

            return $this->getResponse($res);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 申请通过`拒绝/全部通过
     * @param Request $request
     * @param CircleApply $circleApply
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, CircleApply $circleApply, CircleFriend $circleFriend, AppUserInfo $appUserInfo, CircleNotify $circleNotify)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $all_count = $circleFriend->getAllCountFriend($arrRequest['app_id']);

            if ($all_count > 1000) {
                return $this->getInfoResponse('3000', '加好友上限1000人!');
            }

            if ($arrRequest['type'] == 2) {
                $res = $circleApply->allPass($arrRequest['app_id']);
                if ($res <> 0) {
                    $all_apply = $circleApply->getALLApply($arrRequest['app_id']);
                    foreach ($all_apply as $item) {
                        $user_from = $appUserInfo->getUserById($item->from_app_id);
                        $circleFriend->addUserFriend($item->to_app_id, $item->from_app_id, $user_from->avatar, $user_from->real_name);
                        $user_to = $appUserInfo->getUserById($item->to_app_id);
                        $circleFriend->addUserFriend($item->from_app_id, $item->to_app_id, $user_to->avatar, $user_to->real_name);
                        $circleNotify->putMsg($item->from_app_id, $arrRequest['app_id'], $user_from->real_name, $user_from->avatar, false);
                    }
                }
            }

            if ($arrRequest['type'] == 1) {
                $apply = $circleApply->getOneApply($arrRequest['apply_id']);
                if (empty($apply)) {
                    return $this->getInfoResponse('4000', '此申请不存在！');
                }
                if ($apply->to_app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('4004', '您不是被申请的用户，无权利');
                }
                if (empty($arrRequest['status'])) {
                    return $this->getInfoResponse('4004', '当前没有status状态！');
                }
                $res = $circleApply->changeApplyStatus($arrRequest['apply_id'], $arrRequest['status']);
                if ($arrRequest['status'] == 2 && $res <> 0) {
                    $user_from = $appUserInfo->getUserById($apply->from_app_id);
                    $circleFriend->addUserFriend($apply->to_app_id, $apply->from_app_id, $user_from->avatar, $user_from->real_name);
                    $user_to = $appUserInfo->getUserById($apply->to_app_id);
                    $circleFriend->addUserFriend($apply->from_app_id, $apply->to_app_id, $user_to->avatar, $user_to->real_name);
                    $circleNotify->putMsg($apply->from_app_id, $arrRequest['app_id'], $user_from->real_name, $user_from->avatar, false);
                }
            }

            return $this->getResponse('操作成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取单个申请
     * @param $id
     * @param Request $request
     * @param CircleApply $circleApply
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'from_app_id' => 'required',
                'to_app_id' => 'required',
            ];
            $circleApply = new CircleApply();
            $res = $circleApply->getOneApplyByFromTo($arrRequest['from_app_id'], $arrRequest['to_app_id']);
            $obj_notify = new CircleNotify();
            $obj_notify->read($arrRequest['to_app_id'], $arrRequest['from_app_id'], 1);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
