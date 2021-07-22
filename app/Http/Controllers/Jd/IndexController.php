<?php

namespace App\Http\Controllers\Jd;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entitys\App\JdIndexShow;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IndexController extends Controller
{
    /**
     * 获取京东及拼多多首页banner轮播图片列表及商品分类列表
     * @param Request $request
     * @param JdIndexShow $jdIndexShow
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function indexBannerAndSorts(Request $request, JdIndexShow $jdIndexShow)
    {
        try {

            if (Cache::has('jd_pdd_index_banner_sort_list')) {
                $result = Cache::get('jd_pdd_index_banner_sort_list');
            } else {
                $jd_banner = $jdIndexShow->getListByType(1);
                $jd_sort = $jdIndexShow->getListByType(2);
                $pdd_banner = $jdIndexShow->getListByType(3);
                $pdd_sort = $jdIndexShow->getListByType(4);
                $result = [
                    'jd_banner' => $jd_banner,
                    'jd_sort' => $jd_sort,
                    'pdd_banner' => $pdd_banner,
                    'pdd_sort' => $pdd_sort
                ];
                Cache::put('jd_pdd_index_banner_sort_list', $result, 5);
            }

            return $this->getResponse($result);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
