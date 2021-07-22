<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\App\ShopCarts;
use App\Entitys\App\ShopGoods;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartsController extends Controller
{
    /**
     * 列出当前所有的购物车内容
     * get {"app_id":"1"}
     * @param Request $request
     * @param ShopCarts $shopCarts
     * @param ShopGoods $shopGoods
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, ShopCarts $shopCarts, ShopGoods $shopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $res = $shopCarts->getAllCarts($arrRequest['app_id']);

            foreach ($res as $shop => $value) {
                $shop_one = $shopGoods->getOneGood($value['good_id']);
                if ($shop_one) {
                    $res[$shop]['title'] = $shop_one->title;
                    $res[$shop]['header_img'] = json_decode($shop_one->header_img);
                    $res[$shop]['price'] = $shop_one->price;
                    $res[$shop]['ptb_price'] = $shop_one->price * 10;
                    $res[$shop]['open_time_test'] = $shop_one->open_time;
                    $res[$shop]['is_open_time_yes'] = $shop_one->open_time > time() ? 0 : 1;
                } else {
                    unset($res[$shop]);
                    continue;
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * 用户创建或者添加购物车商品 （用于用户商品那边点击增加）type永远传1
     *
     * 购物车增加数量、减少数量 1:代表增加，2：代表减少
     *
     * post {"app_id":"1","good_id":"1","shop_id":"1","number":"2","type":"1","desc":""}
     * @param Request $request
     * @param ShopCarts $shopCarts
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, ShopCarts $shopCarts)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('good_id', $arrRequest) || !array_key_exists('shop_id', $arrRequest) || !array_key_exists('number', $arrRequest) || !array_key_exists('type', $arrRequest) || !array_key_exists('desc', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            if ($arrRequest['type'] == 1) {
                $res = $shopCarts->getOneCart($arrRequest['app_id'], $arrRequest['good_id'], $arrRequest['shop_id'], $arrRequest['desc']);
                if ($res) {
                    $shopCarts->addNumber($res->id, $arrRequest['number']);
                } else {
                    $shopCarts->addShopInCarts($arrRequest['app_id'], $arrRequest['good_id'], $arrRequest['shop_id'], $arrRequest['number'], $arrRequest['desc']);
                }
            }

            if ($arrRequest['type'] == 2) {
                $res = $shopCarts->getOneCart($arrRequest['app_id'], $arrRequest['good_id'], $arrRequest['shop_id'], $arrRequest['desc']);
                if ($res) {
                    if ($res->number <= 1) {
                        throw new ApiException('商品数量已经不能再减少了！', '4001');
                    }
                    $shopCarts->reduceNumber($res->id, $arrRequest['number']);
                } else {
                    throw new ApiException('商品还没有被添加！', '4004');
                }
            }

            $count = count($shopCarts->getAllCarts($arrRequest['app_id']));

            return $this->getResponse(['remark' => '添加成功！', 'number' => $count]);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }

    /**
     * 购物车数量标志
     * Display the specified resource.
     * get /carts/1
     * {"app_id":""}
     * @param $id
     * @param Request $request
     * @param ShopCarts $shopCarts
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, ShopCarts $shopCarts)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $count = count($shopCarts->getAllCarts($arrRequest['app_id']));

            return $this->getResponse($count);

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * /edit/1
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {

    }

    /**
     * 删除打钩的商品
     * put /1
     * {"goods":["1","2"],"app_id":"1"}
     * @param Request $request
     * @param $id
     * @param ShopCarts $shopCarts
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, ShopCarts $shopCarts)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('goods', $arrRequest) || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            foreach ($arrRequest['goods'] as $v) {
                $shopCarts->deleteOneGood($arrRequest['app_id'], $v);
            }

            return $this->getResponse('删除成功！');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }

    /**
     * 清理商品
     * delete
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function destroy(Request $request, $id, ShopCarts $shopCarts)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $shopCarts->deleteAllGood($arrRequest['app_id']);

            return $this->getResponse('删除成功！');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }
}
