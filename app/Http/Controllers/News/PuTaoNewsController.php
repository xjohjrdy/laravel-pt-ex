<?php

namespace App\Http\Controllers\News;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\ArticleInfo;
use App\Entitys\Article\Agent;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class PuTaoNewsController extends Controller
{
    /**
     * 文章上传接口
     */
    public function releaseArticle(Request $request, Agent $agent, AdUserInfo $adUserInfo, ArticleInfo $articleInfo, RechargeOrder $rechargeOrder, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required', //用户唯一id
                'article_id' => 'required', //用户唯一id
                'ad_where' => 'required',//广告位置（0：顶部固定)
                'ad_url' => 'required', //广告图片
                'ad_jump_url' => 'required', // 广告点击过后跳转目标
                'style_type' => 'required',// 当前时间，服务器时间戳（秒做为单位）
                'item_type' => 'required', //文章类型
                'url' => 'required', //正文页链接（第三方的）
                'publish_time' => 'required', //文章发布的时间
                'from_time' => 'required', //计算过后的转换时间（xx分钟前）
                'cmt_cnt' => 'required', //评论数量
                'source_name' => 'required', //来源文字
                'title' => 'required', //文章标题
//                'subhead' => 'required', //文章副标题，只有运营类文章可能有这个元素
                'img_url' => 'required', //图文链接（json字符串）
                'touch_number' => 'required', //评论数量 cmt_cnt
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $all_long = strlen($arrRequest['ad_jump_url']);
            if ($all_long > 100) {
                return $this->getInfoResponse('3003', '广告点击参数过长！');
            }

            $number = $agent->checkNumber($arrRequest['app_id']);
            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            if ($user->groupid == 23 && $number <= 0) {
                return $this->getInfoResponse('4001', '您的剩余文章数不足，下个月再发布哦！');
            }

            $rechargeOrder->where(['uid' => $user->uid])->where('price', '<', '50')->first();
            if (!empty($rechargeOrder) && $number <= 0) {
                return $this->getInfoResponse('4441', '您剩余文章数为0，无法发布哦。');
            }
            if ($number <= 0) {
                return $this->getInfoResponse('4441', '超级用户才可以每个月发布我的头条哦。');
            }

            $app_user = $appUserInfo->getUserById($arrRequest['app_id']);
            $user_name = empty($app_user['user_name']) ? '0' : $app_user['user_name'];
            $touch_number = $arrRequest['touch_number'] + 999;
            $article_id = $arrRequest['article_id'] . '.' . $arrRequest['app_id'];
            $data = [
                'app_id' => $arrRequest['app_id'],
                'article_id' => $article_id,
                'touch_number' => $touch_number,
                'style_type' => $arrRequest['style_type'],
                'item_type' => $arrRequest['item_type'],
                'url' => $arrRequest['url'],
                'publish_time' => $arrRequest['publish_time'],
                'from_time' => $arrRequest['from_time'],
                'cmt_cnt' => $arrRequest['cmt_cnt'],
                'source_name' => $arrRequest['source_name'],
                'title' => $arrRequest['title'],
                'subhead' => empty($arrRequest['subhead']) ? '0' : $arrRequest['subhead'],
                'img_url' => $arrRequest['img_url'],
                'ad_where' => $arrRequest['ad_where'],//
                'ad_url' => $arrRequest['ad_url'],
                'ad_jump_url' => $arrRequest['ad_jump_url'],
                'touch_get_number' => 0,
                'my_url' => 'http://a001.p17t.com/article_share/#/?article_id=' . $article_id,
                'user_name' => $user_name
            ];
            foreach ($data as $k => $v) {
                if (empty($v)) {
                    unset($data[$k]);
                }
            }
            $article = $articleInfo->where(['article_id' => $article_id])->first();
            if (empty($article)) {
                $articleInfo->create($data);
                $agent->decrementUserArticle($user->pt_id, $number - 1);
                return $this->getResponse('');//正常返回数据
            } else {
                return $this->getInfoResponse(300, '该文章已被发布!');
            }

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 根据app_id获取用户发布的文章列表
     */
    public function getUserArticles(Request $request, ArticleInfo $articleInfo)
    {
        try {//仅用于测试兼容旧版-start
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required', //用户唯一id
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $result = $articleInfo->getArticlesByPage(['app_id' => $arrRequest['app_id']]);
            $new_arr = $result->toArray();
            $new_data = $new_arr['data'];
            foreach ($new_data as $key => $item) {

                $new_data[$key]['img_url'] = json_decode(@$item['img_url'], true);
                $new_data[$key]['ad_jump_url'] = str_replace('api.36qq.com', 'api_new.36qq.com', @$item['ad_jump_url']);
                $new_data[$key]['my_url'] = str_replace('api.36qq.com', 'api_new.36qq.com', @$item['my_url']);
            }
            $response = [
                'data' => $new_data,
                'lastPage' => $new_arr['last_page'],
                'currentPage' => $new_arr['current_page'],
            ];

            return $this->getResponse($response);//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取所有用户发布的文章列表
     */
    public function getAllUserArticles(Request $request, ArticleInfo $articleInfo)
    {
        try {//仅用于测试兼容旧版-start
            $data = Input::all();
            $page = empty($data['page']) ? '1' : $data['page'];
            $cache_key = 'uc_news_insert_ad_release_list_' . $page;
            $response = [];
            if (Cache::has($cache_key)) {
                $response = Cache::get($cache_key);
            } else {
                $result = $articleInfo->getArticlesByPage();
                $new_arr = $result->toArray();
                $new_data = $new_arr['data'];
                foreach ($new_data as $key => $item) {
                    $header = empty($item['user_name']) ? '*' : $item['user_name'];
                    $header = mb_substr($header, 0, 1, "utf-8");
                    $new_data[$key]['user_name'] = $header . '**';
                    $new_data[$key]['img_url'] = json_decode(@$item['img_url'], true);
                    $new_data[$key]['cmt_cnt'] = $new_data[$key]['cmt_cnt'] + 999;
                    $new_data[$key]['created_at'] = date('m-d H:i', strtotime($new_data[$key]['created_at']));
                }
                $response = [
                    'data' => $new_data,
                    'lastPage' => $new_arr['last_page'],
                    'currentPage' => $new_arr['current_page'],
                ];
                Cache::put($cache_key, $response, 6);
            }

            return $this->getResponse($response);//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取用户个人信息
     */
    public function getReleaseArticlesInfo(Request $request, ArticleInfo $articleInfo, AppUserInfo $user, Agent $agent)
    {
        try {
//            if($request->header('data')){
//                $request->data = $request->header('data');
//            }
            $data_arr = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];

            $validator = Validator::make($data_arr, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $data_arr['app_id'];
            $user_info = $user->getUserById($app_id); # 获取app_id 和 用户电话
            $had_released_ad_count = $articleInfo->where(['app_id' => $app_id])->count(); # 用户已投广告的数量
            $lift_ad_package_count = $agent->checkNumber($app_id); # 剩余广告包数量
            $valid_click_num = $articleInfo->where(['app_id' => $app_id])->sum('touch_get_number'); # 有效点击数
            $phone = empty($user_info['phone']) ? '未绑定手机号' : $user_info['phone'];
            return $this->getResponse([
                'phone' => $phone,
                'released_count' => $had_released_ad_count, #已投放广告数量
                'lift_package' => $lift_ad_package_count, #剩余广告包
                'click_num' => $valid_click_num #有效点击数
            ]);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 根据app_id | article_id文章id获取用户发布的文章列表
     */
    public function getArticlesById(Request $request, ArticleInfo $articleInfo)
    {
        try {//仅用于测试兼容旧版-start
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'article_id' => 'required', // 文章ID
            ];

            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $result = $articleInfo->getOneArticles(['article_id' => $arrRequest['article_id']]);
            return $this->getResponse($result);//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
