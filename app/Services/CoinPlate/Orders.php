<?php

namespace App\Services\CoinPlate;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\CoinShopGoods;
use App\Entitys\App\CoinShopOrders;
use App\Entitys\App\CoinUser;
use App\Entitys\App\ShopAddress;
use App\Entitys\Article\Agent;

class Orders
{
    //

    /**
     * 订单生成器
     */
    public function generate($zone, $collection, $phone, $detail, $app_id, $number, $custom, $good_id, $type)
    {

        $coin_shop_orders = new CoinShopOrders();
        $coin_shop_goods = new CoinShopGoods();

        $good = $coin_shop_goods->getAllOne($good_id);
        $order_id = 'WHCOINSHOP' . date('YmdHis') . rand(100000, 999999);

        $fare_price = $this->noArea($zone, $good_id, $number);

        $coin_shop_orders->create([
            'order_id' => $order_id,
            'collection' => $collection,
            'phone' => $phone,
            'zone' => $zone,
            'detail' => $detail,
            'app_id' => $app_id,
            'good_id' => $good_id,
            'number' => $number,
            'little_img' => $good->little_img,
            'title' => $good->title,
            'normal_price' => $good->normal_price,
            'custom' => $custom,
            'fare_price' => $fare_price,
            'real_price' => $good->price,
            'coin' => $good->coin,
            'status' => 0,
            'type' => $good->type,
            'pay_type' => $type,
        ]);
        return $order_id;
    }


    /**
     * 运费生成器新版
     * @param $addredd_zone
     * @param $goods_id
     * @param $number
     * @return float|int|mixed
     */
    public function noArea($addredd_zone, $goods_id, $number)
    {
        $shopGoods = new CoinShopGoods();
        $add_express = 0;
        $good = $shopGoods->getAllOne($goods_id);
        $one_express = 0;
        if (empty($good->area)) {
            if (stristr($addredd_zone, '内蒙古') ||
                stristr($addredd_zone, '西藏') ||
                stristr($addredd_zone, '新疆') ||
                stristr($addredd_zone, '青海') ||
                stristr($addredd_zone, '甘肃') ||
                stristr($addredd_zone, '黑龙江')) {
                $good_express = 8;
                if ($good) {
                    if (empty($good->express) || $good->express == 0.00) {
                        $good_express = 0;
                    } else {
                        $good_express = $good->express;
                    }
                }
                $one_express = ((int)($number / 5) + 1) * $good_express;
                $is_have_weight = $good->real_weight * $number;
                if ($good && $is_have_weight) {
                    $real_weight = $is_have_weight - 1000;
                    $one_express = $good_express;
                    if ($real_weight > 0) {
                        $one_express = $one_express + ((int)($real_weight / 500) + 1) * $good_express;
                    }
                }
                $add_express = $add_express + $one_express;
            }
        } else {
            $area = $good->area;
            $address = $addredd_zone;
            $arr_area = explode(',', $area);

            foreach ($arr_area as $one_area) {
                if (stristr($address, $one_area)) {
                    $good_express = 8;
                    if ($good) {
                        if (empty($good->express) || $good->express == 0.00) {
                            $good_express = 0;
                        } else {
                            $good_express = $good->express;
                        }
                    }
                    $one_express = ((int)($number / 5) + 1) * $good_express;
                    $is_have_weight = $good->real_weight * $number;
                    if ($good && $is_have_weight) {
                        $real_weight = $is_have_weight - 1000;
                        $one_express = $good_express;
                        if ($real_weight > 0) {
                            $one_express = $one_express + ((int)($real_weight / 500) + 1) * $good_express;
                        }
                    }
                    $add_express = $add_express + $one_express;
                }
            }

        }

        return $add_express;
    }

    /**
     * 处理订单号
     */
    public function handle($order_id)
    {
        $coin_shop_orders = new CoinShopOrders();
        $coin_shop_goods = new CoinShopGoods();
        $coinUser = new CoinUser();
        $coin_shop_orders->updateOrders($order_id);
        $coin_shop_orders->where(['order_id' => $order_id])->update([
            'put_time' => time()
        ]);
        $order_info = $coin_shop_orders->getOrder($order_id);


        $good_info = $coin_shop_goods->getAllOne($order_info->good_id);
        $get_number = $good_info->get;


        $deduct_coin = ($good_info->coin) * $order_info->number;
        $coin_user = $coinUser->where(['app_id' => $order_info->app_id])->first();

        if ($coin_user->coin < $deduct_coin) {
            $coin_shop_orders->where(['order_id' => $order_id])->update([
                'status' => 9
            ]);
            return 'fail';
        }

        if ($deduct_coin > 0) {
            $coinCommonService = new CoinCommonService($order_info->app_id); // 传app_id
            $coinCommonService->minusCoin(-$deduct_coin, CoinConst::COIN_MINUS_GOODS_DEDUCT, '商城购买金币扣除');
        }

        $coin_shop_orders->where(['order_id' => $order_id])->update([
            'pay_time' => time()
        ]);

        if ($order_info->type == 1) {
            //加大转盘次数
            $coin_turntable = new CoinCommonService($order_info->app_id);
            $coin_turntable->incrementTurntableNum($get_number);
            $coin_shop_orders->where(['order_id' => $order_id])->update([
                'status' => 3
            ]);
        }

        if ($order_info->type == 2) {
            //加头条次数
            $this->handleArticle($order_info->app_id, $get_number);
        }

        //统一
        $coin_shop_goods->addOrDel($order_info->good_id);


        return 'success';
    }


    /**
     * 增加
     * @param $app_id
     * @param $number
     */
    public function handleArticle($app_id, $number)
    {
        $obj_agent = new Agent();
        $obj_ad_info = new AdUserInfo();
        $res = $obj_agent->where('pt_id', $app_id)->first();
        $user_data = $obj_ad_info->where('pt_id', $app_id)->first();
        if ($res) {
            $res->number += $number;
            $res->update_time = time();
            $res->save();
        } else {
            $obj_agent->username = $user_data->pt_username;
            $obj_agent->pt_id = $user_data->pt_id;
            $obj_agent->uid = $user_data->uid;
            $obj_agent->update_time = time();
            $obj_agent->number = $number;
            $obj_agent->forever = 0;
            $obj_agent->save();
        }

    }

}
