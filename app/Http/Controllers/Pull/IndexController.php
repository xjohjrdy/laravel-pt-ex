<?php

namespace App\Http\Controllers\Pull;

use App\Entitys\App\PutNewBackgroundAll;
use App\Entitys\App\PutNewFaker;
use App\Entitys\App\PutNewGetMoney;
use App\Entitys\App\PutNewRankList;
use App\Entitys\App\PutNewReward;
use App\Entitys\App\PutNewRewardReal;
use App\Entitys\App\PutNewRewardUser;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\Common\UserMoney;
use App\Services\Qmshida\OtherUserMoneyService;
use App\Services\User\Invite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    //
    /**
     * 首页展示接口
     */
    public function getIndex(Request $request, PutNewFaker $putNewFaker, PutNewRankList $putNewRankList, PutNewBackgroundAll $putNewBackgroundAll, PutNewReward $putNewReward)
    {

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //广播列表
            $put_new_faker = $putNewFaker->get(['phone', 'user_name']);
            if (empty($put_new_faker)) {
                return $this->getInfoResponse('4004', '广播列表未配置！');
            }
            $back_ground = $putNewBackgroundAll->where(['id' => 1])->first();
            if (empty($back_ground)) {
                return $this->getInfoResponse('4004', '后台参数未配置！');
            }

            //邀请人数
            $my_info = $putNewRankList->where(['app_id' => $arrRequest['app_id']])->first();
            //奖品列表
            $all_prize = $putNewReward->get(['img', 'title', 'money', 'for_one']);
            if (empty($all_prize)) {
                return $this->getInfoResponse('4004', '奖品未配置！');
            }
            //排行榜
            $put_new_rank_list = $putNewRankList->orderByDesc('success_add')->limit(50)->get(['avatar', 'show_info', 'success_add']);

            foreach ($put_new_rank_list as &$item) {
                $item->show_info = preg_replace(array('/(?<=^\d{3})\d+(?=\d{4}$)/', '/(?<=^[\x{4e00}-\x{9fa5}])[\x{4e00}-\x{9fa5}]$/u', '/(?<=^[\x{4e00}-\x{9fa5}A-Za-z0-9_]).+(?=[\x{4e00}-\x{9fa5}A-Za-z0-9_]$)/u'), '***', $item->show_info);
            }

            return $this->getResponse([
                'faker_list' => $put_new_faker,
                'money_carve_up' => [
                    'all_info' => empty($back_ground->user_all) ? 10 : $back_ground->user_all,
                    'my_info' => empty($my_info->success_add) ? 0 : $my_info->success_add,
                    'all_money' => empty($back_ground->money) ? 88888 : $back_ground->money,
                ],
                'prize' => $all_prize,
                'put_rank_list' => $put_new_rank_list,
                'show_add' => $back_ground->show_add,
            ]);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }


    }

    /**
     * 首页弹窗校验接口
     */
    public function getJump(Request $request, PutNewRankList $putNewRankList, PutNewGetMoney $putNewGetMoney, PutNewBackgroundAll $putNewBackgroundAll)
    {

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $carve_up = $putNewGetMoney->where(['app_id' => $arrRequest['app_id']])->first(['money', 'is_add', 'app_id']);
//            $reward_user = $putNewRewardUser->where(['app_id' => $arrRequest['app_id']])->where('for_one', '<>', '0')->first(['app_id', 'img', 'title', 'money', 'for_one']);
            $back_ground = $putNewBackgroundAll->where(['id' => 1])->first();


            if (time() > 1596211200) {
                $all_user = $putNewRankList->where(['app_id' => $arrRequest['app_id']])->value('success_add');
                $count_user = $putNewRankList->where('success_add', '>', $all_user)->count();
                $my = $count_user + 1;
            } else {
                $my = null;
            }

            return $this->getResponse([
                'carve_up' => $carve_up,//瓜分的钱
                'reward_user' => [
                    'for_one' => $my
                ],//根据for_one的类型不同的弹窗

                'first_reward' => $back_ground->first_reward,//第一名图片
                'two_reward' => $back_ground->two_reward,//第2名图片
                'three_reward' => $back_ground->three_reward,//第3名图片
                'other_reward' => $back_ground->other_reward,//第4-50名图片
            ]);

        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /**
     * 瓜分点击
     */
    public function carveUp(Request $request, PutNewGetMoney $putNewGetMoney, PutNewRewardUser $putNewRewardUser)
    {

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            //1.查询lc_put_new_get_money
            $new_put_money = $putNewGetMoney->where(['app_id' => $arrRequest['app_id']])->first();

            //不符合条件pass
            if (!empty($new_put_money->is_add)) {
                return $this->getInfoResponse(4001, "已经瓜分！");
            }

            if (empty($new_put_money)) {
                return $this->getInfoResponse(4001, "还未到瓜分时间哦！");
            }

            //符合的话

            //更新lc_put_new_get_money状态
            $putNewGetMoney->where(['app_id' => $arrRequest['app_id']])->update([
                'is_add' => 1,
            ]);

            //插入lc_put_new_reward_user
            $putNewRewardUser->create([
                'app_id' => $arrRequest['app_id'],
                'img' => '', //图片待定，看是否需要动态，不需要的话固定掉
                'title' => '瓜分现金奖励' . $new_put_money->money . '元',
                'money' => $new_put_money->money,
                'for_one' => '0',
                'is_exchange' => '1',
            ]);

            //其次增加钱变化记录

            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($arrRequest['app_id'], $new_put_money->money, '474');
            //加钱


            return $this->getResponse($new_put_money->money);

        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /**
     * 我的奖品
     */
    public function getReward(Request $request, PutNewRewardUser $putNewRewardUser)
    {
        //拉出这个表数据lc_put_new_reward_user

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $my_reward = $putNewRewardUser->where(['app_id' => $arrRequest['app_id']])->get(['img', 'reward_id', 'title', 'money', 'for_one', 'is_exchange', 'created_at']);

            return $this->getResponse($my_reward);

        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 奖品详情
     */
    public function rewardDetail(Request $request, PutNewRewardReal $putNewRewardReal)
    {
        //拉出这个表数据lc_put_new_reward_real

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'reward_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $reward_real = $putNewRewardReal->where(['app_id' => $arrRequest['app_id']])->first();
            if(empty($reward_real)){
                $model = new PutNewRewardUser();
                $reward_real = $model->where(['app_id' => $arrRequest['app_id']])->first()->toArray();
                $reward_real['reward_id'] = $reward_real['for_one'];
                $reward_real['address'] = '';
                $reward_real['order_id'] = '';
                $reward_real['phone'] = '';
                $reward_real['real_name'] = '';
            }
            return $this->getResponse($reward_real);

        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 立即兑奖
     */
    public function pushReward(Request $request, PutNewRewardReal $putNewRewardReal, PutNewRewardUser $putNewRewardUser, PutNewReward $putNewReward)
    {
        //插入lc_put_new_reward_real
        //更新lc_put_new_reward_user

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'reward_id' => 'required',
                'img' => 'required',
                'title' => 'required',
                'address' => 'required',
                'real_name' => 'required',
                'phone' => 'required',
                'money' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $res = $putNewRewardUser->where(['app_id' => $arrRequest['app_id']])->value('for_one');
            $reward_id = $putNewReward->where(['for_one' => $res])->value('id');

            if ($reward_id <> $arrRequest['reward_id']) {
                return $this->getInfoResponse(4004, '该奖品非该用户名次奖品！');
            }


            $res_exchange = $putNewRewardUser->where(['app_id' => $arrRequest['app_id']])->value('is_exchange');
            if ($res_exchange) {
                return $this->getInfoResponse(4004, '该奖品已经兑换！！');
            }


            $common_function = new  CommonFunction();
            $reward_real = $putNewRewardReal->create([
                'app_id' => $arrRequest['app_id'],
                'reward_id' => $arrRequest['reward_id'],
                'order_id' => date('YmdHis') . $common_function->random(5),
                'img' => $arrRequest['img'],
                'title' => $arrRequest['title'],
                'address' => $arrRequest['address'],
                'real_name' => $arrRequest['real_name'],
                'phone' => $arrRequest['phone'],
                'money' => $arrRequest['money'],
            ]);

            $putNewRewardUser->where(['app_id' => $arrRequest['app_id']])->update([
                'is_exchange' => 1,
            ]);

            return $this->getResponse('兑换成功！');

        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 我的邀请
     */
    public function myInvitation(Request $request, PutNewRankList $putNewRankList)
    {

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $res = Invite::getInviteUsers($arrRequest['app_id'], 1593532800, 1596211200);
            $valid = $putNewRankList->where(['app_id' => $arrRequest['app_id']])->value('success_add');

            return $this->getResponse([
                'valid' => empty($valid) ? 0 : $valid,
                'list' => $res
            ]);

        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }
}
