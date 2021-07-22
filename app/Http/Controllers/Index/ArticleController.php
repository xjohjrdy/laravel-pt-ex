<?php

namespace App\Http\Controllers\Index;

use App\Entitys\Article\AdInfo;
use App\Entitys\Article\ArticleInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Services\Common\CommonFunction;

class ArticleController extends Controller
{
    /**
     * 根据传入的page以及limit进行分页获取文章数据列表
     * @param ArticleInfo $articleInfo
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(ArticleInfo $articleInfo, Request $request)
    {
        $page = $request->get('page', 1);

        $cache_engine = 'redis';
        $cache_key = CommonFunction::getIndexArticlePageCacheKey($page);

        if ($page <= 20) {
            $ArticleInfo = Cache::store($cache_engine)->get($cache_key);
            if ($ArticleInfo === NULL) {
                return $this->getInfoResponse('4001', '服务器繁忙，请刷新再试。');
            }
        } else {
            $ArticleInfo = Cache::store($cache_engine)->get($cache_key);
            if ($ArticleInfo === NULL) {
                $ArticleInfo = $articleInfo
                    ->orderBy('id', 'desc')
                    ->paginate(20, ['id', 'addtime', 'infoid', 'title', 'userid', 'title', 'wximg', 'wxlink']);
                Cache::store($cache_engine)->put($cache_key, $ArticleInfo, 1);
            }
        }

        if (empty($ArticleInfo->items())) {
            return $this->getInfoResponse('4001', '指定文章列表不存在');
        }
        foreach ($ArticleInfo->items() as $singleInfo) {
            $addtime = strtotime($singleInfo->addtime);
            $com = intval((time() - $addtime) / 86400);
            if ($com > 0) {
                $time = $com . '天前';
            } else {
                $com2 = intval((time() - $addtime) / 3600);
                if ($com2 > 0) {
                    $time = $com2 . '小时前';
                } else {
                    $com3 = intval((time() - $addtime) / 60);
                    if ($com3 > 0) {
                        $time = $com3 . '分钟前';
                    } else {
                        $time = '刚刚';
                    }
                }
            }
            $singleInfo->time = $time;
            $singleInfo->userid = '****' . substr($singleInfo->userid, -4);
            if (preg_match('/^if.+else.+/isu', $singleInfo->title) > 0) {
                preg_match('/(?<=write\(\").+?(?=\"\))/', $singleInfo->title, $strTitleRes);
                $singleInfo->title = @$strTitleRes[0];
            }
        }

        return $this->getResponse($ArticleInfo);
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
     * 根据传入的infoid查询对应的文章，以及查询对相应的广告
     * @param ArticleInfo $articleInfo
     * @param AdInfo $adInfo
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show(ArticleInfo $articleInfo, AdInfo $adInfo, Request $request)
    {
        if (!$request->infoid) {
            throw new ApiException('infoid为空', '4001');
        }
        try {
            $singleInfo = $articleInfo
                ->where('infoid', $request->infoid)
                ->first(['title', 'addtime', 'infoid', 'content', 'adid', 'ifweizhi', 'is_quanping', 'qptime', 'is_quanping2', 'ifPublicNumber', 'zhedie']);
        } catch (\Exception $e) {
            throw new ApiException('数据查询错误', '3001');
        }
        if (empty($singleInfo)) {
            throw new ApiException('指定文章不存在', '4002');
        }

        if (preg_match('/^if.+else.+/isu', $singleInfo->title) > 0) {
            preg_match('/(?<=write\(\").+?(?=\"\))/', $singleInfo->title, $strTitleRes);
            $singleInfo->title = @$strTitleRes[0];
        }

        if (!empty($singleInfo->adid)) {
            $ad = $adInfo->where('id', $singleInfo->adid)->first();
            $singleInfo->ad = $ad;
        }

        return $this->getResponse($singleInfo);
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
