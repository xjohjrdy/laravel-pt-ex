<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndex;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SpecialController extends Controller
{
    /**
     * 展示特殊商品
     * @param ShopIndex $shopIndex
     * @param ShopGoods $shopGoods
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(ShopIndex $shopIndex, ShopGoods $shopGoods)
    {
        try {

            $cache_key = 'shop_special_index_wuhang_2019_0925_cache';
            if (!Cache::has($cache_key)) {

                $shop_index = $shopIndex->where(['id' => 26])->get();
                $shop_index = $shop_index->toArray();
                $content = json_decode($shop_index[0]['content'], true);
                if ($content) {
                    foreach ($content as $k => $item) {
                        $good = $shopGoods->getOneById($k);
                        if ($good) {
                            $good->small_img = $item;
                            $good->header_img = json_decode($good->header_img);
                            $shop_index[] = $good;
                        }
                    }
                }

                Cache::put($cache_key, $shop_index, 10);
            }

            $shop_index = Cache::get($cache_key);


            return $this->getResponse($shop_index);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
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
