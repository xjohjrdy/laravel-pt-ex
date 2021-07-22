<?php

namespace App\Http\Controllers\News;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\ArticleCheckInfo;
use App\Entitys\App\ArticleInfo;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AdController extends Controller
{

    /**
     * 点击广告，用户获得余额奖励
     * 用于web点击，兼容旧版
     * 无论何种情况，都会重定向到广告页面
     * @param Request $request
     * @param UserCreditLog $creditLog
     * @param UserAboutLog $aboutLog
     * @param AdUserInfo $adUserInfo
     * @param UserAccount $userAccount
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function webUpdate($id, Request $request, ArticleInfo $articleInfo, UserCreditLog $creditLog, UserAboutLog $aboutLog, AdUserInfo $adUserInfo, UserAccount $userAccount, ArticleCheckInfo $articleCheckInfo)
    {

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'article_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $redis_key = 'profit_touch_' . $arrRequest['article_id'];

            if (Cache::has($redis_key)) {
                return $this->getInfoResponse('4884', '该文章点击过于频繁，请稍候再试！');
            }

            Cache::put($redis_key, $arrRequest['article_id'], 5);


            if (!$id) {
                return $this->getInfoResponse('4114', '当前未选择！');
            }


            $article_info = $articleInfo->getOneArticles(['article_id' => $arrRequest['article_id']]);

            $articleInfo->updateData($arrRequest['article_id'], 'touch_number', ($article_info->touch_number + 1));


            $id = $article_info->app_id;

            $time = strtotime(date("Y-m-d"), time());
            $user = $adUserInfo->appToAdUserId($id);

            if (empty($user)) {
                return $this->getInfoResponse('4884', '用户不正确，请稍候再试！');
            }

            if ($article_info->touch_get_number >= 3) {
                return $this->getInfoResponse('4224', '当前文章点击余额已经达到上限！请重新发布新的文章！');
            }


            $log_1 = $articleCheckInfo->getLastLog($id);
            $count_log_1 = $articleCheckInfo->getCountLog($id, $time);

            if (!$log_1) {
                $time_check_1 = 0;
            } else {
                $time_check_1 = time() - $log_1->check_time;
            }
            if ($time_check_1 < 86400 && $count_log_1 >= 3) {
                return $this->getInfoResponse('4334', '当前余额已经领取达到上限！');
            }
            

            $log = $creditLog->getLastLog($user->uid, "PTG");
            $count_log = $creditLog->getCountLog($user->uid, "PTG", $time);
            if (!$log) {
                $time_check = 0;
            } else {
                $time_check = time() - $log->dateline;
            }
            if ($time_check < 86400 && $count_log >= 3) {
                return $this->getInfoResponse('4134', '当前余额已经领取达到上限！！');
            }

            $articleInfo->updateData($arrRequest['article_id'], 'touch_get_number', ($article_info->touch_get_number + 1));
            $articleCheckInfo->addInfo([
                'article_id' => $arrRequest['article_id'],
                'app_id' => $id,
                'check_time' => $time,
            ]);

            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($user->pt_id, 0.1, '61');

//            $account = $userAccount->getUserAccount($user->uid);
//            if ($account) {
//                $account->addPTBMoney(($account->extcredits4 + 1), $user->uid);
//            }
//            $insert_id = $creditLog->addLog($user->uid, "PTG", ['extcredits4' => 1]);
//            $extcredits4_change = $account->extcredits4 + 1;
//            $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);


            return $this->getResponse('为该作者增加余额！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }


    }


    public function getMyUrl(Request $request)
    {
        $my_url = 'http://api.36qq.com/api/get_reward_new/' . $request->app_id;
        return $this->getResponse($my_url);
    }


}
