<?php

namespace App\Http\Controllers\Article;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Article\AdInfo;
use App\Entitys\Article\ArticleInfo;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AdvertisementController extends Controller
{
    /**
     * @param Request $request
     * @param AdUserInfo $adUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, AdUserInfo $adUserInfo)
    {
        try {

            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest) {
                throw new ApiException('传入参数错误', '3001');
            }

            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            $ad = "http://api.36qq.com/getReward/" . $user->username;

            return $this->getResponse(['url' => $ad]);
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
     * post
     * 参数：{"user_id": "3","ad_title":1,"ad_link":"#","ad_img":"https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg"}
     * 新增用户广告
     * @param Request $request
     * @param AdInfo $adInfo
     * @param AdUserInfo $adUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, AdInfo $adInfo, AdUserInfo $adUserInfo)
    {

        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest) {
                throw new ApiException('传入参数错误', '3001');
            }
            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            $ad_count = $adInfo->getAdList($user->uid)->count();
            if ($ad_count > 10) {
                throw new ApiException('用户添加已经超过10条', '201');
            }
            $adInfo->addUserAd($user->uid, $arrRequest);

            return $this->getResponse("添加成功");

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }

    /**
     * get /ad/#id
     * 参数：{"user_id": "3","ad_id":3}
     * 返回值可能出现null情况
     * 展现当前用户单个指定广告
     * @param $id
     * @param Request $request
     * @param AdInfo $adInfo
     * @param AdUserInfo $adUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, AdInfo $adInfo, AdUserInfo $adUserInfo)
    {
        try {

            $arrRequest = json_decode($request->data, true);
            if (!array_key_exists('ad_id', $arrRequest)) {
                $arrRequest['ad_id'] = -1;
            }
            if (!$arrRequest) {
                throw new ApiException('传入参数错误', '3001');
            }
            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            if (empty($user)) {
                return $this->getInfoResponse('4004', '请先使用广告联盟，才能获得我的币哦！');
            }
            $ad = $adInfo->getUserAd($user->uid, $arrRequest['ad_id']);
            if (empty($ad)) {
                $ad = $adInfo->getUserAd($user->uid, -1);
                if (empty($ad)) {
                    $ad = $adInfo->getUserAd('1499531', -1);
                    $ad->id = -1;
                }
            }
            $ad->ad_link = "http://api.36qq.com/getReward/" . $user->username;


            return $this->getResponse($ad);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     *
     * /xxx/{id}/edit
     * 修改用户广告
     * 参数:{"user_id": "3","ad_id":"3","ad_title":1,"ad_link":"#","ad_img":"https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg"}
     * @param $id
     * @param Request $request
     * @param AdUserInfo $adUserInfo
     * @param AdInfo $adInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function edit($id, Request $request, AdUserInfo $adUserInfo, AdInfo $adInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || $arrRequest['ad_id'] != $id) {
                throw new ApiException('传入参数错误', '3001');
            }

            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            $ad = $adInfo->getUserAd($user->uid, $arrRequest['ad_id']);

            if (!$ad) {
                throw new ApiException('资源不存在', '4004');
            }
            $ad->addUserAd($user->uid, $arrRequest);

            return $this->getResponse("更新成功");

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }


    }

    /**
     * 点击广告，用户获得我的币奖励
     * put行为
     * /ad/123
     * 输入值：data : {"user_id": "3"}
     * @param Request $request
     * @param $id
     * @param UserCreditLog $creditLog
     * @param UserAboutLog $aboutLog
     * @param AdUserInfo $adUserInfo
     * @param UserAccount $userAccount
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, UserCreditLog $creditLog, UserAboutLog $aboutLog, AdUserInfo $adUserInfo, UserAccount $userAccount)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || $arrRequest['user_id'] != $id) {
                throw new ApiException('传入参数错误', '3001');
            }
            $time = strtotime(date("Y-m-d"), time());
            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            $log = $creditLog->getLastLog($user->uid, "PTG");
            $count_log = $creditLog->getCountLog($user->uid, "PTG", $time);
            $time_check = time() - $log->dateline;
            if ($time_check < 86400 && $count_log >= 3) {
                return $this->getResponse(['status' => "不符合赠送我的币情况", "url" => "https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg"]);
            }
            $userAccount->addPTBMoney(1, $user->uid);
            $account = $userAccount->getUserAccount($user->uid);
            $insert_id = $creditLog->addLog($user->uid, "PTG", ['extcredits4' => 1]);
            $extcredits4_change = $account->extcredits4 - 1;
            $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $extcredits4_change], ["extcredits4" => $account->extcredits4]);

            return $this->getResponse(['status' => "赠送我的币", "url" => "https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg"]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     *
     * delete行为 /ad/#id
     * 参数：{"user_id": "3","ad_id":1}
     * 删除用户广告
     * @param $id
     * @param Request $request
     * @param AdUserInfo $adUserInfo
     * @param AdInfo $adInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     * @throws \Exception
     */
    public function destroy($id, Request $request, AdUserInfo $adUserInfo, AdInfo $adInfo)
    {

        try {

            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || $arrRequest['ad_id'] != $id) {
                throw new ApiException('传入参数错误', '3001');
            }
            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            $ad = $adInfo->getUserAd($user->uid, $arrRequest['ad_id']);
            $ad->delete();

            if ($ad->trashed()) {
                return $this->getResponse("删除成功");
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 点击广告，用户获得我的币奖励
     * 用于web点击，兼容旧版
     * 无论何种情况，都会重定向到广告页面
     * @param Request $request
     * @param UserCreditLog $creditLog
     * @param UserAboutLog $aboutLog
     * @param AdUserInfo $adUserInfo
     * @param UserAccount $userAccount
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function webUpdate($id, Request $request, UserCreditLog $creditLog, ArticleInfo $articleInfo, UserAboutLog $aboutLog, AdUserInfo $adUserInfo, UserAccount $userAccount)
    {
//        return "<script>
//alert('该文章请稍候再试！');
//        function htmlDecode(text){
//            var temp = document.createElement(\"div\");
//            temp.innerHTML = text;
//            var output = temp.innerText || temp.textContent;
//            temp = null;
//            return output;
//        }
//var url = '" . $request->url . "';
//var uri = htmlDecode(url);
//console.log(uri);
//window.location.replace(uri);
//</script>
//";

        $redis_key = 'profit_touch_' . $request->article_id;

        if (Cache::has($redis_key)) {
            return "<script>
alert('该文章点击过于频繁，请稍候再试！');
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $request->url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
        }

        Cache::put($redis_key, $request->article_id, 5);
        if (empty($request->url)) {
            $request->url = 'http://weixin.sogou.com/api/share?timestamp=1536670807&amp;signature=qIbwY*nI6KU9tBso4VCd8lYSesxOYgLcHX5tlbqlMR8N6flDHs4LLcFgRw7FjTAO8s-k3BVo-d9v6nRAE*wtznaJo8-inYzQDaYxrqwBjB-ZlchthOo7ZQnv7iCwdqkd22US2GOpkIpdIp9W7e8JpULuMwbAirZ3M3OlBMe8hyE8lRZZS3wKIjPjPUfRtXdaE1UUsMYN-ciqSDuiUf-LMF1dJr4HgQ-YYFUL2r3-bnA=';
        }
        if (empty($request->article_id)) {
            return "<script>
alert('文章必须分享到微信！才能点击获得我的币哦！');
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $request->url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
        }

        $request->url = urldecode($request->url);
        if (!$id) {
            return "<script>
alert('当前没有选择用户！');
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $request->url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
        }
        $time = strtotime(date("Y-m-d"), time());
        $user = $adUserInfo->getUserByUsername($id);
        if (empty($user)) {
            return "<script>
alert('当前分享的文章手机号无法匹配对应用户，请用户修改正确的手机号！');
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $request->url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
        }
        $article_info = $articleInfo->getByInfoId($request->article_id);

        if ($article_info->touch_number >= 3) {
            return "<script>
alert('当前文章点击我的币已经达到上限！请重新发布新的文章！');
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $request->url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
        }

        $log = $creditLog->getLastLog($user->uid, "PTG");
        $count_log = $creditLog->getCountLog($user->uid, "PTG", $time);
        if (!$log) {
            $time_check = 0;
        } else {
            $time_check = time() - $log->dateline;
        }
        if ($time_check < 86400 && $count_log >= 3) {
            return "<script>
alert('当前领取我的币已经上限！');
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $request->url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
        }
        $userMoneyService = new UserMoney();
        $userMoneyService->plusCnyAndLog($user->pt_id, 0.1, '61');
//        $account = $userAccount->getUserAccount($user->uid);
//        if ($account) {
//            $account->addPTBMoney(($account->extcredits4 + 1), $user->uid);
//        }
//        $insert_id = $creditLog->addLog($user->uid, "PTG", ['extcredits4' => 1]);
//        $extcredits4_change = $account->extcredits4 + 1;
//        $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
        $articleInfo->addUserPTBMoney(1, $request->article_id);

        return "<script>
alert('已经为此文章作者增加0.1元余额！');
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $request->url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
    }
}
