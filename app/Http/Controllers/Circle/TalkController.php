<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleActivePushNoSay;
use App\Entitys\App\CircleNoPainNoSay;
use App\Entitys\App\CircleNotify;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\CircleTalk;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TalkController extends Controller
{
    /**
     * 拉出列表
     * @param Request $request
     * @param CircleTalk $circleTalk
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleTalk $circleTalk, CircleRingAdd $circleRingAdd, CircleNotify $circleNotify)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $is_have = $circleRingAdd->getByAppCircle($arrRequest['circle_id'], $arrRequest['app_id']);
            if (!$is_have) {
                return $this->getInfoResponse('5000', '您没有加入圈子，无法进入群聊');
            }
            $res = $circleTalk->getList($arrRequest['circle_id']);

            foreach ($res as $k => $re) {
                $is_have_one = $circleRingAdd->getByAppCircle($re->circle_id, $re->app_id);
                if (empty($is_have_one)) {
                    $res[$k]->no_say = 1;
                } else {
                    $res[$k]->no_say = $is_have_one->no_say;
                }
            }
            $circleNotify->read($arrRequest['app_id'], $arrRequest['circle_id'], 3);
            return $this->getResponse([
                'no_say' => $is_have->no_say,
                'status' => $is_have->status,
                'res' => $res,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取多个群聊，由于上面的资源写反了，所以这里只能凑合写个新的方法去存储了
     * @param Request $request
     * @param CircleTalk $circleTalk
     * @param CircleRingAdd $circleRingAdd
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getManyIndex(Request $request, CircleTalk $circleTalk, CircleRingAdd $circleRingAdd, CircleRing $circleRing)
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

            $all_message = [];
            $all_ring_add = $circleRingAdd->getByAppId($arrRequest['app_id']);
            foreach ($all_ring_add as $k => $ring_add) {
                $circle_talk = $circleTalk->getOneMessage($ring_add->circle_id);
                $circle_talk_count = $circleTalk->getCountList($ring_add->circle_id);
                $circle_ring = $circleRing->getById($ring_add->circle_id);
                $all_message[$k]['ico_img'] = $circle_ring->ico_img;
                $all_message[$k]['ico_title'] = $circle_ring->ico_title;
                $all_message[$k]['number_all'] = $circle_talk_count;
                $all_message[$k]['new_message'] = $circle_talk->comment_content;
                $all_message[$k]['created_at'] = $circle_talk->created_at;
            }

            return $this->getResponse($all_message);
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
     * 发送消息
     * @param Request $request
     * @param CircleTalk $circleTalk
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, CircleTalk $circleTalk, CircleRingAdd $circleRingAdd, CircleNotify $circleNotify, CircleRing $circleRing, AppUserInfo $appUserInfo, CircleActivePushNoSay $circleActivePushNoSay)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
                'comment_content' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $is_circle = $circleRing->where(['id'=>$arrRequest['circle_id'], 'app_id'=>$arrRequest['app_id']])->exists();
            $obj_ad_user_info = new AdUserInfo();
            $ad_user_info = $obj_ad_user_info->where(['pt_id' => $arrRequest['app_id']])->value('groupid');
            if ($ad_user_info == 10 && !$is_circle){
                return $this->getResponse('发送成功');
            }
            $obj_circle_no_say = new CircleNoPainNoSay();
            $arr = $obj_circle_no_say->where('app_id', $arrRequest['app_id'])->first();
            if ($arr) {
                return $this->getInfoResponse('4005', '您已被禁言,无法发送消息！');
            }
            $no_say = $circleActivePushNoSay->getNoSay();
            foreach ($no_say as $item) {
                if (strstr($arrRequest['comment_content'], $item)) {
                    return $this->getInfoResponse('3003', '您发的信息疑似含有违规内容，请修改后重新发布！');
                }
            }

            if (strlen($arrRequest['comment_content']) >= 140) {
                return $this->getInfoResponse('4001', '您发的信息过长，请修改后重新发布！！');
            }
            $circle_ring = $circleRing->getById($arrRequest['circle_id'], 1);
            if (empty($arrRequest['user_name'])) {
                $user_name = "圈子ID:" . $arrRequest['circle_id'];
            } else {
                $user_name = $circle_ring->ico_title;
            }
            if (empty($arrRequest['ico_img'])) {
                $ico_img = 0;
            } else {
                $ico_img = $circle_ring->ico_img;
            }
            $add_thing = $circleRingAdd->getByAppCircle($arrRequest['app_id'], $arrRequest['circle_id']);
            if (!empty($add_thing->no_say)) {
                return $this->getInfoResponse('4000', '您已经被禁言！');
            }

            $circleNotify->pushGroupNotify($arrRequest['app_id'], $arrRequest['circle_id'], $arrRequest['comment_content'], $user_name, $ico_img);
            $circleTalk->pushInfo($arrRequest);
            return $this->getResponse('发送成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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
