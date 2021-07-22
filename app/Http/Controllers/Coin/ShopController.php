<?php

namespace App\Http\Controllers\Coin;

use App\Entitys\App\CoinShopGoods;
use App\Entitys\App\CoinShopGoodsJump;
use App\Entitys\App\CoinShopOrders;
use App\Entitys\App\CoinUser;
use App\Entitys\App\ShopAddress;
use App\Exceptions\ApiException;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\CoinPlate\CoinConst;
use App\Services\CoinPlate\Orders;
use App\Services\HeMengTong\HeMeToServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    //
    /**
     * 商品列表
     */
    public function goods(Request $request, CoinShopGoods $coinShopGoods, CoinShopGoodsJump $coinShopGoodsJump)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $turntable = $coinShopGoods->getCoinTurntable();


            $matter = $coinShopGoods->getCoinMatter();

            $jump = $coinShopGoodsJump->getJump();

            if (!empty($jump)) {
                $jump->page_params = json_decode($jump->page_params);
            }

            foreach ($matter as $k => $item) {
                $matter[$k]['faker_sale_volume'] = $matter[$k]['sale_volume'] + 0;
            }

            return $this->getResponse([
                'turntable' => $turntable,
                'matter' => $matter,
                'jump' => $jump
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 商品详情
     */
    public function details(Request $request, CoinShopGoods $coinShopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'good_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $goods = $coinShopGoods->getOne($arrRequest['good_id']);
            $goods->faker_sale_volume = $goods->sale_volume + 0;

            return $this->getResponse($goods);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 下单支付
     */
    public function pay(Request $request, CoinShopGoods $coinShopGoods, CoinUser $coinUser)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'good_id' => 'required',
                'zone' => 'required',
                'collection' => 'required',
                'phone' => 'required',
                'detail' => 'required',
                'custom' => 'required',
                'number' => 'required',
                'pay_type' => 'required',//区分微信支付宝
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $good = $coinShopGoods->getAllOne($arrRequest['good_id']);

            if ($good->volume <= 0) {
                return $this->getInfoResponse('4141', '该礼品已被抢光啦，再看看其他商品吧！');
            }

            if ($good->type == 1) {
                $coinShopOrders = new CoinShopOrders();
                $res = $coinShopOrders->getAppidGoodid($arrRequest['app_id'], $arrRequest['good_id']);
                if (!empty($res)) {
                    return $this->getInfoResponse('4041', '特殊商品每日只能购买一次');
                }
            }

//            $now_time = time();
//            if ($good->start_time > $now_time) {
//                return $this->getInfoResponse('99', '未到开始时间');
//            }
//
//            if ($good->end_time < $now_time) {
//                return $this->getInfoResponse('89', '已经结束');
//            }

            $deduct_coin = ($good->coin) * $arrRequest['number'];
            $coin_user = $coinUser->where(['app_id' => $arrRequest['app_id']])->first();

            if ($coin_user->coin < $deduct_coin) {
                return $this->getInfoResponse('4441', '账户金币余额不足！');
            }


            $deduct_money = ($good->price) * $arrRequest['number'];


            $orders = new Orders();

            $zone = $arrRequest['zone'];
            $collection = $arrRequest['collection'];
            $phone = $arrRequest['phone'];
            $detail = $arrRequest['detail'];
            $app_id = $arrRequest['app_id'];
            $number = $arrRequest['number'];
            $custom = $arrRequest['custom'];
            $good_id = $arrRequest['good_id'];
            $type = $arrRequest['pay_type'];

            $order_id = $orders->generate($zone, $collection, $phone, $detail, $app_id, $number, $custom, $good_id, $type);

            $coin_shop_orders = new CoinShopOrders();
            $coin_shop_orders->where(['order_id' => $order_id])->update([
                'click_time' => time()
            ]);

            $res = 'fail';
            if ($deduct_money > 0) {
                $HeMeToServices = new HeMeToServices();
                if ($type == 1) {
                    $data = $HeMeToServices->appWxPayCoinShop($deduct_money, $order_id, $app_id);
                    return $this->getResponse($data);
                }

                if ($type == 2) {
                    $data = $HeMeToServices->appPayCoinShop($deduct_money, $deduct_money, $order_id);
                    $res = json_decode($data, true);
                    if (@$res['fcode'] != 10000) {
                        return $this->getResponse('购买失败！请联系客服');
                    }
                    return $this->getResponse(@$res['fcode_url']);
                }

            } else {
                //更新订单号状态
                $res = $orders->handle($order_id);
            }


            return $this->getResponse($res);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 运费接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function area(Request $request, Orders $orders)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'zone' => 'required',
                'goods_id' => 'required',
                'number' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $express = $orders->noArea($arrRequest['zone'], $arrRequest['goods_id'], $arrRequest['number']);

            $shopGoods = new CoinShopGoods();
            $good = $shopGoods->getAllOne($arrRequest['goods_id']);

            $express = $express == 0 ? '0.00' : round($express, 2);
            return $this->getResponse([
                'express' => $express,
                'pay_all' => $good->price + $express,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 默认地址
     */
    public function getDefaultAddress(Request $request, ShopAddress $shopAddress)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $address = $shopAddress->getUserDefaultAddress($arrRequest['app_id']);

            if (empty($address)) {
                return $this->getInfoResponse('4005', '不存在默认地址，请填写');
            }

            return $this->getResponse($address);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 兑换记录
     */
    public function getLog(Request $request, CoinShopOrders $coinShopOrders)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $shop_orders = $coinShopOrders->getPage($arrRequest['app_id']);

            return $this->getResponse($shop_orders);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 是否可以购买
     */
    public function isBuy(Request $request, CoinUser $coinUser, CoinShopGoods $coinShopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'coin_buy' => 'required',
                'good_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $deduct_coin = $arrRequest['coin_buy'];

            $coin_user = $coinUser->where(['app_id' => $arrRequest['app_id']])->first();

            if (empty($coin_user)) {
                return $this->getInfoResponse('4441', '您的金币不足！快去做任务领金币吧');
            }

            if ($coin_user->coin < $deduct_coin) {
                return $this->getInfoResponse('4441', '您的金币不足！快去做任务领金币吧');
            }

            $good = $coinShopGoods->getAllOne($arrRequest['good_id']);

            if ($good->volume <= 0) {
                return $this->getInfoResponse('4141', '该礼品已被抢光啦，再看看其他商品吧！');
            }

            if ($good->type == 1) {
                $coinShopOrders = new CoinShopOrders();
                $res = $coinShopOrders->getAppidGoodid($arrRequest['app_id'], $arrRequest['good_id']);
                if (!empty($res)) {
                    return $this->getInfoResponse('4041', '您今日已兑换过该优惠券，请明日再来兑换');
                }
            }

            return $this->getResponse('success');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

}
