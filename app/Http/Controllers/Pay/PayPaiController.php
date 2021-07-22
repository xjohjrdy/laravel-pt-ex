<?php

namespace App\Http\Controllers\Pay;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\MiniWechatInfo;
use App\Entitys\App\ShopAddress;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndex;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersOne;
use App\Entitys\App\TaobaoUser;
use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use App\Extend\Random;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\Common\DingAlerts;
use App\Services\Common\UserMoney;
use App\Services\HeMengTong\HeMeToServices;
use App\Services\Pay\PayPaiService;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\Shop\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yansongda\Pay\Pay;

class PayPaiController extends Controller
{
    /**
     * 小程序商城支付接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appletPay(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'mini_app_id' => 'required',
                'app_id' => 'required',
                'orderAmount' => 'required',
                'ip' => 'required',
                'goodsName' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $orderId = Random::alpha(32);
            $mini_app_id = $arrRequest['mini_app_id'];
            $app_id = $arrRequest['app_id'];
            $orderAmount = $arrRequest['orderAmount'];
            $ip = $arrRequest['ip'];
            $goodsName = $arrRequest['goodsName'];
            $goodsDetail = empty($arrRequest['goodsDetail']) ? '' : $arrRequest['goodsDetail'];
            $wechatModel = new MiniWechatInfo();
            $userWcInfo = $wechatModel->where(['app_id' => $app_id])->first();
            if (empty($userWcInfo)) {
                throw new ApiException('无效的app_id', 4461);
            }
            $openId = $userWcInfo['openid'];
            $payPayService = new PayPaiService();
            $res = $payPayService->pay(
                $orderId,
                $mini_app_id,
                '',
                $openId,
                $orderAmount,
                $ip,
                $goodsName,
                $goodsDetail
            );
            $res = json_decode($res, true);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 支付订单
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param ShopIndex $shopIndex
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function generatePayInfo(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('is_check', $arrRequest) || !array_key_exists('address_id', $arrRequest) || !array_key_exists('order_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['order_id']) {
                $orders = $shopOrders->getById($arrRequest['order_id']);
                if (!$orders) {
                    return $this->getInfoResponse('3005', '订单不存在');
                }
                if ($orders->app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('3006', '这不是您的订单');
                }
                if (strtotime($orders->created_at) + 172800 < time()) {
                    return $this->getInfoResponse('3009', '您的订单已过期！请重新发起');
                }
            }
            if (Cache::has('shop_orders_store_' . $arrRequest['app_id'])) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试...');
            }
            Cache::put('shop_orders_store_' . $arrRequest['app_id'], 1, 0.5);

            if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028])) {
                return $this->getInfoResponse('3003', '内测期间，商城暂未开放，感谢您的耐心等待！');
            }

            $type = 2;
            $address_id = 0;
            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);

            if ($arrRequest['address_id']) {
                $address = $shopAddress->getOneAddress($arrRequest['address_id']);
                if (!$address) {
                    return $this->getInfoResponse('3003', '地址不存在');
                }
                if ($address->app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('3004', '这不是您的地址');
                }
                $address_id = $arrRequest['address_id'];
            }
            $add_express = $order_model->noArea($address_id, $arrRequest['order_id']);

            $real_price = $orders->price + $add_express;
            if ($arrRequest['is_check']) {
                $shop_orders_one = $shopOrdersOne->getAllGoods($arrRequest['order_id']);
                foreach ($shop_orders_one as $k => $item) {
                    if ($item->good_id == 102) {
                        return $this->getInfoResponse('3009', '该产品仅限使用支付宝支付，请重新支付');
                    }
                }

                $account = $userAccount->getUserAccount($user->uid);
                if ($account->extcredits4 <= 0) {
                    return $this->getInfoResponse('3002', '我的币数量不足');
                }
                $ptb_number = $account->extcredits4;
                $price = $real_price;
                if ($ptb_number >= $price * 10) {
                    $ptb_number = $price * 10;
                    $real_price = 0;
                    $type = 1;
                }
                if ($ptb_number < $price * 10) {
                    $real_price = $price - $ptb_number / 10;
                    $type = 3;
                }
            }
            $res = $shopOrders->updateOrders($arrRequest['order_id'], $real_price, $ptb_number, $address_id, $type);
            $shop_orders_one = $shopOrdersOne->getAllGoods($arrRequest['order_id']);

            Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($arrRequest['order_id'], true));
            foreach ($shop_orders_one as $k => $item) {
                if ($shopIndex->isVipGoods($item->good_id)) {
                    $is_have = $rechargeOrder->getOrdersById($orders->order_id);
                    if (empty($is_have)) {
                        $order_model->installOrder($user->uid, 800, $orders->order_id);
                    }
                }
                $good_special = $shopGoods->getOneById($item->good_id);
                Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($item->good_id, true));
                if (!empty($good_special)) {

                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($good_special, true));
                    if ($good_special->volume <= 0) {
                        return $this->getInfoResponse('4009', '您的商品中存在已售罄商品，请重新选择');
                    }
                    if ($good_special->volume < $item->number) {
                        return $this->getInfoResponse('4015', '您输入的数量，库存已经无法满足了！');
                    }
                    if ($good_special->is_push == 0 && $add_express > 0) {
                        return $this->getInfoResponse('4016', '抱歉该商品偏远地区不发货哦，请继续选购其他好货！');
                    }
                    if ($item->good_id == 1475) {
                        $buy_count = $shopOrdersOne->getOrderOneByAppIdAndGoodId($arrRequest['app_id'], 1475);
                        if ($buy_count) {
                            return $this->getInfoResponse('4016', '抱歉该商品只能购买一次，请继续选购其他好货！');
                        }
                    }
                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($shopIndex->isVipGoods($item->good_id), true));

                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($user->groupid, true));
                    if ($shopIndex->isVipGoods($item->good_id) && $user->groupid >= 23) {
                        return $this->getInfoResponse('4019', '您好，会员商品只能购买一件呢！');
                    }
                }
            }

            Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export('支付', true));
            if ($type == 1) {
                $userAccount->subtractPTBMoney($ptb_number, $user->uid);
                $insert_id = $creditLog->addLog($user->uid, "SHX", ['extcredits4' => -$ptb_number]);
                $extcredits4_change = $account->extcredits4 - $ptb_number;
                $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
                $res_maid = $order_model->processOrder($orders->order_id);
                if ($res_maid) {
                    return $this->getResponse('购买成功！');
                }
                return $this->getResponse('购买失败！请联系客服');
            }

            if ($arrRequest['app_id'] == 1694511) {
                $real_price = 0.01;
            }


            if ($type == 3) {
//                if ($arrRequest['app_id'] != 1694511) return $this->getInfoResponse('4123', '支付宝正在升级中，请先用微信或者我的币支付');
//                $order = [
//                    'out_trade_no' => $orders->order_id,
//                    'total_amount' => $real_price,
//                    'subject' => '我的商城购物 - ' . $real_price . '元',
//                ];
//                $alipay = Pay::alipay($this->config)->app($order);
//                return $alipay;
                $payPayService = new PayPaiService();
//                $real_price = 0.02; // 测试专用
                $res = $payPayService->h5Pay(
                    $arrRequest['app_id'], $orders->order_id, $real_price, $good_special->title, $good_special->detail_desc
                );
                $res = json_decode($res, true);
                return $this->getResponse($res);
            }
            if ($type == 2) {
//                if ($arrRequest['app_id'] != 1694511) return $this->getInfoResponse('4123', '支付宝正在升级中，请先用微信或者我的币支付');
                $payPayService = new PayPaiService();
//                $real_price = 0.02; // 测试专用
                $res = $payPayService->h5Pay(
                    $arrRequest['app_id'], $orders->order_id, $real_price, $good_special->title, $good_special->detail_desc
                );
                $res = json_decode($res, true);
                return $this->getResponse($res);
                //                $order = [
//                    'out_trade_no' => $orders->order_id,
//                    'total_amount' => $real_price,
//                    'subject' => '我的商城购物 - ' . $real_price . '元',
//                ];
//                $alipay = Pay::alipay($this->config)->app($order);
//                return $alipay;
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 支付订单 结合余额支付
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param ShopIndex $shopIndex
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function generatePayInfoV1(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('is_check', $arrRequest) || !array_key_exists('address_id', $arrRequest) || !array_key_exists('order_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['order_id']) {

                $orders = $shopOrders->getById($arrRequest['order_id']);
                if (!$orders) {
                    return $this->getInfoResponse('3005', '订单不存在');
                }
                if (Cache::has('wx_applet_pay_success' . $orders['order_id'])) { // 防止重复订单支付成功更新状态
                    return $this->getInfoResponse('3007', '订单已支付！正在处理中。');
                }
                if ($orders->status == 1) {
                    return $this->getInfoResponse('3007', '订单已支付！正在处理中。');
                }
                if ($orders->app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('3006', '这不是您的订单');
                }
                if (strtotime($orders->created_at) + 172800 < time()) {
                    return $this->getInfoResponse('3009', '您的订单已过期！请重新发起');
                }
            }
            if (Cache::has('wx_shop_orders_store_' . $arrRequest['app_id'])) {
                return $this->getInfoResponse('2005', '您的支付操作过于频繁，请3秒后重试~');
            }
            Cache::put('wx_shop_orders_store_' . $arrRequest['app_id'], 1, 0.05);

            //           if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028])) {
            //               return $this->getInfoResponse('3003', '内测期间，商城暂未开放，感谢您的耐心等待！');
            //          }

            $type = 2;
            $address_id = 0;
            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);

            if ($arrRequest['address_id']) {
                $address = $shopAddress->getOneAddress($arrRequest['address_id']);
                if (!$address) {
                    return $this->getInfoResponse('3003', '地址不存在');
                }
                if ($address->app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('3004', '这不是您的地址');
                }
                $address_id = $arrRequest['address_id'];
            }
            $add_express = $order_model->noArea($address_id, $arrRequest['order_id']);

            $real_price = $orders->price + $add_express;
            $pay_money = 0; //所需支付余额
            if ($arrRequest['is_check']) {
                $shop_orders_one = $shopOrdersOne->getAllGoods($arrRequest['order_id']);
                foreach ($shop_orders_one as $k => $item) {
                    if ($item->good_id == 102) {
                        return $this->getInfoResponse('3009', '该产品仅限使用微信支付，请重新支付');
                    }
                }

                $userModel = new TaobaoUser();
                $account_money = $userModel->getUserMoney($arrRequest['app_id']);
                if ($account_money <= 0) {
                    return $this->getInfoResponse('3002', '余额不足');
                }
                $price = $real_price;
                if ($account_money >= $price) {
                    $real_price = 0;
                    $pay_money = $price;
                    $type = 1;
                } else {
                    $real_price = $price - $account_money;
                    $pay_money = $account_money;
                    $type = 3;
                }
            }
            $res = $shopOrders->updateOrders($arrRequest['order_id'], $real_price, $pay_money * 10, $address_id, $type);
            $shop_orders_one = $shopOrdersOne->getAllGoods($arrRequest['order_id']);

            Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($arrRequest['order_id'], true));
            foreach ($shop_orders_one as $k => $item) {
                if ($shopIndex->isVipGoods($item->good_id)) {
                    $is_have = $rechargeOrder->getOrdersById($orders->order_id);
                    if (empty($is_have)) {
                        $order_model->installOrder($user->uid, 800, $orders->order_id);
                    }
                }
                $good_special = $shopGoods->getOneById($item->good_id);
                Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($item->good_id, true));
                if (!empty($good_special)) {

                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($good_special, true));
                    if ($good_special->volume <= 0) {
                        return $this->getInfoResponse('4009', '您的商品中存在已售罄商品，请重新选择');
                    }
                    if ($good_special->volume < $item->number) {
                        return $this->getInfoResponse('4015', '您输入的数量，库存已经无法满足了！');
                    }
                    if ($good_special->is_push == 0 && $add_express > 0) {
                        return $this->getInfoResponse('4016', '抱歉该商品偏远地区不发货哦，请继续选购其他好货！');
                    }
                    if ($item->good_id == 1475) {
                        $buy_count = $shopOrdersOne->getOrderOneByAppIdAndGoodId($arrRequest['app_id'], 1475);
                        if ($buy_count) {
                            return $this->getInfoResponse('4016', '抱歉该商品只能购买一次，请继续选购其他好货！');
                        }
                    }
                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($shopIndex->isVipGoods($item->good_id), true));

                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export(@$user->groupid, true));
                    if ($shopIndex->isVipGoods($item->good_id) && @$user->groupid >= 23) {
                        return $this->getInfoResponse('4019', '您好，会员商品只能购买一件呢！');
                    }
                }
            }

            Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export('支付', true));
            if ($type == 1) {
                $userMoneyService = new UserMoney();
                $userMoneyService->minusCnyAndLog($arrRequest['app_id'], $pay_money, '20001', 'all');
                $res_maid = $order_model->processOrderV1($orders->order_id, 1);
                if ($res_maid) {
                    Cache::put('wx_applet_pay_success' . $orders->order_id, 1, 10);
                    return $this->getResponse('购买成功！');
                }
                return $this->getResponse('购买失败！请联系客服');
            }

            if ($arrRequest['app_id'] == 1694511) {
                $real_price = 0.01;
            }
//            $detail_desc = empty($good_special->detail_desc) ? $good_special->id : $good_special->detail_desc;
            if ($type == 3) {
                $payPayService = new PayPaiService();
                $res = $payPayService->h5Pay(
                    $arrRequest['app_id'], $orders->order_id, $real_price, $good_special->id, '订单号:' . $orders->order_id
                );
                $res = json_decode($res, true);
                return $this->getResponse($res);
            }
            if ($type == 2) {
                $payPayService = new PayPaiService();
                $res = $payPayService->h5Pay(
                    $arrRequest['app_id'], $orders->order_id, $real_price, $good_special->id, '订单号:' . $orders->order_id
                );
                $res = json_decode($res, true);
                return $this->getResponse($res);
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 禾盟通 支付订单 结合余额支付
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param ShopIndex $shopIndex
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function generatePayInfoV2(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {
        return $this->getInfoResponse('3003', '系统升级期间暂停付款，请耐心等待！！');
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('is_check', $arrRequest) || !array_key_exists('address_id', $arrRequest) || !array_key_exists('order_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['order_id']) {

                $orders = $shopOrders->getById($arrRequest['order_id']);
                if (!$orders) {
                    return $this->getInfoResponse('3005', '订单不存在');
                }
                if (Cache::has('wx_applet_pay_success' . $orders['order_id'])) { // 防止重复订单支付成功更新状态
                    return $this->getInfoResponse('3007', '订单已支付！正在处理中。');
                }
                if ($orders->status == 1) {
                    return $this->getInfoResponse('3007', '订单已支付！正在处理中。');
                }
                if ($orders->app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('3006', '这不是您的订单');
                }
                if (strtotime($orders->created_at) + 172800 < time()) {
                    return $this->getInfoResponse('3009', '您的订单已过期！请重新发起');
                }
            }
            if (Cache::has('wx_shop_orders_store_' . $arrRequest['app_id'])) {
                return $this->getInfoResponse('2005', '您的支付操作过于频繁，请3秒后重试~');
            }
            Cache::put('wx_shop_orders_store_' . $arrRequest['app_id'], 1, 0.05);

            //           if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028])) {
            //               return $this->getInfoResponse('3003', '内测期间，商城暂未开放，感谢您的耐心等待！');
            //          }

            $type = 2;
            $address_id = 0;
            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);

            if ($arrRequest['address_id']) {
                $address = $shopAddress->getOneAddress($arrRequest['address_id']);
                if (!$address) {
                    return $this->getInfoResponse('3003', '地址不存在');
                }
                if ($address->app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('3004', '这不是您的地址');
                }
                $address_id = $arrRequest['address_id'];
                if (stristr($address->zone, '北京市')) {
                    return $this->getInfoResponse('211', '北京由于疫情原因和快递政策暂不能发货，开放时间另行通知，敬请谅解！');
                }
            }
            $add_express = $order_model->noArea($address_id, $arrRequest['order_id']);

            $real_price = $orders->price + $add_express;
            $pay_money = 0; //所需支付余额

            if ($arrRequest['is_check']) {
                $shop_orders_one = $shopOrdersOne->getAllGoods($arrRequest['order_id']);
                foreach ($shop_orders_one as $k => $item) {
                    if ($item->good_id == 102) {
                        return $this->getInfoResponse('3009', '该产品仅限使用微信支付，请重新支付');
                    }
                }

                $userModel = new TaobaoUser();
                $account_money = $userModel->getUserMoney($arrRequest['app_id']);
                if ($account_money <= 0) {
                    return $this->getInfoResponse('3002', '余额不足');
                }
                $price = $real_price;
                if ($account_money >= $price) {
                    $real_price = 0;
                    $pay_money = $price;
                    $type = 1;
                } else {
                    $real_price = $price - $account_money;
                    $pay_money = $account_money;
                    $type = 3;
                }
            }
            $res = $shopOrders->updateOrders($arrRequest['order_id'], $real_price, $pay_money * 10, $address_id, $type);
            $shop_orders_one = $shopOrdersOne->getAllGoods($arrRequest['order_id']);

            Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($arrRequest['order_id'], true));
            foreach ($shop_orders_one as $k => $item) {
                if ($shopIndex->isVipGoods($item->good_id)) {
                    $is_have = $rechargeOrder->getOrdersById($orders->order_id);
                    if (empty($is_have)) {
                        $order_model->installOrder($user->uid, 800, $orders->order_id);
                    }
                }
                $good_special = $shopGoods->getOneById($item->good_id);
                Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($item->good_id, true));
                if (!empty($good_special)) {

                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($good_special, true));
                    if ($good_special->volume <= 0) {
                        return $this->getInfoResponse('4009', '您的商品中存在已售罄商品，请重新选择');
                    }
                    if ($good_special->volume < $item->number) {
                        return $this->getInfoResponse('4015', '您输入的数量，库存已经无法满足了！');
                    }
                    if ($good_special->is_push == 0 && $add_express > 0) {
                        return $this->getInfoResponse('4016', '抱歉该商品偏远地区不发货哦，请继续选购其他好货！');
                    }
                    if ($item->good_id == 1475) {
                        $buy_count = $shopOrdersOne->getOrderOneByAppIdAndGoodId($arrRequest['app_id'], 1475);
                        if ($buy_count) {
                            return $this->getInfoResponse('4016', '抱歉该商品只能购买一次，请继续选购其他好货！');
                        }
                    }
                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($shopIndex->isVipGoods($item->good_id), true));

                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export(@$user->groupid, true));
                    if ($shopIndex->isVipGoods($item->good_id) && @$user->groupid >= 23) {
                        return $this->getInfoResponse('4019', '您好，会员商品只能购买一件呢！');
                    }
                }
            }

            Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export('支付', true));
            if ($type == 1) {
                $userMoneyService = new UserMoney();
                $userMoneyService->minusCnyAndLog($arrRequest['app_id'], $pay_money, '20001', 'all');
                $res_maid = $order_model->processOrderV1($orders->order_id, 1);

                //新增余额支付时间
                $shopOrders->where('order_id', $orders->order_id)->update(['pay_time' => time()]);

                if ($res_maid) {
                    //爆款支付成功完成任务
                    if (!config('test_appid.debug') || in_array($arrRequest['app_id'], config('test_appid.app_ids'))) {
                        try {
                            $coinCommonService = new CoinCommonService($arrRequest['app_id']);
                            $task_id = 4;#爆款商城首购
                            $task_time = time();
                            $coinCommonService->successTask($task_id, $task_time);

                            //邀请的新人完成爆款首购完成任务
                            //得到上级
                            $appUserInfo = new AppUserInfo();
                            $user_info = $appUserInfo->where('id', $arrRequest['app_id'])->first(['parent_id', 'create_time']);
                            $new_user_time = strtotime('2020-07-01');//定义新人注册的时间
                            if ($user_info && $user_info->create_time >= $new_user_time) {//有上级 且为新人 且为首购 上级可完成任务
                                $coinCommonService = new CoinCommonService($user_info->parent_id);
                                $task_id = 9;#邀好友完成爆款首购
                                $task_time = time();
                                $coinCommonService->successTask($task_id, $task_time);
                            }
                        } catch (\Exception $e) {

                        }
                    }

                    Cache::put('wx_applet_pay_success' . $orders->order_id, 1, 10);
                    return $this->getResponse('购买成功！');
                }
                return $this->getResponse('购买失败！请联系客服');
            }

            if ($arrRequest['app_id'] == 1694511) {
                $real_price = 0.01;
            }
//            $detail_desc = empty($good_special->detail_desc) ? $good_special->id : $good_special->detail_desc;
            if ($type == 3) {
//                $payPayService = new PayPaiService();
//                $res = $payPayService->h5Pay(
//                    $arrRequest['app_id'], $orders->order_id, $real_price, $good_special->id, '订单号:' . $orders->order_id
//                );
                //判断是传入openid
                if (empty($arrRequest['open_id'])) {
                    //根据app_id取openid
                    $miniWechatInfo = new MiniWechatInfo();
                    $fopenid = $miniWechatInfo->where('app_id', $arrRequest['app_id'])->value('openid');
                } else {
                    $fopenid = $arrRequest['open_id'];
                }

                if (empty($fopenid)) {
                    return $this->getResponse('购买失败！请联系客服');
                }


                //存在订单id读取缓存
                if (Cache::has('wx_is_pay_' . $orders->order_id . $real_price)) {
                    $res = Cache::get('wx_is_pay_' . $orders->order_id . $real_price);
                    return $this->getResponse($res);
                }


                $heMeToServices = new HeMeToServices();
                $res = $heMeToServices->wxAppPay($real_price, $fopenid, $orders->order_id);
                $res = json_decode($res, true);
                if (@$res["fcode"] != 10000) {
                    if (!Cache::has('dding' . @$res["fcode"])) {
                        $dingAlerts = new DingAlerts();
                        $dingAlerts->sendByText('小程序下单失败:' . @$res['fmsg']);
                        Cache::put('dding' . @$res["fcode"], 1, 20);
                    }

                    return $this->getInfoResponse('1001', @$res['fmsg']);//错误返回数据
                }

                //下单成功存入缓存
                Cache::put('wx_is_pay_' . $orders->order_id . $real_price, $res, 10);

                return $this->getResponse($res);
            }
            if ($type == 2) {
//                $payPayService = new PayPaiService();
//                $res = $payPayService->h5Pay(
//                    $arrRequest['app_id'], $orders->order_id, $real_price, $good_special->id, '订单号:' . $orders->order_id
//                );
                //判断是传入openid
                if (empty($arrRequest['open_id'])) {
                    //根据app_id取openid
                    $miniWechatInfo = new MiniWechatInfo();
                    $fopenid = $miniWechatInfo->where('app_id', $arrRequest['app_id'])->value('openid');
                } else {
                    $fopenid = $arrRequest['open_id'];
                }

                if (empty($fopenid)) {
                    return $this->getResponse('购买失败！请联系客服');
                }

                //存在订单id读取缓存
                if (Cache::has('wx_is_pay_' . $orders->order_id . $real_price)) {
                    $res = Cache::get('wx_is_pay_' . $orders->order_id . $real_price);
                    return $this->getResponse($res);
                }

                $heMeToServices = new HeMeToServices();
                $res = $heMeToServices->wxAppPay($real_price, $fopenid, $orders->order_id);
                $res = json_decode($res, true);
                if (@$res["fcode"] != 10000) {
                    if (!Cache::has('dding' . @$res["fcode"])) {
                        $dingAlerts = new DingAlerts();
                        $dingAlerts->sendByText('小程序下单失败:' . @$res['fmsg']);
                        Cache::put('dding' . @$res["fcode"], 1, 20);
                    }

                    return $this->getInfoResponse('1001', @$res['fmsg']);//错误返回数据
                }

                //下单成功存入缓存
                Cache::put('wx_is_pay_' . $orders->order_id . $real_price, $res, 10);

                return $this->getResponse($res);
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * h5商城支付接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function h5Pay(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'orderAmount' => 'required',
                'goodsName' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $orderId = Random::alpha(32);
            $app_id = $arrRequest['app_id'];
            $orderAmount = $arrRequest['orderAmount'];
            $goodsName = $arrRequest['goodsName'];
            $goodsDetail = empty($arrRequest['goodsDetail']) ? '' : $arrRequest['goodsDetail'];
            $payPayService = new PayPaiService();
//            $orderAmount = 0.02; // 用于测试
            $res = $payPayService->h5Pay(
                $app_id, $orderId, $orderAmount, $goodsName, $goodsDetail
            );
            $res = json_decode($res, true);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 小程序商城批量退款接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refund2List(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $desc = empty($arrRequest['desc']) ? '心选购小程序商城购物退款' : $arrRequest['desc'];


            $payPayService = new PayPaiService();
            $order_ids = [
                ['WXAPPLET20200314102621v8Kvt', 186.90, '25686'],
//                ['WXAPPLET20200321142428b1YRV',9.63, 'WXAPPLET20200321142428b1YRV'],
//                ['WXAPPLET202003052320104ieKp',25.57,25254], 1
//                ['WXAPPLET202003061518341VibR',46.90,25275], 1
//                ['WXAPPLET202003071020493nPBy',9.98,25312], 1
//                ['WXAPPLET202003070601245kCn2',139.90,25381], 1
//                ['WXAPPLET20200309075414epQwE',82.80,25394], 1
//                ['WXAPPLET202003090752499NJ4C',88.00,25395], 1
//                ['WXAPPLET20200309140801aPEG3',79.00,25396], 1
//                ['WXAPPLET202003051006581Ofjc',38.40,25456], 1
//                ['WXAPPLET20200311112840agsIZ',46.32,25511], 1
//                ['WXAPPLET202003111552497pinW',79.00,25520], 1
//                ['WXAPPLET20200307110706CyaFV',29.98,25523], 1
//                ['WXAPPLET20200307110846n7uCo',29.98,25524], 1
//                ['WXAPPLET2020030711095722Uri',29.98,25525], 1
//                ['WXAPPLET20200306141804cPNE6',29.98,25526], 1
//                ['WXAPPLET20200304210102l35qU',28.80,25537], 1
//                ['WXAPPLET20200307161656kqvdJ',49.60,25540], 1
//                ['WXAPPLET20200308150302qIvux',22.16,25615], 1
//                ['WXAPPLET20200309083749zXX2A',75.20,25620], 1
//                ['WXAPPLET20200313115244nHg8m',18.33,25629], 1
//                ['WXAPPLET20200312223927uGDMX',79.00,25632], 1
//                ['WXAPPLET20200313131904sTqmZ',219.00,25641], 1
//                ['WXAPPLET20200307110056thX4X',55.90,25643], 1
//                ['WXAPPLET20200310135340zeumR',31.65,25647], 1
//                ['WXAPPLET20200313095844qEDx5',215.00,25653], 1
//                ['WXAPPLET20200309165531k4FRo',37.54,25659], 1
//                ['WXAPPLET20200312151519VNvXi',197.00,25662],
//                ['WXAPPLET20200310191636R6CNN',75.20,25668], 1
//                ['WXAPPLET20200312170417XNu2G',27.02,25672], 1
//                ['WXAPPLET20200309071819FXMJ3',71.17,25673], 1
//                ['WXAPPLET20200305191519VafbX',66.50,25679], 1
//                ['WXAPPLET20200311085732om5gQ',216.00,25701], 1
//                ['WXAPPLET20200309110912Tn1pQ',23.80,25705], 1
//                ['WXAPPLET202003080812169P4bb',41.01,25742], 1
//                ['WXAPPLET20200308075423fkNLt',57.80,25747], 1
//                ['WXAPPLET20200310065410xCdj2',67.45,25756], 1
//                ['WXAPPLET20200310103715RENAi',75.20,25758], 1
//                ['WXAPPLET20200310103454NX6ZK',75.20,25759], 1
//                ['WXAPPLET20200310103120IBDnW',75.20,25760], 1
//                ['WXAPPLET20200305155312JJkdH',28.80,25763], 1
//                ['WXAPPLET202003061151538uTpo',26.83,25768], 1
//                ['WXAPPLET20200309163258l9j3v',59.90,25782], 1
//                ['WXAPPLET20200306172930lmlPo',9.80,25799], 1
//                ['WXAPPLET20200312001937N4VSB',22.16,25814], 1
//                ['WXAPPLET20200308203222MsDPN',31.77,25826], 1
//                ['WXAPPLET20200306223427pWBWj',19.30,25829], 1
//                ['WXAPPLET20200310092529vGp0t',47.28,25831], 1
//                ['WXAPPLET20200309181839vvjjK',78.00,25832], 1
//                ['WXAPPLET20200307210806kbmLy',145.00,25834], 1
//                ['WXAPPLET20200305081239oHNJs',161.40,25836], 1
//                ['WXAPPLET202003091510576yzvj',117.60,25840], 1
//                ['WXAPPLET20200306214254Z4sTe',29.90,25843], 1
//                ['WXAPPLET20200310133510C5x6z',86.85,25851], 1
//                ['WXAPPLET20200310143210IGN91',75.20,25860], 1
//                ['WXAPPLET20200312222637nzSjx',62.59,25866], 1
//                ['WXAPPLET20200308124040ekLLC',39.47,25871], 1
//                ['WXAPPLET20200306122158AsNSG',9.66,25872], 1
//                ['WXAPPLET20200309223757cex0t',75.20,25895], 1
//                ['WXAPPLET20200305093113XZ6f2',67.07,25896], 1
//                ['WXAPPLET20200305074212DtMTw',97.60,25905], 1
//                ['WXAPPLET20200305081357e24FB',97.60,25907], 1
//                ['WXAPPLET20200307163144UnGTF',28.80,25930], 1
//                ['WXAPPLET202003101408433jsjw',28.32,25954], 1
//                ['WXAPPLET20200308173705HmoEG',24.50,25965],  1
//                ['WXAPPLET20200307141741ijYLO',28.80,25989], 1
//                ['WXAPPLET20200308120605aKfc1',75.20,26011],1
//                ['WXAPPLET20200311211352bngg0',67.07,26019], 1
            ];
            // 泰隆
//            $order_ids = [
//                ['WXAPPLET20200313180117k5mS2',28.85,25639],
//                ['WXAPPLET202003132053110P8Hg',64.56,25645],
//                ['WXAPPLET20200313232850NmMrB',27.02,25649],
//                ['WXAPPLET20200314100504DAMFX',46.39,25658],
//                ['WXAPPLET20200314115410Gl7Hd',53.07,25665],
//                ['WXAPPLET20200314103928Y6x4Y',34.26,25693],
//                ['WXAPPLET20200314102146ssqOt',34.26,25694],
//                ['WXAPPLET20200314101322X4fuW',46.32,25695],
//                ['WXAPPLET20200314113039k98z0',39.99,25698],
//            ];
            foreach ($order_ids as $item) {
                $order_id = $item[0];
                $amount = $item[1];
                $refund_id = $item[2];
                $res = $payPayService->refund(
                    $refund_id,
                    $order_id,
                    $amount,
                    $desc,
                    0
                ); //1对公 2 对私 0 泰隆
                $res = json_decode($res, true);
                if ($res['rt2_retCode'] == '0001') {
//                return $this->getResponse('退款成功');
                    $this->refundLog2($res['rt2_retCode'] . '----' . $res['rt3_retMsg'] . '- order_id' . $res['rt5_orderId']);
                } else {
                    $this->refundLog2($res['rt2_retCode'] . '----' . $res['rt3_retMsg'] . '- order_id' . $res['rt5_orderId']);
                }
            }

            return $this->getResponse('');
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            $this->refundLog2($e->getMessage());
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 小程序商城退款接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refund(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
//                'type' => 'required', // 1 支付宝  2 微信
                'order_id' => 'required',
                'refund_id' => 'required',
                'amount' => 'required',
                'sign' => 'required', //简单签名
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $desc = empty($arrRequest['desc']) ? '心选购小程序商城购物退款' : $arrRequest['desc'];

//            $type = $arrRequest['type'];
            $refund_id = $arrRequest['refund_id'];
            $order_id = $arrRequest['order_id'];
            $amount = $arrRequest['amount'];
            $you_sign = $arrRequest['sign'];
            unset($arrRequest['sign']);

            $my_sign = md5(implode("/*1pt23*/", $arrRequest));

//            if ($my_sign != $you_sign) {
//                return $this->getInfoResponse('1001', '签名错误已记录IP！' . $request->ip());
//            }

            $payPayService = new PayPaiService();
            $res = $payPayService->refund(
                $refund_id,
                $order_id,
                $amount,
                $desc,
                2
            ); // 对私
            $res = json_decode($res, true);
            if ($res['rt2_retCode'] == '0001') {
                return $this->getResponse('退款成功');
            } else {
                $res = $payPayService->refund(
                    $refund_id,
                    $order_id,
                    $amount,
                    $desc,
                    1
                ); // 对公
                $res = json_decode($res, true);
                if ($res['rt2_retCode'] == '0001') {
                    return $this->getResponse('退款成功');
                } else {
                    $res = $payPayService->refund(
                        $refund_id,
                        $order_id,
                        $amount,
                        $desc, 0
                    ); // 其他
                    $res = json_decode($res, true);
                    if ($res['rt2_retCode'] == '0001') {
                        return $this->getResponse('退款成功');
                    }
                }
            }
            $this->refundLog($res['rt2_retCode'] . '----' . $res['rt3_retMsg'] . '- order_id' . $res['rt5_orderId'] . '- refund_id:' . $res['rt6_refundOrderNum']);
            return $this->getInfoResponse('1000', $res['rt2_retCode'] . '----' . $res['rt3_retMsg']);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            $this->refundLog($e->getMessage());
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * h5商城支付回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refundCallback(Request $request)
    {
        $params = Input::all();
        try {
            $paypaiServicve = new PayPaiService();
            $paypaiServicve->refundCallBack($params);
            return "success";
        } catch (\Throwable $e) {
            $this->refundLog($e->getMessage());
            return "success";
        }
    }

    /**
     * h5商城支付回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function h5PayCallBack(Request $request, RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, ShopOrders $shopOrders, Order $order_model)
    {
        $params = Input::all();
        try {
            $paypaiServicve = new PayPaiService();
            if ($paypaiServicve->callValidate($params)) {
                if ($params['rt4_status'] <> "SUCCESS") {
                    $this->payLog($params['rt2_orderId'] . '---' . $params['rt4_status'] . '----' . $params['rt8_desc']);
                } else {
                    //拿到订单
                    $this->payLog('验证成功开始执行！ :' . $params['rt2_orderId']);
                    if (Cache::has('wx_applet_pay_success' . $params['rt2_orderId'])) { // 防止重复订单支付成功更新状态
                        return 'success';
                    }
                    Cache::put('wx_applet_pay_success' . $params['rt2_orderId'], 1, 10);
                    //  // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
                    $out_trade_no = $params['rt2_orderId'];
                    $order = $rechargeOrder->getOrdersById($out_trade_no);
                    // 第二种订单情况，如果存在则进入商品回调
                    $shop_order = $shopOrders->getByOrderId($out_trade_no);
                    if (!empty($shop_order)) {
                        if ($shop_order->app_id == 1569840) {
                            $shop_order->real_price = 0.01;
                        }
                        $computer_price = $shop_order->real_price * 100;
//                if ($data->total_fee == $computer_price) {
//                file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);
//                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);
                        $this->payLog('处理订单开始！');
                        $res_maid = $order_model->processOrderV1($shop_order->order_id, 1);
                        $this->payLog('处理订单结束！');
//                }
                    }

                    if (!empty($order)) {
                        if ($order->uid == 1499531) {
                            $order->price = 0.01;
                        }
//                if (($order->price * 100) <> $data->total_fee) {
//                    file_put_contents('wechat_pay_notify_shop.txt', '金额不对等' . PHP_EOL, FILE_APPEND);
//                    file_put_contents('wechat_pay_notify_shop.txt', $data->total_fee . PHP_EOL, FILE_APPEND);
//                    file_put_contents('wechat_pay_notify_shop.txt', "订单金额：" . $order->price . PHP_EOL, FILE_APPEND);
//                    exit();
//                }
//                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);
                        // 5、其它业务逻辑情况
                        $arr = [
                            'uid' => $order->uid,
                            'money' => $order->price,
                            'orderid' => $out_trade_no,
                        ];
                        if ($shop_order) {
                            $arr = [
                                'uid' => $order->uid,
                                'money' => 800,
                                'orderid' => $out_trade_no,
                            ];
                        }
//                $AdUserInfo = new AdUserInfo();
//                $x = $AdUserInfo->getUserById($order->uid);
//                if ($x->groupid <= 22) {
                        $rechargeUserLevel->initOrder($arr);
                        $rechargeUserLevel->updateExt(); //升级
                        $rechargeUserLevel->returnCommission(); //返佣
                        $rechargeUserLevel->handleArticle(); //更新文章
                        $rechargeOrder->updateOrderStatus($out_trade_no);//更新订单
//                }
                    }
                }
            }
            return "success";
        } catch (\Throwable $e) {
            $this->payLog($e->getMessage());
            return "success";
        }
    }


    /**
     * 支付记录日志
     */
    private function payLog($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/PayPai/pay/' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }


    /**
     * 小程序商城支付回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function miniPayCallBack(Request $request)
    {
        $params = Input::all();
        $payPayService = new PayPaiService();
        $res = $payPayService->payCallBack($params);
        return $res;
    }

    /**
     * 小程序商城用户退款回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function miniRefundCallback(Request $request)
    {
        $params = Input::all();
        $payPayService = new PayPaiService();
        $res = $payPayService->payCallBack($params);
        return $res;
    }

    /**
     * 记录日志
     */
    private function refundLog($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/PayPai/refund/' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }

    /**
     * 记录日志
     */
    private function refundLog2($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/PayPai/refund2/' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }
}
