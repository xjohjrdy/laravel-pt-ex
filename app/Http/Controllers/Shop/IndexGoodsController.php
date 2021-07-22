<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\App\JsonConfig;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndexBoomRecommend;
use App\Entitys\App\ShopIndexSlideImage;
use App\Entitys\App\ShopIndexSortClass;
use App\Entitys\App\ShopVipBuy;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class IndexGoodsController extends Controller
{
    /**
     * 昨日。今日。明日抢购商品
     *  1   .  2  .  3
     * Display a listing of the resource.
     * get {"type":"1"}
     * @param Request $request
     * @throws ApiException
     */
    public function index(Request $request, ShopGoods $shopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('type', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $res = $shopGoods->getAllGoodsByType($arrRequest['type']);
            if (!$res) {
                $res = $shopGoods->getAllGoodsByType(4);
            }

            foreach ($res as $i) {
                $arr = json_decode($i->header_img, true);
                if (array_key_exists(0, $arr)) {
                    $i->header_img = $arr[0];
                } else {
                    $i->header_img = '';
                }
                $i->remain_time = $i->open_time - time();
                $all_volume = $i->sale_volume + $i->volume;
                $hope_volume = (int)($all_volume / 10) + rand(1, 9);
                if ($i->sale_volume < $hope_volume && $i->remain_time <= 0) {
                    $shopGoods->balanceNumber($i->id, $hope_volume);
                }

                if (($i->volume + $i->sale_volume) == 0) {
                    $i->percent = 0;
                } else {
                    $i->percent = $i->sale_volume / ($i->volume + $i->sale_volume);
                }

                if ($i->volume < 0) {
                    $i->volume = 0;
                }
            }

            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }

    /**
     * 获得新的接口数据(商城)
     */
    public function getNewPage(Request $request, ShopGoods $shopGoods, ShopIndexBoomRecommend $shopIndexBoomRecommend, ShopIndexSlideImage $shopIndexSlideImage, ShopIndexSortClass $shopIndexSortClass)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'type_sell_number' => 'required',
                'type_new_number' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //判断小程序是否展示多条
            $obj_config = new JsonConfig();
            $mini_version = empty($arrRequest['mini_version']) ? 0 : $arrRequest['mini_version'];
            $arr_config_data = $obj_config->getValue('mini_show_config');
            $int_web_config_data = $arr_config_data['mini_version'];
            if ($mini_version >= $int_web_config_data) {
                $boom_recommend = $shopIndexBoomRecommend->getPageMini($arrRequest['type_sell_number'], $arrRequest['type_new_number']);
                if ($request->input('page') > 1) {
                    $boom_recommend = new LengthAwarePaginator([], 0, 1);
                }
            } else {
                $boom_recommend = $shopIndexBoomRecommend->getPage($arrRequest['type_sell_number'], $arrRequest['type_new_number']);
            }

            foreach ($boom_recommend as $k => $item) {
                $obj_one_good = $shopGoods->getOneGood($item->good_id);
                if (empty($obj_one_good)) {
                    continue;
                }
                $boom_recommend[$k]->good_info = $shopGoods->getOneGood($item->good_id);
                $boom_recommend[$k]->good_info->header_img = json_decode($boom_recommend[$k]->good_info->header_img);
            }
            $slide_image = $shopIndexSlideImage->getPage();
            $sort_class = $shopIndexSortClass->getPage();

            return $this->getResponse([
                'slide_image' => $slide_image,
                'sort_class' => $sort_class,
                'boom_recommend' => $boom_recommend,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * 分类索引
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getTypeInfo(Request $request, ShopGoods $shopGoods)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'index' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //判断小程序是否展示多条
            $obj_config = new JsonConfig();
            $mini_version = empty($arrRequest['mini_version']) ? 0 : $arrRequest['mini_version'];
            $arr_config_data = $obj_config->getValue('mini_show_config');
            $int_web_config_data = $arr_config_data['mini_version'];
            if ($mini_version >= $int_web_config_data) {
                $goods = $shopGoods->getAllTypeGoodsWeb($arrRequest['index']);
                if (!empty($arrRequest['new'])) {
                    $order_by = 'created_at';
                    $fix = $arrRequest['new'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsWeb($arrRequest['index'], $order_by, $fix);
                }

                if (!empty($arrRequest['sell'])) {
                    $order_by = 'sale_volume';
                    $fix = $arrRequest['sell'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsWeb($arrRequest['index'], $order_by, $fix);
                }

                if (!empty($arrRequest['price'])) {
                    $order_by = 'price';
                    $fix = $arrRequest['price'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsWeb($arrRequest['index'], $order_by, $fix);
                }
            } else {
                $goods = $shopGoods->getAllTypeGoods($arrRequest['index']);
                if (!empty($arrRequest['new'])) {
                    $order_by = 'created_at';
                    $fix = $arrRequest['new'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoods($arrRequest['index'], $order_by, $fix);
                }

                if (!empty($arrRequest['sell'])) {
                    $order_by = 'sale_volume';
                    $fix = $arrRequest['sell'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoods($arrRequest['index'], $order_by, $fix);
                }

                if (!empty($arrRequest['price'])) {
                    $order_by = 'price';
                    $fix = $arrRequest['price'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoods($arrRequest['index'], $order_by, $fix);
                }
            }

            $goods->map(function ($model) {
                $model->header_img = json_decode($model->header_img);
            });

            return $this->getResponse($goods);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * 搜索关键词
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getSearchInfo(Request $request, ShopGoods $shopGoods, ShopVipBuy $shopVipBuy)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'text' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
//            $no_show = $shopVipBuy->get(['vip_id']);
//            if (empty($no_show)) {
//                $no_show_arr = [];
//            } else {
//                $no_show_arr = $no_show->toArray();
//                $no_show_arr = array_column($no_show_arr, 'vip_id');
//            }


//            $goods = $shopGoods->getAllSearchGoods($arrRequest['text'], $no_show_arr);

            //判断小程序是否展示多条
            $obj_config = new JsonConfig();
            $mini_version = empty($arrRequest['mini_version']) ? 0 : $arrRequest['mini_version'];
            $arr_config_data = $obj_config->getValue('mini_show_config');
            $int_web_config_data = $arr_config_data['mini_version'];
            $no_show_arr = [];
            if ($mini_version >= $int_web_config_data) {
                $goods = $shopGoods->getAllSearchGoodsWeb($arrRequest['text'], $no_show_arr);

                if (!empty($arrRequest['new'])) {
                    $order_by = 'created_at';
                    $fix = $arrRequest['new'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsNewWeb($arrRequest['text'], $order_by, $fix, $no_show_arr);
                }

                if (!empty($arrRequest['sell'])) {
                    $order_by = 'sale_volume';
                    $fix = $arrRequest['sell'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsNewWeb($arrRequest['text'], $order_by, $fix, $no_show_arr);
                }

                if (!empty($arrRequest['price'])) {
                    $order_by = 'price';
                    $fix = $arrRequest['price'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsNewWeb($arrRequest['text'], $order_by, $fix, $no_show_arr);
                }
            } else {
                $goods = $shopGoods->getAllSearchGoods($arrRequest['text'], $no_show_arr);

                if (!empty($arrRequest['new'])) {
                    $order_by = 'created_at';
                    $fix = $arrRequest['new'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsNew($arrRequest['text'], $order_by, $fix, $no_show_arr);
                }

                if (!empty($arrRequest['sell'])) {
                    $order_by = 'sale_volume';
                    $fix = $arrRequest['sell'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsNew($arrRequest['text'], $order_by, $fix, $no_show_arr);
                }

                if (!empty($arrRequest['price'])) {
                    $order_by = 'price';
                    $fix = $arrRequest['price'] == 1 ? 'asc' : 'desc';
                    $goods = $shopGoods->getAllTypeGoodsNew($arrRequest['text'], $order_by, $fix, $no_show_arr);
                }
            }

            $goods->map(function ($model) {
                $model->header_img = json_decode($model->header_img);
            });

            return $this->getResponse($goods);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
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
