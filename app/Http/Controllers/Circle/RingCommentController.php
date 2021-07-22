<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleActive;
use App\Entitys\App\CircleActivePushNoSay;
use App\Entitys\App\CircleComment;
use App\Entitys\App\CircleCommonNotify;
use App\Entitys\App\CircleNoPainNoSay;
use App\Entitys\App\CircleRing;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RingCommentController extends Controller
{
    /**
     * 获取评论列表
     * @param Request $request
     * @param CircleComment $circleComment
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleComment $circleComment)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'active_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $res = $circleComment->getListComment($arrRequest['active_id']);

            return $this->getResponse($res);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 发布评论
     * @param Request $request
     * @param CircleComment $circleComment
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function create(Request $request, CircleActivePushNoSay $circleActivePushNoSay)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'active_id' => 'required',
                'app_id' => 'required',
                'comment_content' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $obj_circle_no_say = new CircleNoPainNoSay();
            $arr = $obj_circle_no_say->where('app_id', $arrRequest['app_id'])->first();
            if ($arr) {
                return $this->getInfoResponse('4005', '您已被禁言,无法发布评论！');
            }

            if (empty($arrRequest['user_name'])) {
                $arrRequest['user_name'] = '无昵称';
            }
            $no_say = $circleActivePushNoSay->getNoSay();
            foreach ($no_say as $item) {
                if (strstr($arrRequest['comment_content'], $item)) {
                    return $this->getInfoResponse('3003', '您发的信息疑似含有违规内容，请修改后重新发布！');
                }
            }

            $circleComment = new CircleComment();
            $circleActive = new CircleActive();
            $circleActive->addNumber($arrRequest['active_id'], 'have_number', 1);
            $circleComment->pushComment($arrRequest);

            $info = $circleActive->getById($arrRequest['active_id']);
            $re_obj_user = AppUserInfo::find($arrRequest['app_id']);

            $obj_notify = new CircleCommonNotify();
            $n_data = [];
            $n_data['app_id'] = $info->app_id;
            $n_data['ico'] = $re_obj_user->avatar;
            $n_data['username'] = $re_obj_user->user_name;
            $n_data['notify'] = $arrRequest['comment_content'];
            $n_data['to_id'] = $arrRequest['active_id'];
            $n_data['word_content'] = $info->circle_content;
            $arr_img = explode(",", $info->circle_content_img);
            if (empty($arr_img)) {
                $n_data['url_content'] = '';
            } else {
                $n_data['url_content'] = @$arr_img[0];
            }
            $n_data['type'] = 2;
            $obj_notify->addNotify($n_data);

            return $this->getResponse('发布成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
