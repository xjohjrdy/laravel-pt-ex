<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleActive;
use App\Entitys\App\CircleActiveNo;
use App\Entitys\App\CircleActiveNoSay;
use App\Entitys\App\CircleActivePushNoSay;
use App\Entitys\App\CircleCommonNotify;
use App\Entitys\App\CircleFriend;
use App\Entitys\App\CircleHighNumber;
use App\Entitys\App\CircleIndexUpNo;
use App\Entitys\App\CircleNoPainNoSay;
use App\Entitys\App\CircleNoSay;
use App\Entitys\App\CircleOrder;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\UserHigh;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RingActiveController extends Controller
{
    /**
     * 获取列表的信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleActive $circleActive, CircleRingAdd $circleRingAdd, CircleRing $circleRing, AppUserInfo $appUserInfo, CircleHighNumber $circleHighNumber)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle = $circleRing->getById($arrRequest['circle_id']);

            if (!$circle) {
                return $this->getInfoResponse('4004', '您所要查看的圈子不存在！');
            }
            $is_have = $circleRingAdd->getByAppCircle($arrRequest['circle_id'], $arrRequest['app_id']);
            if (!$is_have) {
                $status = 99;
            } else {
                $status = $is_have->status;
            }
            $user_real_name = $appUserInfo->getUserById($circle->app_id);
            $real_name = $circle->app_id;
            if (!empty($user_real_name->user_name)) {
                $real_name = $user_real_name->user_name;
            }
            $active = $circleActive->getList($arrRequest['circle_id']);

            /**
             * 获取免费领取次数
             */
            $tmp_count_number = 0;
            $groupid = AdUserInfo::where(['pt_id' => $arrRequest['app_id']])->value('groupid');

            $high_number = $circleHighNumber->getCanGetNumber($arrRequest['app_id']);
            $tmp_count_number += $high_number;
            if ($groupid == 24) {
                $tmp_count_number += 1;
            }
            $ring_is_count = CircleOrder::where(['app_id' => $arrRequest['app_id'], 'money' => 0, 'status' => 1])->count();
            $have_number = $tmp_count_number - $ring_is_count;

            return $this->getResponse([
                'circle' => $circle,
                'user_real_name' => $real_name,
                'is_have' => $status,
                'circle_active' => $active,
                'red_number' => '123',
                'have_number' => $have_number,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * 发布动态
     * @param Request $request
     * @param CircleActive $circleActive
     * @param CircleRing $circleRing
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, CircleActive $circleActive, CircleRing $circleRing, CircleActivePushNoSay $circleActivePushNoSay, CircleActiveNo $circleActiveNo, AppUserInfo $appUserInfo, CircleRingAdd $circleRingAdd)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'circle_content' => 'required',
                'app_id' => 'required',
                'circle_content_img' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $obj_circle_no_say = new CircleNoPainNoSay();
            $arr = $obj_circle_no_say->where('app_id', $arrRequest['app_id'])->first();
            if ($arr) {
                return $this->getInfoResponse('4005', '您已被禁言,无法发布动态！');
            }
            $no_say = $circleActivePushNoSay->getNoSay();
            foreach ($no_say as $item) {
                if (strstr($arrRequest['circle_content'], $item)) {
                    return $this->getInfoResponse('3003', '您发的动态疑似含有违规内容，请修改后重新发布！');
                }
            }

            $is_need = $circleRingAdd->getByAppCircle($arrRequest['circle_id'], $arrRequest['app_id']);
            if (!$is_need) {
                return $this->getInfoResponse('5000', '您没有权限在这里发布动态');
            }
            if ($is_need->no_say) {
                return $this->getInfoResponse('4000', '您已经被禁言！');
            }
            $is_active_no = $circleActiveNo->getByAppId($arrRequest['app_id']);
            if (!empty($is_active_no)) {
                return $this->getInfoResponse('4000', '您的动态已经被禁言！');
            }
            $circle_content_img = 0;
            $user = $appUserInfo->getUserById($arrRequest['app_id']);
            $circle = $circleRing->getById($arrRequest['circle_id'], 1);
            if ($circle->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('4000', '非圈子圈主，无法在圈子发布动态！');
            }

            $circle_id = $circle->id;
            $circle_name = $circle->ico_title;
            $circle_content = $arrRequest['circle_content'];
            $app_id = $arrRequest['app_id'];
            $user_name = "ID:" . $arrRequest['app_id'];
            if (!empty($user->user_name)) {
                $user_name = $user->user_name;
            }
            $user_ico_img = $user->avatar ? $user->avatar : 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/default.png';
            $circleActive->pushActive($circle_id, $circle_name, $circle_content, $app_id, $user_name, $user_ico_img, $arrRequest['circle_content_img']);
            $circleRing->addNumber($circle_id, 'number_zone', 1);
            return $this->getResponse('发布成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取单个动态的记录信息
     * @param Request $request
     * @param CircleActive $circleActive
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getOne(Request $request, CircleActive $circleActive)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'active_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $active = $circleActive->getOne($arrRequest['active_id']);

            return $this->getResponse($active);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取单个用户动态
     * @param $id
     * @param CircleActive $circleActive
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, CircleActive $circleActive, CircleRingAdd $circleRingAdd, CircleFriend $circleFriend)
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
            $circle_add = $circleRingAdd->getByAppId($id);
            $is_have = $circleFriend->getUser($arrRequest['app_id'], $id);
            if ($is_have) {
                $is_have = 1;
            } else {
                $is_have = 0;
            }
            if (empty($arrRequest['circle_id'])) {
                $res = $circleActive->getByUser($id);
                return $this->getResponse([
                    'number_list' => $res->count(),
                    'number_circle' => $circle_add->count(),
                    'is_have' => $is_have,
                    'list' => $res
                ]);
            }

            $res = $circleActive->getByUserCircle($id, $arrRequest['circle_id']);
            return $this->getResponse([
                'number_list' => $res->count(),
                'number_circle' => $circle_add->count(),
                'is_have' => $is_have,
                'list' => $res
            ]);
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
     * 动态顶到首页
     * @param Request $request
     * @param CircleActive $circleActive
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function indexUp(Request $request, CircleActive $circleActive, CircleRing $circleRing, CircleActiveNoSay $circleActiveNoSay, CircleIndexUpNo $circleIndexUpNo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'active_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_ring = $circleRing->getById($arrRequest['circle_id']);

            if ($circle_ring->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('5000', '您没有权限发送首页动态');
            }

            $index_up_no = $circleIndexUpNo->getCircleNo($arrRequest['circle_id']);
            $active = $circleActive->getById($arrRequest['active_id']);

            if (!empty($active)) {
                $no_say = $circleActiveNoSay->getNoSay();
                foreach ($no_say as $item) {
                    if (strstr($active->circle_content, $item)) {
                        return $this->getInfoResponse('3003', '该内容疑似有违规内容，不允许推送至首页哦！');
                    }
                }
            }

            if (!empty($index_up_no)) {
                if ($index_up_no->no_time > time()) {
                    $remain_time = $index_up_no->no_time - time();
                    return $this->getInfoResponse('5000', '您因推送违规内容至首页，暂停推送功能，剩余' . round($remain_time / 86400, 2) . '天。');
                }
            }
            $res_count = $circleActive->getIndexUpCount($arrRequest['circle_id']);
            if ($res_count >= 5) {
                return $this->getInfoResponse('3000', '推送失败，今日剩余：0次');
            }

            $circleActive->indexUp($arrRequest['active_id']);

            return $this->getResponse('推送成功，今日剩余：' . (5 - $res_count) . '次！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 点赞
     * @param Request $request
     * @param $id
     * @param CircleActive $circleActive
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update($id, CircleActive $circleActive, Request $request)
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

            $app_id = $arrRequest['app_id'];

            $circleActive->like($id, 1);
            $info = $circleActive->getById($id);

            $re_obj_user = AppUserInfo::find($app_id);
            $obj_notify = new CircleCommonNotify();
            $n_data = [];
            $n_data['app_id'] = $info->app_id;
            $n_data['ico'] = $re_obj_user->avatar;
            $n_data['username'] = $re_obj_user->user_name;
            $n_data['notify'] = "💗";
            $n_data['to_id'] = $id;
            $n_data['word_content'] = "收到一条点赞";
            $arr_img = explode(",", $info->circle_content_img);
            if (empty($arr_img)) {
                $n_data['url_content'] = '';
            } else {
                $n_data['url_content'] = @$arr_img[0];
            }
            $n_data['type'] = 1;
            $obj_notify->addNotify($n_data);

            return $this->getResponse('点赞成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 删除动态
     * @param $id
     * @param Request $request
     * @param CircleActive $circleActive
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function destroy($id, Request $request, CircleActive $circleActive, CircleRing $circleRing)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'is_all' => 'required',
                'circle_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $circle_ring = $circleRing->getById($arrRequest['circle_id']);
            if ($circle_ring->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('5005', '您当前没有权限删除该用户动态！');
            }
            if ($arrRequest['is_all'] == 0) {
                if (empty($arrRequest['id'])) {
                    return $this->getInfoResponse('5000', '缺少必要信息删除动态！');
                }
                $circleActive->deleteActive($arrRequest['id']);
            }
            if ($arrRequest['is_all'] == 1) {
                $circleActive->deleteAllActive($arrRequest['circle_id'], $id);
            }

            return $this->getResponse('删除成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
