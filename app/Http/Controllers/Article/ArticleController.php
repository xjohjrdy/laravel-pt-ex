<?php

namespace App\Http\Controllers\Article;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Article\AdInfo;
use App\Entitys\Article\Agent;
use App\Entitys\Article\Article;
use App\Entitys\Article\ArticleInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /**
     *
     * 展示头条信息
     * {"sort":"0","limit":"20","static":"1"}
     * @param Request $request
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, Article $article)
    {
        try {

            return $this->getInfoResponse('44411', '旧版关闭！');
            $arrRequest = json_decode($request->data, true);
            $limit = !empty($arrRequest['limit']) ? $arrRequest['limit'] : 20;
            $sort = !empty($arrRequest['sort']) ? $arrRequest['sort'] : rand(1, 20);
            $static = !empty($arrRequest['static']) ? $arrRequest['static'] : 0;
            if (Cache::has('index_article_' . $request->ip())) {
                return $this->getInfoResponse('1005', '刷新太频繁，请稍后再试！');
            }
            Cache::put('index_article_' . $request->ip(), 1, 0.1);
            if ($sort == 99) {
                $sort = rand(1, 20);
            }
            $ArticleInfo = DB::connection('a1191125678')->table('tbl_article')
                ->select(['id', 'title', 'static', 'abstract', 'list_img', 'sort', 'vipcn_name', 'article_sg_url'])
                ->where(['static' => $static, 'sort' => $sort])
                ->orderBy('addtime', 'desc')
                ->paginate($limit);

            if (empty($ArticleInfo->items())) {
                return $this->getInfoResponse('4004', '当前分类文章已经发完，请尝试其他分类！');
            }


            return $this->getResponse($ArticleInfo);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
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
     * 发布头条---注意广告发布肯定除了一般情况，还会有其他的传值，这里暂时默认为下半部分的展示
     * {"user_id": "3","article_id":"1","type":"2","ad_link":"#","ad_img":"https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg"}
     * {"user_id": "3","article_id":"1","type":"3","ad_context":"跑马灯内容"}
     * {"user_id": "3","article_id":"1","type":"3","ad_context":"跑马灯内容","ad_id":"1103"}
     * {"user_id": "3","article_id":"1","type":"2","ad_link":"#","ad_img":"https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg","ad_id":"1103"}
     *
     * {"user_id": "3","article_id":"1","type":"2","ad_link":"#","ad_img":"https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg","ad_id":"1103","ad_context":"跑马灯内容"}
     *
     * @param Request $request
     * @param AdInfo $adInfo
     * @param Agent $agent
     * @param Article $article
     * @param ArticleInfo $articleInfo
     * @param AdUserInfo $adUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, AdInfo $adInfo, Agent $agent, Article $article, ArticleInfo $articleInfo, AdUserInfo $adUserInfo, RechargeOrder $rechargeOrder)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $arrRequest['ad_link'] = $request->ad_link;
            $arrRequest['ad_img'] = $request->ad_img;
            $arrRequest['ad_context'] = $request->ad_context;
            $all_long = strlen($articleInfo['ad_context']);

            if (!$arrRequest || !array_key_exists('user_id', $arrRequest) || !array_key_exists('article_id', $arrRequest) || !array_key_exists('type', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['type'] == 2 && (!$arrRequest['ad_img'] || !$arrRequest['ad_link'])) {
                return $this->getInfoResponse('3002', '传入底部固定参数错误！');
            }
            if ($all_long > 100) {
                return $this->getInfoResponse('3003', '传入跑马灯参数过长！');
            }
            if ($arrRequest['type'] == 3 && !$arrRequest['ad_context']) {
                return $this->getInfoResponse('3003', '传入跑马灯参数错误！');
            }
            if (!array_key_exists('ad_id', $arrRequest)) {
                $arrRequest['ad_id'] = -1;
            }
            $number = $agent->checkNumber($arrRequest['user_id']);
            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
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
            $ad = $adInfo->updateOrInsertUserAd($user->uid, $arrRequest, $user->username);
            if (!$ad) {
                return $this->getInfoResponse('4002', '请重新添加一下图片!');
            }
            $article_user = $article->getCanUseArticle($arrRequest['article_id']);
            if (!$article_user) {
                return $this->getInfoResponse('4003', '这文章已经被使用了，您手速慢了哦！');
            }
            $res = $articleInfo->addNewArticle($article_user, $ad, $user->username, $arrRequest['type']);
            if ($res) {
                $article_user->usingArticle();
                $agent->decrementUserArticle($user->pt_id, $number - 1);
            }
            return $this->getResponse("发布成功");
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * get / news / # id
     * 展示当前用户文章列表
     * data :{"user_id": "3"}
     * @param $id
     * @param Request $request
     * @param AdUserInfo $adUserInfo
     * @param ArticleInfo $articleInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, AdUserInfo $adUserInfo, ArticleInfo $articleInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || $arrRequest['user_id'] != $id) {
                throw new ApiException('传入参数错误', '3001');
            }

            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);

            if (!$user) {
                return $this->getInfoResponse('4004', '未注册广告联盟！');
            }

            $articles = $articleInfo->getByUsername($user->username);

            if (!$articles) {
                return $this->getInfoResponse('4005', '用户没有文章！');
            }

            return $this->getResponse([
                'count' => $articles->count(),
                'sum' => $articles->sum('touch_number'),
                'list' => $articles,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
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
