<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleMaid;
use App\Entitys\App\CircleRedTime;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class MaidController extends Controller
{
    /**
     * 我的佣金
     * @param Request $request
     * @param CircleMaid $circleMaid
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleMaid $circleMaid, AppUserInfo $appUserInfo, CircleRedTime $circleRedTime)
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

            $user = $appUserInfo->getUserById($arrRequest['app_id']);

            if (Cache::has('putao:circle:' . $arrRequest['app_id']) && Cache::has('putao:circle.all:' . $arrRequest['app_id'])) {
                $all = Cache::get('putao:circle.all:' . $arrRequest['app_id']);
                $red_get = Cache::get('putao:circle.zero:' . $arrRequest['app_id']);
                $in_circle_get = Cache::get('putao:circle.two:' . $arrRequest['app_id']);
                $red_maid = Cache::get('putao:circle.three:' . $arrRequest['app_id']);
                $introduce_one_buy = Cache::get('putao:circle.four:' . $arrRequest['app_id']);
                $forward_user_get_circle = Cache::get('putao:circle.five:' . $arrRequest['app_id']);
                $forward_user_in_circle = Cache::get('putao:circle.six:' . $arrRequest['app_id']);
                $bidding_get = Cache::get('putao:circle.one:' . $arrRequest['app_id']);
            } else {
                $red_get = $circleRedTime->getAllSum($arrRequest['app_id'], 1);
                $in_circle_get = $circleMaid->getByUserTypeSum($arrRequest['app_id'], 2);
                $red_maid = $circleMaid->getByUserTypeSum($arrRequest['app_id'], 3);
                $introduce_one_buy = $circleMaid->getByUserTypeSum($arrRequest['app_id'], 4);
                $bidding_get = $circleMaid->getByUserTypeSum($arrRequest['app_id'], 1);
                $forward_user_get_circle = $circleMaid->getByUserTypeSum($arrRequest['app_id'], 5);
                $forward_user_in_circle = $circleMaid->getByUserTypeSum($arrRequest['app_id'], 6);
                $all = $red_get + $in_circle_get + $red_maid + $introduce_one_buy + $bidding_get + $forward_user_get_circle + $forward_user_in_circle;

                Cache::put('putao:circle.all:' . $arrRequest['app_id'], $all, 10);
                Cache::put('putao:circle.zero:' . $arrRequest['app_id'], $red_get, 10);
                Cache::put('putao:circle.two:' . $arrRequest['app_id'], $in_circle_get, 10);
                Cache::put('putao:circle.three:' . $arrRequest['app_id'], $red_maid, 10);
                Cache::put('putao:circle.four:' . $arrRequest['app_id'], $introduce_one_buy, 10);
                Cache::put('putao:circle.five:' . $arrRequest['app_id'], $forward_user_get_circle, 10);
                Cache::put('putao:circle.six:' . $arrRequest['app_id'], $forward_user_in_circle, 10);
                Cache::put('putao:circle.one:' . $arrRequest['app_id'], $bidding_get, 10);
                Cache::put('putao:circle:' . $arrRequest['app_id'], $arrRequest['app_id'], 10);
            }


            return $this->getResponse([
                'user' => [
                    'ico' => $user->avatar,
                    'name' => $user->user_name,
                ],
                'all' => $all,
                'zero' => $red_get,
                'two' => $in_circle_get,
                'three' => $red_maid,
                'four' => $introduce_one_buy,
                'five' => $forward_user_get_circle,
                'six' => $forward_user_in_circle,
                'one' => $bidding_get,
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * 某个种类的佣金列表
     * @param $id
     * @param Request $request
     * @param CircleMaid $circleMaid
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, CircleMaid $circleMaid, CircleRedTime $circleRedTime, AppUserInfo $appUserInfo)
    {
        return $this->getInfoResponse('4004', '内测期间，感谢您的耐心等待！');
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

            if (Cache::has('putao:circle:show:' . $arrRequest['app_id'] . ':type:' . $arrRequest['type']) && Cache::has('putao:circle:show.type.' . $arrRequest['type'] . '.sum.' . $arrRequest['app_id'])) {
                $list = Cache::get('putao:circle:show.type.' . $arrRequest['type'] . '.list.' . $arrRequest['app_id']);
                $sum = Cache::get('putao:circle:show.type.' . $arrRequest['type'] . '.sum.' . $arrRequest['app_id']);
            } else {
                if ($arrRequest['type']) {
                    $sum = $circleMaid->getByUserTypeSum($arrRequest['app_id'], $arrRequest['type']);
                    $list = $circleMaid->getByUserType($arrRequest['app_id'], $arrRequest['type']);
                } else {
                    $sum = $circleRedTime->getAllSum($arrRequest['app_id'], 1);
                    $list = $circleRedTime->getAllByAppId($arrRequest['app_id'], 1);
                }
                Cache::put('putao:circle:show:' . $arrRequest['app_id'] . ':type:' . $arrRequest['type'], $arrRequest['type'], 10);
                Cache::put('putao:circle:show.type.' . $arrRequest['type'] . '.list.' . $arrRequest['app_id'], $list, 10);
                Cache::put('putao:circle:show.type.' . $arrRequest['type'] . '.sum.' . $arrRequest['app_id'], $sum, 10);
            }

            $arr = [];

            if ($arrRequest['type'] == 0) {
                foreach ($list as $k => $value) {
                    $user_app = $appUserInfo->getUserById($value->from_app_id);
                    $arr[$k]['info'] = "您抢到了" . ($user_app->user_name ? $user_app->user_name : "ID:" . $value->from_app_id) . "的红包，共抢到" . $value->have . "葡萄币。";
                    $arr[$k]['created_at'] = $value->created_at;
                    $arr[$k]['ico_img'] = $user_app->avatar;
                }
            }
            if ($arrRequest['type'] == 1) {
                foreach ($list as $k => $value) {
                    $arr[$k]['info'] = ($value->from_user_name ? $value->from_user_name : "用户:" . substr_replace($value->from_user_phone, '***', 1, 9)) . "花费了" . ($value->order_money / 10) . "元抢购了您的" . $value->from_circle_name . "圈子，您获得" . $value->money . "葡萄币。";
                    $arr[$k]['created_at'] = $value->created_at;
                    $arr[$k]['ico_img'] = $value->from_circle_img;
                }
            }
            if ($arrRequest['type'] == 5) {
                foreach ($list as $k => $value) {
                    $arr[$k]['info'] = "您的直属会员" . ($value->from_user_name ? $value->from_user_name : substr_replace($value->from_user_phone, '***', 1, 9)) . "花费了" . ($value->order_money / 10) . "元竞价抢购了'" . $value->from_circle_name . "'圈子，您获得" . $value->money . "葡萄币。";
                    $arr[$k]['created_at'] = $value->created_at;
                    $arr[$k]['ico_img'] = $value->from_circle_img;
                }
            }
            if ($arrRequest['type'] == 2) {
                foreach ($list as $k => $value) {
                    $arr[$k]['info'] = "直属会员：" . ($value->from_user_name ? $value->from_user_name : substr_replace($value->from_user_phone, '***', 1, 9)) . "付费" . ($value->order_money / 10) . "元进入了您的" . $value->from_circle_name . "圈子，作为圈主您获得" . $value->money . "葡萄币。";
                    $arr[$k]['created_at'] = $value->created_at;
                    $arr[$k]['ico_img'] = $value->from_circle_img;
                }
            }
            if ($arrRequest['type'] == 6) {
                foreach ($list as $k => $value) {
                    $arr[$k]['info'] = "直属会员" . ($value->from_user_name ? $value->from_user_name : substr_replace($value->from_user_phone, '***', 1, 9)) . "付费" . ($value->order_money / 10) . "元进入了'" . $value->from_circle_name . "'圈子，您获得" . $value->money . "葡萄币。";
                    $arr[$k]['created_at'] = $value->created_at;
                    $arr[$k]['ico_img'] = $value->from_circle_img;
                }
            }

            if ($arrRequest['type'] == 3) {
                foreach ($list as $k => $value) {
                    $arr[$k]['info'] = "您的圈子“" . $value->from_circle_name . "”发了红包，您获得" . $value->money . "葡萄币。";

                    $arr[$k]['created_at'] = $value->created_at;
                    $arr[$k]['ico_img'] = $value->from_circle_img;
                }
            }

            if ($arrRequest['type'] == 4) {
                foreach ($list as $k => $value) {
                    $phone_user = $appUserInfo->getUserByPhone($value->from_user_phone);
                    if ($phone_user->parent_id == $arrRequest['app_id']) {
                        $info = "直属会员";
                    } else {
                        $info = "团队会员";
                    }
                    $arr[$k]['info'] = $info . ($value->from_user_name ? $value->from_user_name : substr_replace($value->from_user_phone, '***', 1, 9)) . "购买了圈子，您获得" . $value->money . "葡萄币。";
                    $arr[$k]['created_at'] = $value->created_at;
                    $arr[$k]['ico_img'] = $value->from_circle_img;
                }
            }

            return $this->getResponse([
                'sum' => $sum,
                'list' => $arr,
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
