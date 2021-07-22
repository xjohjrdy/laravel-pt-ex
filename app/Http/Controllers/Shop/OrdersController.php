<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\GrowthUserValue;
use App\Entitys\App\GrowthUserValueChange;
use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\PretendShopOrdersMaid;
use App\Entitys\App\ShopAddress;
use App\Entitys\App\ShopCarts;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndex;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersOne;
use App\Entitys\App\ShopVipBuy;
use App\Entitys\App\TaobaoUser;
use App\Exceptions\ApiException;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\Common\UserMoney;
use App\Services\HeMengTong\HeMeToServices;
use App\Services\Other\ShopCommissionService;
use App\Services\Recharge\PurchaseUserGroup;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\Shop\Order;
use App\Services\UpgradeVip\ChangeVipService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yansongda\Pay\Pay;

class OrdersController extends Controller
{
    /**
     * 拉出你想要的订单列表
     * get {"app_id":"1","status":""}
     * @param Request $request
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne, ShopGoods $shopGoods)
    {
//        try {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
        $arrRequest = json_decode($request->data, true);
        if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('status', $arrRequest)) {
            throw new ApiException('传入参数错误', '3001');
        }
//        if (!$arrRequest['status']) {
//            $arrRequest['status'] = 99;
//        }
        $res = $shopOrders->getAllUserOrders($arrRequest['app_id'], $arrRequest['status']);
        if ($res) {
            foreach ($res as $k => $v) {
                $all_number = 0;
                $all_price = 0;
                $goods = $shopOrdersOne->getAllGoods($v->id);
                if ($goods) {
                    foreach ($goods as $key => $value) {
                        $goods[$key]->good_content = $shopGoods->getOneById($value['good_id'], 0);
                        if ($goods[$key]->good_content) {
                            $goods[$key]->good_content->header_img = json_decode($goods[$key]->good_content->header_img);
                        }
                        $all_price = $all_price + $goods[$key]->real_price * $goods[$key]->number;
                        $all_number = $all_number + $goods[$key]->number;
//                        $is_refund = $this->getIsRefund($arrRequest['app_id'], $value['good_id']);

                        $good_id = $value['good_id'];
                        $app_id = $arrRequest['app_id'];
                        $res_one = $shopGoods->getOneById($value['good_id'], 0);
                        $adUserInfo = new AdUserInfo();
                        $user = $adUserInfo->appToAdUserId($app_id);
                        //获取成长值比例 计算次月最大送的成长值
                        $obj_growth_user_value_Config = new GrowthUserValueConfig();
                        $num_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');

                        if (!empty($user->groupid)) {
                            if ($user->groupid < 23) {
                                $res_one->profit_value = number_format($res_one->profit_value * 0.41 * 0.3, 2);
                            } else {
                                $res_one->profit_value = number_format($res_one->profit_value * 0.41 * 0.6, 2);
                            }
                        } else {
                            $res_one->profit_value = number_format($res_one->profit_value * 0.41 * 0.3, 2);
                        }

                        //判断是不是vip商品
                        $obj_shop_vip_buy = new ShopVipBuy();
                        $is_vip_shop = $obj_shop_vip_buy->where('vip_id', $good_id)->first();
                        if (empty($is_vip_shop)) {
                            $res_one->is_vip_goods = 0;
                            $is_refund = round($res_one->profit_value / $num_growth_value, 2);
                        } else {
                            $res_one->is_vip_goods = 1;
                            $is_refund = $is_vip_shop->can_active;
                        }

                        if ($is_refund >= 20) {
                            $is_refund = 0;
                        } else {
                            $is_refund = 1;
                        }
                        $goods[$key]->is_refund = $is_refund;
                    }
                    $res[$k]['orders_one'] = $goods;
                } else {
                    $res[$k]['orders_one'] = [];
                }
                $res[$k]->all_price = $all_price;
                $res[$k]->all_number = $all_number;
            }
        }

        return $this->getResponse($res);
//        } catch (\Exception $e) {
//            if (!empty($e->getCode())) {
//                throw new ApiException($e->getMessage(), $e->getCode());
//            }
//            throw new ApiException('网络开小差了！请稍后再试', '500');
//        }
    }

    /**
     * 此处仅作为初始生成参考，实际付款订单用户肯定会做出修改
     * Show the form for creating a new resource.
     * 1、从购物车里进来的订单创建 get {"app_id":"1","carts_id":["1","2","3"]}
     * 2、从商品页面进来的订单创建 get {"app_id":"1","good_id":"","shop_id":"","desc":"","number":""}
     * @param Request $request
     * @param Order $order
     * @param ShopCarts $shopCarts
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     *
     */
    public function create(Request $request, Order $order)
    {

        try {
            DB::beginTransaction();
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['number'] > 1) {
                return $this->getInfoResponse('1001', '只允许购买单个商品!');
            }
            $pattern = '/^[\x{00}-\x{ff}\x{4e00}-\x{9fa5}\x{3010}\x{3011}\x{ff08}\x{ff09}\x{201c}\x{201d}\x{2018}\x{2019}\x{ff0c}\x{ff01}\x{ff0b}\x{3002}\x{ff1f}\x{3001}\x{ff1b}\x{ff1a}\x{300a}\x{300b}]+$/u';
            if (!empty($arrRequest['desc'])) {
                if (!preg_match($pattern, $arrRequest['desc'])) {

                    DB::rollBack();
                    return $this->getInfoResponse('3003', '留言请不要输入特殊符号哦');
                }
            }

//            if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028, 1694511])) {
//                return $this->getInfoResponse('3003', '内测期间，商城暂未开放，感谢您的耐心等待！');
//            }

            //拦截版本
            $request_device = $request->header('Accept-Device'); //设备类型
            $request_appversion = $request->header('Accept-Appversion'); //版本号
//            if ($request_device != 'android' || $request_appversion < 198) {
            if (($request_device == 'android' && $request_appversion < 198) || ($request_device == 'ios' && version_compare($request_appversion, '4.6.5', '<'))) {
                return $this->getInfoResponse('3003', '系统升级期间暂停付款，请耐心等待！！');
            }

            $res = [];
            if ($request->type == 1) {
                DB::rollBack();
                return $this->getInfoResponse('3003', '暂时不支持购物车购买！');
                if (!$arrRequest || !array_key_exists('carts_id', $arrRequest)) {
                    throw new ApiException('传入参数错误', '3001');
                }
                foreach ($arrRequest['carts_id'] as $k => $v) {
                    $shop_cart = $shopCarts->getOneById($v);
                    if (!$shop_cart) {
                        break;
                    }
                    if ($shop_cart->app_id <> $arrRequest['app_id']) {
                        continue;
                    }
                    $res[] = $shop_cart->toArray();
                }

                $res = $order->generateOrder($arrRequest['app_id'], $res);
            }

            if ($request->type == 2) {
                if (!$arrRequest || !array_key_exists('good_id', $arrRequest) || !array_key_exists('shop_id', $arrRequest) || !array_key_exists('desc', $arrRequest) || !array_key_exists('number', $arrRequest)) {
                    throw new ApiException('传入参数错误', '3001');
                }
                if ($arrRequest['good_id'] == 1780) {
                    $AdUserInfo = new AdUserInfo();
                    $app_user = $AdUserInfo->appToAdUserId($arrRequest['app_id']);
                    if (empty($app_user) || $app_user->groupid < 23) {
                        return $this->getInfoResponse('4005', '暂无权限，该商品需要超级用户权限购买！');
                    }
                }
                if ($arrRequest['good_id'] == 2624) {
                    $AdUserInfo = new AdUserInfo();
                    $app_user = $AdUserInfo->appToAdUserId($arrRequest['app_id']);
                    if (empty($app_user) || $app_user->groupid < 23) {
                        return $this->getInfoResponse('4005', '暂无权限，该商品需要超级用户权限购买！');
                    }
                }
                if ($arrRequest['good_id'] == 1475) {
                    $shopOrdersOne = new ShopOrdersOne();
                    $buy_count = $shopOrdersOne->getOrderOneByAppIdAndGoodId($arrRequest['app_id'], 1475);
                    if ($buy_count) {
                        return $this->getInfoResponse('4016', '抱歉该商品只能购买一次，请继续选购其他好货！');
                    }
                }
                $res = $order->generateOrder($arrRequest['app_id'], [0 => ['app_id' => $arrRequest['app_id'], 'good_id' => $arrRequest['good_id'], 'shop_id' => $arrRequest['shop_id'], 'desc' => $arrRequest['desc'], 'number' => $arrRequest['number']]]);
            }

            DB::commit();

            return $this->getResponse($res);
        } catch (\Exception $e) {
            DB::rollBack();

            Storage::disk('local')->append('callback_document/test_error.txt', var_export('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage(), true));
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }

    }

    /**
     * 用户付款
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Yansongda\Pay\Gateways\Alipay\AppGateway
     * @throws ApiException
     */
    public function store(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {

        try {
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

            $ptb_number = 0;
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
                if ($arrRequest['app_id'] != 1694511) return $this->getInfoResponse('4123', '支付宝正在升级中，请先用微信或者余额支付');
                $order = [
                    'out_trade_no' => $orders->order_id,
                    'total_amount' => $real_price,
                    'subject' => '我的商城购物 - ' . $real_price . '元',
                ];
                $alipay = Pay::alipay($this->config)->app($order);
                return $alipay;
            }
            if ($type == 2) {
                if ($arrRequest['app_id'] != 1694511) return $this->getInfoResponse('4123', '支付宝正在升级中，请先用微信或者余额支付');
                $order = [
                    'out_trade_no' => $orders->order_id,
                    'total_amount' => $real_price,
                    'subject' => '我的商城购物 - ' . $real_price . '元',
                ];
                $alipay = Pay::alipay($this->config)->app($order);
                return $alipay;
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 微信付款
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Yansongda\Pay\Gateways\Alipay\AppGateway
     * @throws ApiException
     */
    public function wechatPay(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {

//        return $this->getInfoResponse('3003', '系统升级期间爆款商城暂停付款，预计1月1号升级完成，敬请期待！');
        try {
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
            }

            if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028])) {
                return $this->getInfoResponse('3003', '系内测期间，商城暂未开放，感谢您的耐心等待！');
            }

            $ptb_number = 0;
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
            foreach ($shop_orders_one as $k => $item) {
                if ($shopIndex->isVipGoods($item->good_id)) {
                    $is_have = $rechargeOrder->getOrdersById($orders->order_id);
                    if ($is_have) {
                        break;
                    }
                    $order_model->installOrder($user->uid, 800, $orders->order_id);
                }
                $good_special = $shopGoods->getOneById($item->good_id);
                if ($good_special) {
                    if ($good_special->volume <= 0) {
                        return $this->getInfoResponse('4009', '您的商品中存在已售罄商品，请重新选择');
                    }
                    if ($good_special->volume < $item->number) {
                        return $this->getInfoResponse('4015', '您输入的数量，库存已经无法满足了！');
                    }
                    if ($good_special->is_push == 0 && $add_express > 0) {
                        return $this->getInfoResponse('4016', '抱歉该商品偏远地区不发货哦，请继续选购其他好货！');
                    }
                }
            }
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

            if ($type == 3) {
                if ($arrRequest['app_id'] == 1569840) {
                    $real_price = 0.01;
                }
                if ($arrRequest['app_id'] == 1694511) {
                    $real_price = 0.01;
                }
                $order = [
                    'out_trade_no' => $orders->order_id,
                    'total_fee' => ($real_price * 100),
                    'body' => '我的商城购物 - ' . $real_price . '元',
                ];
                $this->wechat_config['notify_url'] = 'http://api.36qq.com/api/shop_wechat_pay_now_wuhang';
                $pay = Pay::wechat($this->wechat_config)->app($order);
                return $pay;
            }
            if ($type == 2) {
                if (in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028])) {
                    $real_price = 0.01;
                }
                $order = [
                    'out_trade_no' => $orders->order_id,
                    'total_fee' => ($real_price * 100),
                    'body' => '我的商城购物 - ' . $real_price . '元',
                ];
                $this->wechat_config['notify_url'] = 'http://api.36qq.com/api/shop_wechat_pay_now_wuhang';
                $pay = Pay::wechat($this->wechat_config)->app($order);
                return $pay;
            }

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了哦！', '500');
        }
    }

    /**
     * 用户付款 余额支付
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Yansongda\Pay\Gateways\Alipay\AppGateway
     * @throws ApiException
     */
    public function storeV1(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {

        try {
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

            if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028, 1694511])) {
                return $this->getInfoResponse('3003', '内测期间，商城暂未开放，感谢您的耐心等待！');
            }

            $ptb_number = 0;
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
                        return $this->getInfoResponse('3009', '该产品仅限使用支付宝支付，请重新支付');
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

                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($user->groupid, true));
                    if ($shopIndex->isVipGoods($item->good_id) && $user->groupid >= 23) {
                        return $this->getInfoResponse('4019', '您好，会员商品只能购买一件呢！');
                    }
                }
            }

            Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export('支付', true));
            if ($type == 1) {
                $userMoneyService = new UserMoney();
                $userMoneyService->minusCnyAndLog($arrRequest['app_id'], $pay_money, '20002', 'all');
//                $userAccount->subtractPTBMoney($ptb_number, $user->uid);
//                $insert_id = $creditLog->addLog($user->uid, "SHX", ['extcredits4' => -$ptb_number]);
//                $extcredits4_change = $account->extcredits4 - $ptb_number;
//                $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
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
                if ($arrRequest['app_id'] != 1694511) return $this->getInfoResponse('4123', '支付宝正在升级中，请先用微信或者余额支付');
                $order = [
                    'out_trade_no' => $orders->order_id,
                    'total_amount' => $real_price,
                    'subject' => '我的商城购物 - ' . $real_price . '元',
                ];
                $alipay = Pay::alipay($this->config_v1)->app($order);
                return $this->getResponse($alipay->getContent());
            }
            if ($type == 2) {
                if ($arrRequest['app_id'] != 1694511) return $this->getInfoResponse('4123', '支付宝正在升级中，请先用微信或者余额支付');
                $order = [
                    'out_trade_no' => $orders->order_id,
                    'total_amount' => $real_price,
                    'subject' => '我的商城购物 - ' . $real_price . '元',
                ];
                $alipay = Pay::alipay($this->config_v1)->app($order);
                return $this->getResponse($alipay->getContent());
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
     * 微信付款 余额支付
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Yansongda\Pay\Gateways\Alipay\AppGateway
     * @throws ApiException
     */
    public function wechatPayV1(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {

//        return $this->getInfoResponse('3003', '系统升级期间爆款商城暂停付款，预计1月1号升级完成，敬请期待！');
        try {
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
            }

            if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028, 1694511])) {
                return $this->getInfoResponse('3003', '系内测期间，商城暂未开放，感谢您的耐心等待！');
            }

            $ptb_number = 0;
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
                        return $this->getInfoResponse('3009', '该产品仅限使用支付宝支付，请重新支付');
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
            foreach ($shop_orders_one as $k => $item) {
                if ($shopIndex->isVipGoods($item->good_id)) {
                    $is_have = $rechargeOrder->getOrdersById($orders->order_id);
                    if ($is_have) {
                        break;
                    }
                    $order_model->installOrder($user->uid, 800, $orders->order_id);
                }
                $good_special = $shopGoods->getOneById($item->good_id);
                if ($good_special) {
                    if ($good_special->volume <= 0) {
                        return $this->getInfoResponse('4009', '您的商品中存在已售罄商品，请重新选择');
                    }
                    if ($good_special->volume < $item->number) {
                        return $this->getInfoResponse('4015', '您输入的数量，库存已经无法满足了！');
                    }
                    if ($good_special->is_push == 0 && $add_express > 0) {
                        return $this->getInfoResponse('4016', '抱歉该商品偏远地区不发货哦，请继续选购其他好货！');
                    }
                }
            }
            if ($type == 1) {
                $userMoneyService = new UserMoney();
                $userMoneyService->minusCnyAndLog($arrRequest['app_id'], $pay_money, '10001', 'all');
//                $userAccount->subtractPTBMoney($ptb_number, $user->uid);
//                $insert_id = $creditLog->addLog($user->uid, "SHX", ['extcredits4' => -$ptb_number]);
//                $extcredits4_change = $account->extcredits4 - $ptb_number;
//                $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
                $res_maid = $order_model->processOrder($orders->order_id);
                if ($res_maid) {
                    return $this->getResponse('购买成功！');
                }
                return $this->getResponse('购买失败！请联系客服');
            }

            if ($type == 3) {
                if ($arrRequest['app_id'] == 1569840) {
                    $real_price = 0.01;
                }
                if ($arrRequest['app_id'] == 1694511) {
                    $real_price = 0.01;
                }
                $order = [
                    'out_trade_no' => $orders->order_id,
                    'total_fee' => ($real_price * 100),
                    'body' => '我的商城购物 - ' . $real_price . '元',
                ];
                $this->wechat_config['notify_url'] = 'http://api.36qq.com/api/shop_wechat_pay_now_wuhang_v1';
                $pay = Pay::wechat($this->wechat_config)->app($order);
                return $this->getResponse($pay->getContent());
            }
            if ($type == 2) {
                if (in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028])) {
                    $real_price = 0.01;
                }
                $order = [
                    'out_trade_no' => $orders->order_id,
                    'total_fee' => ($real_price * 100),
                    'body' => '我的商城购物 - ' . $real_price . '元',
                ];
                $this->wechat_config['notify_url'] = 'http://api.36qq.com/api/shop_wechat_pay_now_wuhang_v1';
                $pay = Pay::wechat($this->wechat_config)->app($order);
                return $this->getResponse($pay->getContent());
            }

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了哦！', '500');
        }
    }

    /**
     * 支付宝支付 余额支付 禾盟通
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @throws ApiException
     */
    public function storeV2(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {
        return $this->getInfoResponse('3003', '系统升级期间暂停付款，请耐心等待！！');

        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('is_check', $arrRequest) || !array_key_exists('address_id', $arrRequest) || !array_key_exists('order_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['order_id']) {
                $orders = $shopOrders->getById($arrRequest['order_id']);
                if (!$orders) {
                    return $this->getInfoResponse('3005', '订单不存在');
                }

                if (Cache::has('he_me_to_pay_call_back_' . $orders['order_id'])) { // 防止重复订单支付成功更新状态
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
            if (Cache::has('shop_orders_store_' . $arrRequest['app_id'])) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试...');
            }
            Cache::put('shop_orders_store_' . $arrRequest['app_id'], 1, 0.5);

//            if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028, 1694511])) {
//                return $this->getInfoResponse('3003', '内测期间，商城暂未开放，感谢您的耐心等待！');
//            }
            //拦截版本
            $request_device = $request->header('Accept-Device'); //设备类型
            $request_appversion = $request->header('Accept-Appversion'); //版本号
//            if ($request_device != 'android' || $request_appversion < 198) {
            if (($request_device == 'android' && $request_appversion < 198) || ($request_device == 'ios' && version_compare($request_appversion, '4.6.5', '<'))) {
                return $this->getInfoResponse('3003', '系统升级期间暂停付款，请耐心等待！！');
            }

            $ptb_number = 0;
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
                        return $this->getInfoResponse('3009', '该产品仅限使用支付宝支付，请重新支付');
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

                    Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export($user->groupid, true));
                    if ($shopIndex->isVipGoods($item->good_id) && $user->groupid >= 23) {
                        return $this->getInfoResponse('4019', '您好，会员商品只能购买一件呢！');
                    }
                }
            }

            Storage::disk('local')->append('callback_document/test_shop_change_all_pay.txt', var_export('支付', true));
            if ($type == 1) {
                $userMoneyService = new UserMoney();
                $userMoneyService->minusCnyAndLog($arrRequest['app_id'], $pay_money, '20002', 'all');
//                $userAccount->subtractPTBMoney($ptb_number, $user->uid);
//                $insert_id = $creditLog->addLog($user->uid, "SHX", ['extcredits4' => -$ptb_number]);
//                $extcredits4_change = $account->extcredits4 - $ptb_number;
//                $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
                $res_maid = $order_model->processOrder($orders->order_id);

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

                    return $this->getResponse('购买成功！');
                }
                return $this->getResponse('购买失败！请联系客服');
            }

            if ($arrRequest['app_id'] == 1694511) {
                $real_price = 0.01;
            }

            if ($type == 3) {
//                if (!in_array($arrRequest['app_id'], [1694511, 3675700, 4446218,9873668])) return $this->getInfoResponse('4123', '支付宝正在升级中，请先用微信或者余额支付');
//                $order = [
//                    'out_trade_no' => $orders->order_id,
//                    'total_amount' => $real_price,
//                    'subject' => '我的商城购物 - ' . $real_price . '元',
//                ];
//                $alipay = Pay::alipay($this->config_v1)->app($order);
//                return $this->getResponse($alipay->getContent());
                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appPay($real_price, $real_price, $orders->order_id);
                $res = json_decode($data, true);
                if (@$res['fcode'] != 10000) {
                    return $this->getResponse('购买失败！请联系客服');
                }
                return $this->getResponse(@$res['fcode_url']);
            }
            if ($type == 2) {
//                if (!in_array($arrRequest['app_id'], [1694511, 3675700, 4446218,9873668])) return $this->getInfoResponse('4123', '支付宝正在升级中，请先用微信或者余额支付');
//                $order = [
//                    'out_trade_no' => $orders->order_id,
//                    'total_amount' => $real_price,
//                    'subject' => '我的商城购物 - ' . $real_price . '元',
//                ];
//                $alipay = Pay::alipay($this->config_v1)->app($order);
//                return $this->getResponse($alipay->getContent());
//                return $alipay;
                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appPay($real_price, $real_price, $orders->order_id);
                $res = json_decode($data, true);
                if (@$res['fcode'] != 10000) {
                    return $this->getResponse('购买失败！请联系客服');
                }
                return $this->getResponse(@$res['fcode_url']);
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }

            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 微信付款 余额支付 禾盟通
     * post {"app_id":"1569840","is_check":"1","address_id":"22","order_id":"129"}
     * @param Request $request
     * @param Order $order_model
     * @param ShopGoods $shopGoods
     * @param RechargeUserLevel $rechargeUserLevel
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAboutLog $aboutLog
     * @param UserCreditLog $creditLog
     * @param UserAccount $userAccount
     * @param ShopAddress $shopAddress
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @throws ApiException
     */
    public function wechatPayV2(Request $request, Order $order_model, ShopGoods $shopGoods, ShopIndex $shopIndex, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo, UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, ShopAddress $shopAddress, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne)
    {
        return $this->getInfoResponse('3003', '系统升级期间暂停付款，请耐心等待！！');
//        return $this->getInfoResponse('3003', '系统升级期间爆款商城暂停付款，预计1月1号升级完成，敬请期待！');
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('is_check', $arrRequest) || !array_key_exists('address_id', $arrRequest) || !array_key_exists('order_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['order_id']) {
                $orders = $shopOrders->getById($arrRequest['order_id']);
                if (!$orders) {
                    return $this->getInfoResponse('3005', '订单不存在');
                }

                if (Cache::has('he_me_to_pay_call_back_' . $orders['order_id'])) { // 防止重复订单支付成功更新状态
                    return $this->getInfoResponse('3007', '订单已支付！正在处理中。');
                }
                if ($orders->status == 1) {
                    return $this->getInfoResponse('3007', '订单已支付！正在处理中。');
                }

                if ($orders->app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('3006', '这不是您的订单');
                }
            }

            if (Cache::has('shop_orders_store_wx_' . $arrRequest['app_id'])) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试...');
            }
            Cache::put('shop_orders_store_wx_' . $arrRequest['app_id'], 1, 0.5);

//            if (!in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028, 1694511])) {
//                return $this->getInfoResponse('3003', '系内测期间，商城暂未开放，感谢您的耐心等待！');
//            }
            //拦截版本
            $request_device = $request->header('Accept-Device'); //设备类型
            $request_appversion = $request->header('Accept-Appversion'); //版本号
//            if ($request_device != 'android' || $request_appversion < 198) {
            if (($request_device == 'android' && $request_appversion < 198) || ($request_device == 'ios' && version_compare($request_appversion, '4.6.5', '<'))) {
                return $this->getInfoResponse('3003', '系统升级期间暂停付款，请耐心等待！！');
            }

            $ptb_number = 0;
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
                        return $this->getInfoResponse('3009', '该产品仅限使用支付宝支付，请重新支付');
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
            foreach ($shop_orders_one as $k => $item) {
                if ($shopIndex->isVipGoods($item->good_id)) {
                    $is_have = $rechargeOrder->getOrdersById($orders->order_id);
                    if ($is_have) {
                        break;
                    }
                    $order_model->installOrder($user->uid, 800, $orders->order_id);
                }
                $good_special = $shopGoods->getOneById($item->good_id);
                if ($good_special) {
                    if ($good_special->volume <= 0) {
                        return $this->getInfoResponse('4009', '您的商品中存在已售罄商品，请重新选择');
                    }
                    if ($good_special->volume < $item->number) {
                        return $this->getInfoResponse('4015', '您输入的数量，库存已经无法满足了！');
                    }
                    if ($good_special->is_push == 0 && $add_express > 0) {
                        return $this->getInfoResponse('4016', '抱歉该商品偏远地区不发货哦，请继续选购其他好货！');
                    }
                }
            }
            if ($type == 1) {
                $userMoneyService = new UserMoney();
                $userMoneyService->minusCnyAndLog($arrRequest['app_id'], $pay_money, '10001', 'all');
//                $userAccount->subtractPTBMoney($ptb_number, $user->uid);
//                $insert_id = $creditLog->addLog($user->uid, "SHX", ['extcredits4' => -$ptb_number]);
//                $extcredits4_change = $account->extcredits4 - $ptb_number;
//                $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
                $res_maid = $order_model->processOrder($orders->order_id);

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

                    return $this->getResponse('购买成功！');
                }
                return $this->getResponse('购买失败！请联系客服');
            }

            if ($type == 3) {
                if ($arrRequest['app_id'] == 1569840) {
                    $real_price = 0.01;
                }
                if ($arrRequest['app_id'] == 1694511) {
                    $real_price = 0.01;
                }
//                $order = [
//                    'out_trade_no' => $orders->order_id,
//                    'total_fee' => ($real_price * 100),
//                    'body' => '我的商城购物 - ' . $real_price . '元',
//                ];
//                $this->wechat_config['notify_url'] = 'http://api.36qq.com/api/shop_wechat_pay_now_wuhang_v1';
//                $pay = Pay::wechat($this->wechat_config)->app($order);
//                return $this->getResponse($pay->getContent());
                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appWxPay($real_price, $orders->order_id, $arrRequest['app_id']);
                return $this->getResponse($data);
            }
            if ($type == 2) {
                if (in_array($arrRequest['app_id'], [6080694, 9873717, 4693063, 3675700, 10004595, 10004596, 10005028])) {
                    $real_price = 0.01;
                }
//                $order = [
//                    'out_trade_no' => $orders->order_id,
//                    'total_fee' => ($real_price * 100),
//                    'body' => '我的商城购物 - ' . $real_price . '元',
//                ];
//                $this->wechat_config['notify_url'] = 'http://api.36qq.com/api/shop_wechat_pay_now_wuhang_v1';
//                $pay = Pay::wechat($this->wechat_config)->app($order);
//                return $this->getResponse($pay->getContent());
                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appWxPay($real_price, $orders->order_id, $arrRequest['app_id']);
                return $this->getResponse($data);
            }

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了哦！', '500');
        }
    }

    /**
     * 展示单个订单的内容详情
     * get {"app_id":"1"}
     * /1
     * @param $id
     * @param Request $request
     * @param ShopOrders $shopOrders
     * @param ShopOrdersOne $shopOrdersOne
     * @param ShopGoods $shopGoods
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, Order $order, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne, ShopGoods $shopGoods, AdUserInfo $adUserInfo, UserAccount $userAccount, ShopAddress $shopAddress)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $res_order = $shopOrders->getById($id);
            $all_price = $res_order->price;
            $res_order_one = $shopOrdersOne->getAllGoods($id);
            if ($res_order->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('4000', '这不是您的订单！');
            }

            foreach ($res_order_one as $k => $v) {
                $good = $shopGoods->getOneById($v['good_id']);
                if (!$good) {
                    return $this->getInfoResponse('4004', '选中部分商品不存在！');
                }
                $res_order_one[$k]->title = $good->title;
                $res_order_one[$k]->header_img = json_decode($good->header_img);
            }

            $address = $shopAddress->getUserDefaultAddress($arrRequest['app_id']);
            if (!$address) {
                return $this->getInfoResponse('4088', '您没有填写过任何地址！请去完善地址信息！');
            }
            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            $userAccount = $userAccount->getUserAccount($user->uid);

            $all_express = $order->noArea($address->id, $id);
            if ($all_price * 10 >= $userAccount->extcredits4) {
                $deduct_ptb = $userAccount->extcredits4;
            } else {
                $deduct_ptb = $all_price * 10;
            }
            $userModel = new TaobaoUser();
            $account_money = $userModel->getUserMoney($arrRequest['app_id']);
            $deduct_money = 0;
            if ($all_price >= $account_money) {
                $deduct_money = $account_money;
            } else {
                $deduct_money = $all_price;
            }
            return $this->getResponse([
                'address' => $address,
                'order_id' => $id,
                'all_express' => (string)$all_express,
                'all_price' => (string)$all_price,
                'account_ptb' => (string)$userAccount->extcredits4,
                'deduct_ptb' => (string)$deduct_ptb,
                'account_money' => (string)$account_money,
                'deduct_money' => (string)$deduct_money,
                'order_detail' => $res_order_one,
            ]);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }

    /**
     * 退货-仅仅针对未发货状态的商品(如果订单已经发货，则必须走退换货流程)
     * Show the form for editing the specified resource.
     * get {"app_id":"","":""}
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {


    }

    /**
     * 确认收货
     * Update the specified resource in storage.
     * get {"app_id":"","orders_one_id":""}
     * @param Request $request
     * @param $id
     * @param ShopOrdersOne $shopOrdersOne
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, PurchaseUserGroup $purchaseUserGroup, PretendShopOrdersMaid $pretendShopOrdersMaid, ShopOrdersOne $shopOrdersOne, ShopIndex $shopIndex, ShopOrders $shopOrders, AppUserInfo $appUserInfo, RechargeUserLevel $rechargeUserLevel, RechargeOrder $rechargeOrder, Order $order_return)
    {

//        return $this->getInfoResponse('4004', '升级中！！！');
        try {
            DB::beginTransaction();
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('orders_one_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            if (Cache::has('shop_confirm_receipt_' . $arrRequest['orders_one_id'])) {
                return $this->getInfoResponse('1001', '该订单已被确认！');
            }
            Cache::put('shop_confirm_receipt_' . $arrRequest['orders_one_id'], 1, 10);

            $res = $shopOrdersOne->getOneById($arrRequest['orders_one_id']);
            $order = $shopOrders->getById($res->order_id);

            if (!$res) {
                return $this->getInfoResponse('4004', '订单不存在！！！');
            }
            if ($res->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('3000', '这不是您的订单！！！！');
            }
            if (!$res->status) {
                return $this->getInfoResponse('4004', '订单未付款！！！！');
            }
            if ($res->status <> 2) {
                return $this->getInfoResponse('4004', '订单状态不正确！！');
            }
            if ($order->status <> 2) {
                return $this->getInfoResponse('4004', '订单状态不正确！');
            }

            $shopOrdersOne->changeStatus($arrRequest['orders_one_id'], 3);
            $shopOrders->updateStatusOrders($res->order_id, 3);
            $rechargeOrder->updateOrderStatus($order->order_id);
            $app_user = $appUserInfo->getUserById($order->app_id);


            //==========================//
            $obj_shop_vip_buy = new ShopVipBuy();
            $obj_growth_user_value = new GrowthUserValue();
            $obj_change_vip_service = new ChangeVipService();
            $obj_growth_user_value_change = new GrowthUserValueChange();
            $obj_ad_user_info = new AdUserInfo();
            $obj_growth_user_value_Config = new GrowthUserValueConfig();
            $obj_Shop_commission_service = new ShopCommissionService();
            //判断是否是vip商品
            $obj_data_shop_vip = $obj_shop_vip_buy->where('vip_id', $res->good_id)->first();

            $count_partner = 0;

            //是vip商品取固定成长值 can_active
            if (!empty($obj_data_shop_vip)) {
                //vip分佣
                $count_partner = $purchaseUserGroup->returnVipCommissionV2($order->order_id);#直属分 返回合伙人数量
                $obj_Shop_commission_service->vipShopCommission($order->order_id, $count_partner);#第三方分
                $pretendShopOrdersMaid->where('order_id', $order->order_id)->update(['status' => 1]);
                //得到该vip商品所增加的成长值
                $can_active = $obj_data_shop_vip->can_active;
            } else {//得到非vip加的成长值
                //非vip原来分佣逻辑
                if ($order->all_profit_value <> 0.00) {
                    if (!($shopIndex->isVipGoods($res->good_id))) {
                        $count_partner = $order_return->newReturnCommission($order->order_id, $order->all_profit_value, $app_user->parent_id, $res->good_id);#直属分 返回合伙人数量
                        $obj_Shop_commission_service->generalShopCommission($order->order_id, $order->all_profit_value, $app_user->parent_id, $count_partner);#第三方分
                    }
                }

                $shopGoods = new ShopGoods();
                $res_good = $shopGoods->getGoodData($res->good_id);
                $num_shop_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');

                //判断是否为普通用户
                $int_user_groupid = $obj_ad_user_info->where(['pt_id' => $res->app_id])->value('groupid');
                if ($int_user_groupid == 10) {
                    //判断是否填写普通商品购买所增加的成长值
                    if (!($res_good->can_active > 0)) {
                        $can_active = round($res_good->profit_value / $num_shop_growth_value * 8 * 0.41 * 0.3, 2);
                    } else {
                        $can_active = round($res_good->can_active, 2);
                    }
                } else {
                    $can_active = round($res_good->profit_value / $num_shop_growth_value, 2);
                }

            }

            try {
                $obj_Shop_commission_service->shopManagerCommission($order->order_id, $order->all_profit_value, $app_user->parent_id, $count_partner);#经理分佣
            } catch (\Exception $e) {
                Storage::disk('local')->append('callback_document/shop_manager/' . date('Ymd') . '.txt', date('Y-m-d H:i:s') . '  ' . var_export($e->getMessage(), true));
            }

            //所加成长值小于等于99999 分佣处理
            if ($can_active < 99999) {
                //临时兼容处理分过成长值的订单
                $arr_orders = [
                    'WXAPPLET20200305202059KQZan', 'WXAPPLET20200308120712JNHjT', 'WXAPPLET20200307104007FnER9',
                    'WXAPPLET20200306101053j7mlN', 'WXAPPLET20200305101725dGu8L', 'WXAPPLET202003072317231iZ3B',
                    'WXAPPLET20200307151245LFXaZ', 'WXAPPLET20200308151716zJ78h', 'WXAPPLET20200308154106CAJM8',
                    'WXAPPLET20200307105211tFYIf', 'WXAPPLET20200307130207nuxWl', 'WXAPPLET20200308105846DnfUX',
                    'WXAPPLET20200305100320DPYiE', 'WXAPPLET20200306204021B3J6U', 'WXAPPLET2020030718472549HcA',
                    'WXAPPLET20200308151815bsoVl', 'WXAPPLET20200306151642Vl4sx', 'WXAPPLET20200306141744m6m2j',
                    'WXAPPLET20200307162434Ze0Zz', 'WXAPPLET20200307085922gvkGi', 'WXAPPLET202003081541205kr4X',
                    'WXAPPLET20200308161230PCJNn', 'WXAPPLET20200305172717dkjg1', 'WXAPPLET20200307203534vmA6h',
                    'WXAPPLET20200308175739ZLjiL', 'WXAPPLET20200308173054ngGpC', 'WXAPPLET20200308112411Z4FSY',
                    'WXAPPLET20200308151947zP7cv', 'WXAPPLET20200306155134lKOLq', 'WXAPPLET20200306204719fzAf2',
                    'WXAPPLET20200305184709dqxNk', 'WXAPPLET20200306181400cjeHw', 'WXAPPLET20200306052416EayBr',
                    'WXAPPLET20200306092659OXd8w', 'WXAPPLET20200305154009Tigfl', 'WXAPPLET20200306085106V6nq4',
                    'WXAPPLET202003060908330SuNI', 'WXAPPLET20200305181837TwFZf', 'WXAPPLET20200305152048C3nmG',
                    'WXAPPLET20200307162544EYgOu', 'WXAPPLET20200308094337eSeOq', 'WXAPPLET20200307142903dhFYs',
                    'WXAPPLET20200305131257pX0Dx', 'WXAPPLET20200306110530UmEFU', 'WXAPPLET20200307121515bKl7T',
                    'WXAPPLET20200307141144V5i4W', 'WXAPPLET20200306193429dkenf', 'WXAPPLET20200307111144o4xMh',
                    'WXAPPLET20200306210633ucUF7', 'WXAPPLET20200307101925YsXSD', 'WXAPPLET20200308114808QmjNI',
                    'WXAPPLET20200308082932yY0cF', 'WXAPPLET20200305194119LNQRr', 'WXAPPLET20200306084600oAGmN',
                    'WXAPPLET20200306162341yGBGO', 'WXAPPLET20200306204433eI6cy', 'WXAPPLET20200306212559Dm3Ef',
                    'WXAPPLET20200306163848gnML1', 'WXAPPLET20200306193134F9jk9', 'WXAPPLET20200307182257gZsjn',
                    'WXAPPLET20200308180133pzJyf', 'WXAPPLET20200308183035TI7Xe', 'WXAPPLET20200308144110oMRTK',
                    'WXAPPLET20200306173115Sua5Z', 'WXAPPLET20200306114917CDypW', 'WXAPPLET20200308140715FW03m',
                    'WXAPPLET20200308100855EaiUB', 'WXAPPLET20200305003009dVADO', 'WXAPPLET20200306064418pjryI',
                    'WXAPPLET20200305161500RbvkW', 'WXAPPLET20200306104233EDMOb', 'WXAPPLET20200306083738eEGid',
                    'WXAPPLET20200307175328hBrJc', 'WXAPPLET202003061243272mV0p', 'WXAPPLET20200307172850WuG1p',
                    'WXAPPLET20200308130116zSFEh', 'WXAPPLET20200306202250J04qr', 'WXAPPLET202003071817548iqfx',
                    'WXAPPLET20200307165624OuOWt', 'WXAPPLET20200305185323ar50k', 'WXAPPLET20200308214851KPLAj',
                    'WXAPPLET20200308143926k1GJ0', 'WXAPPLET20200305202426JDA9D', 'WXAPPLET20200307103551bTndj',
                    'WXAPPLET20200305071021TLbEG', 'WXAPPLET20200307151227OWRwO', 'WXAPPLET20200308152953A1WaS',
                    'WXAPPLET20200307121644MOHdP', 'WXAPPLET20200308151617idju1', 'WXAPPLET20200304204547mYB1t',
                    'WXAPPLET20200306124737DmnB2', 'WXAPPLET20200305201218mgHbM', 'WXAPPLET20200307011758lCGfd',
                    'WXAPPLET20200307101627pXunQ', 'WXAPPLET20200308135926RC0fV', 'WXAPPLET20200306142127mHpKI',
                    'WXAPPLET20200308120707knLno', 'WXAPPLET20200306180328F23Bo', 'WXAPPLET20200306110902XrHHv',
                    'WXAPPLET20200308092748nvOZ9', 'WXAPPLET20200304214605gotpJ', 'WXAPPLET20200305154754B7du3',
                    'WXAPPLET20200305204047Ybxfk', 'WXAPPLET20200306225016xCBm1', 'WXAPPLET20200306175326kGgqo',
                    'WXAPPLET20200306164600rl4ou', 'WXAPPLET20200305135432j8eMN', 'WXAPPLET20200308162858lD7eF',
                    'WXAPPLET20200305112230ZDGUh', 'WXAPPLET20200308125911FeT6a', 'WXAPPLET202003060649246lHc6',
                    'WXAPPLET20200305090429YRynr', 'WXAPPLET20200305131849UyPvT', 'WXAPPLET20200306173308NhYNX',
                    'WXAPPLET20200307112100tW15Y', 'WXAPPLET20200307132607Yby9M', 'WXAPPLET20200306185151atS9W',
                    'WXAPPLET20200305181525MdDV4', 'WXAPPLET202003052103353EGy1', 'WXAPPLET20200306222158NmSFD',
                    'WXAPPLET20200305171014oSndK', 'WXAPPLET20200305121711nl1Dd', 'WXAPPLET20200308170420iAwhB',
                    'WXAPPLET20200305145341P2rFX', 'WXAPPLET2020030818455410MPN', 'WXAPPLET20200306181450kJ2VN',
                    'WXAPPLET20200307131954pgx13', 'WXAPPLET202003061017496jfx4', 'WXAPPLET20200305150251DaNmN',
                    'WXAPPLET20200307220413wb0xf', 'WXAPPLET20200306123440IX4Ba', 'WXAPPLET20200307222350trHPf',
                    'WXAPPLET20200307235243bB2Gf', 'WXAPPLET20200306204144vME48', 'WXAPPLET20200306223735sJef1',
                    'WXAPPLET202003080826414hr1h', 'WXAPPLET20200308085613ZMFVT', 'WXAPPLET20200305142400k3Q1P',
                    'WXAPPLET20200308140645HDYRp', 'WXAPPLET20200307180728xtppI', 'WXAPPLET20200304225458KOL3L',
                    'WXAPPLET20200305120838T3Yot', 'WXAPPLET20200307150456eyaAp', 'WXAPPLET20200305223747ykHNi',
                    'WXAPPLET202003072145490qsXp', 'WXAPPLET20200306024238MlUPo', 'WXAPPLET20200305135902FOFB3',
                    'WXAPPLET20200307080158tDhsi', 'WXAPPLET20200307153718JXi2e', 'WXAPPLET202003081402341ut8t',
                    'WXAPPLET20200308123940wfNy4', 'WXAPPLET202003081800572ieAR', 'WXAPPLET202003081751136RyZV',
                    'WXAPPLET20200308161539REW2g', 'WXAPPLET20200306113327GwK3t', 'WXAPPLET20200305103314hAVQT',
                    'WXAPPLET202003061022584ZzCz', 'WXAPPLET202003081456332tEOc', 'WXAPPLET20200305181525qpS8H',
                    'WXAPPLET2020030518320784y0O', 'WXAPPLET20200307135449qlzvB', 'WXAPPLET20200306134114huO4d',
                    'WXAPPLET20200306112914Z5l38', 'WXAPPLET20200306204018UoKFR', 'WXAPPLET20200306094417bS7dy',
                    'WXAPPLET20200306165948wmudy', 'WXAPPLET20200307190045YcXDg', 'WXAPPLET20200305122818xGk1R',
                    'WXAPPLET20200307134909VsnOX', 'WXAPPLET202003060725209lffH', 'WXAPPLET20200306141956YZH0X',
                    'WXAPPLET20200307195622eZLxW', 'WXAPPLET20200305154032G9OcU', 'WXAPPLET20200306104421go7ik',
                    'WXAPPLET20200306105339jFTd1', 'WXAPPLET202003080920069jr16', 'WXAPPLET20200305165841FbcLB',
                    'WXAPPLET20200306110409PrY3c', 'WXAPPLET20200307142352RO4RE', 'WXAPPLET20200307175930mRH9j',
                    'WXAPPLET20200305184051NYWoM', 'WXAPPLET20200305173024tigUq', 'WXAPPLET20200309100736nEdn2',
                    'WXAPPLET20200308070329efMRR', 'WXAPPLET20200308155924uzQbu', 'WXAPPLET20200306220631yzUPW',
                    'WXAPPLET20200307172322VPUjJ', 'WXAPPLET20200306162926JK20I', 'WXAPPLET20200305164030Vmw9D',
                    'WXAPPLET20200306165659fT2Ik', 'WXAPPLET20200307143030SiRPx', 'WXAPPLET20200306130359a2xWb',
                    'WXAPPLET20200308131132JFils', 'WXAPPLET202003070915004b6xC', 'WXAPPLET20200305163251AQMOz',
                    'WXAPPLET20200308101625IIjfZ', 'WXAPPLET20200306130702EfuHo', 'WXAPPLET20200306192310mzsNv',
                    'WXAPPLET20200308164506g9YBo', 'WXAPPLET20200308080340w9a7h', 'WXAPPLET20200306164947gikkD',
                    'WXAPPLET20200308171646jWmjk', 'WXAPPLET20200307114800sz7i4', 'WXAPPLET202003061120349AHdC',
                    'WXAPPLET20200308175535RrXib', 'WXAPPLET20200308175223PccRs', 'WXAPPLET20200305180535adZ2r',
                    'WXAPPLET20200308104304gYCVv', 'WXAPPLET20200307140841zvFg3', 'WXAPPLET202003081645432ssGw',
                    'WXAPPLET20200306073351J8qiQ', 'WXAPPLET20200305101225J1Uxk', 'WXAPPLET20200308140916YPNCH',
                    'WXAPPLET20200307093441M0alu', 'WXAPPLET20200307141018JiKL2', 'WXAPPLET20200306223213vuBX2',
                    'WXAPPLET20200307095314Ae41K', 'WXAPPLET20200306110454WR2T8', 'WXAPPLET20200308180428eNld0',
                    'WXAPPLET20200308172837EvLT8', 'WXAPPLET20200306084212Y283i', 'WXAPPLET20200306163718SW6F4',
                    'WXAPPLET20200308104916z9hXJ', 'WXAPPLET20200308171416GJV10', 'WXAPPLET20200307114212kJ5aN',
                    'WXAPPLET202003071129187qR87', 'WXAPPLET20200307212546q7bsh', 'WXAPPLET20200308173449fEK8q',
                    'WXAPPLET20200306153744TVsz5', 'WXAPPLET202003071134116Zrmd', 'WXAPPLET202003081303588kreL',
                    'WXAPPLET20200305190609nchV0', 'WXAPPLET20200308160425CdsPC', 'WXAPPLET20200307140203veQK2',
                    'WXAPPLET202003061758218UPl9', 'WXAPPLET20200307090916K6O1m', 'WXAPPLET20200308174753OLKzr',
                    'WXAPPLET2020030817374835amR', 'WXAPPLET20200307145901g2odW', 'WXAPPLET20200305162644uMXkO',
                    'WXAPPLET20200308161127s9nqJ', 'WXAPPLET20200308220851NN9JC', 'WXAPPLET20200304220801dpxJn',
                    'WXAPPLET20200306102110Wtgd1', 'WXAPPLET20200305090315hzEar', 'WXAPPLET20200305093719krdKr',
                    'WXAPPLET20200307100934zAIvh', 'WXAPPLET20200305180749tOdJf', 'WXAPPLET202003071113209RTXK',
                    'WXAPPLET20200306224405gZRyl', 'WXAPPLET20200307140617344NI', 'WXAPPLET20200305144837adlIL',
                    'WXAPPLET20200308151802284QF', 'WXAPPLET20200308123238yUnjT', 'WXAPPLET20200305081105BH6QA',
                    'WXAPPLET20200306214632EM2PX', 'WXAPPLET20200308161750BlkSG', 'WXAPPLET2020030611544973Oic',
                    'WXAPPLET20200307191331uuddh', 'WXAPPLET202003051422264Ahh0', 'WXAPPLET20200306211252Kw64N',
                    'WXAPPLET20200307104256Dis7j', 'WXAPPLET20200306132959WVgVB', 'WXAPPLET20200307182239xKJol',
                    'WXAPPLET20200304235227hziNu', 'WXAPPLET20200308130610mCGMK', 'WXAPPLET20200308145050DaLzt',
                    'WXAPPLET202003081559489RCbd', 'WXAPPLET20200308070912fjXic', 'WXAPPLET20200305172329bxrU9',
                    'WXAPPLET20200305202704uDDTC', 'WXAPPLET20200306124207C7Tav', 'WXAPPLET20200305173906XlHmv',
                    'WXAPPLET20200308102424EhMkM', 'WXAPPLET20200308121120qMfAJ', 'WXAPPLET202003081535055670X',
                    'WXAPPLET20200305142515Xg8wA', 'WXAPPLET20200306174500u1O5P', 'WXAPPLET20200307083512Jjkm8',
                    'WXAPPLET20200305100040RgkB9', 'WXAPPLET20200305112609IaoR2', 'WXAPPLET20200308130610pAj0b',
                    'WXAPPLET20200307112443n5T8e', 'WXAPPLET20200305210744uT26d', 'WXAPPLET202003061102054n9aW',
                    'WXAPPLET20200307121639XVnGX', 'WXAPPLET20200308182446rfUeR', 'WXAPPLET20200307095211CYyMQ',
                    'WXAPPLET202003061916227Xfw8', 'WXAPPLET20200307145159P87u0', 'WXAPPLET20200307210736junIN',
                    'WXAPPLET20200305185501gZFUx', 'WXAPPLET20200306211100iy9oC', 'WXAPPLET20200308091223pCt8I',
                    'WXAPPLET20200308184229t723E', 'WXAPPLET20200306102553MubIu', 'WXAPPLET20200305140431e1lRr',
                    'WXAPPLET20200307101702hVfle', 'WXAPPLET20200307145922EKvGJ', 'WXAPPLET20200305183522fUOfo',
                    'WXAPPLET2020030809034599v5l', 'WXAPPLET20200308162719OkCa2', 'WXAPPLET202003051414359l6Zp',
                    'WXAPPLET20200306100304OICqa', 'WXAPPLET20200306131201ZcL9f', 'WXAPPLET2020030719283044H05',
                    'WXAPPLET20200305003943q5Ueh', 'WXAPPLET20200305160750EapkA', 'WXAPPLET20200305233707MygsG',
                    'WXAPPLET20200305140946ynsY8', 'WXAPPLET20200305194737sLBJZ', 'WXAPPLET20200306105641WuHpF',
                    'WXAPPLET202003060844545CCjc', 'WXAPPLET20200308153934JwUhR', 'WXAPPLET202003060911235UsX3',
                    'WXAPPLET2020030710414439uBC', 'WXAPPLET2020030515355220tUX', 'WXAPPLET20200305201401ufVb7',
                    'WXAPPLET20200307123021RKGLg', 'WXAPPLET20200308180931xjXBI', 'WXAPPLET20200307101826ayd88',
                    'WXAPPLET20200308141010ZcWOa', 'WXAPPLET20200304221450zXVQg', 'WXAPPLET20200308161321bJDia',
                    'WXAPPLET20200306113709F3zbk', 'WXAPPLET20200308123318I476f', 'WXAPPLET20200304233622Ae67H',
                    'WXAPPLET20200305200148Y3wMI', 'WXAPPLET20200306033238ifii3', 'WXAPPLET20200305120021KYaza',
                    'WXAPPLET20200307162057CA2K5', 'WXAPPLET20200308155813VzbYx', 'WXAPPLET20200307233355a7zRX',
                    'WXAPPLET20200305141414KnJMq', 'WXAPPLET20200307131712wyUJh', 'WXAPPLET20200308183423MMctk',
                    'WXAPPLET2020030517135242Z5c', 'WXAPPLET20200308135604USsBC', 'WXAPPLET20200305180008Olq5l',
                    'WXAPPLET20200309135824vabdr', 'WXAPPLET20200306085305QTsUD', 'WXAPPLET20200306224043vWLMf',
                    'WXAPPLET20200308141713j6h2y', 'WXAPPLET20200307161753qFhkP', 'WXAPPLET20200307154310zqz2P',
                    'WXAPPLET20200307110425NdDEW', 'WXAPPLET20200305173535Gk0Kr', 'WXAPPLET20200305194501DCwdO',
                    'WXAPPLET20200305183701sekVZ', 'WXAPPLET20200306114706sKzi1', 'WXAPPLET20200307180544yrCoi',
                    'WXAPPLET20200306152012uu81n', 'WXAPPLET20200307074819u5H1y', 'WXAPPLET20200307135541VpPIK',
                    'WXAPPLET20200306161943arlA7', 'WXAPPLET202003080949403g6AD', 'WXAPPLET20200306171157fotgE',
                    'WXAPPLET202003080852018LE7T', 'WXAPPLET20200307150546ENbDp', 'WXAPPLET202003072002036Vy3y',
                    'WXAPPLET202003051701583oQkm', 'WXAPPLET202003071454476PvVt', 'WXAPPLET20200307142218YDjmA',
                    'WXAPPLET20200307000846crkX1', 'WXAPPLET20200307173958Vd5Wj', 'WXAPPLET20200305172956JXZI0',
                    'WXAPPLET20200305185706CCCRs', 'WXAPPLET20200306212315vmWGv', 'WXAPPLET20200306164257p9OeZ',
                    'WXAPPLET20200307160410we5uz', 'WXAPPLET20200305154125SOipj', 'WXAPPLET20200306193522HDSi3',
                    'WXAPPLET20200308153549Dcvv1', 'WXAPPLET20200305174130hu1yJ', 'WXAPPLET20200307182852dZmoU',
                    'WXAPPLET20200306220645IafUY', 'WXAPPLET20200307232212zIwdI', 'WXAPPLET20200308131213o8zcu',
                    'WXAPPLET20200307152916Pyx8o', 'WXAPPLET20200306100712OrvHn', 'WXAPPLET20200306110633XqlFE',
                    'WXAPPLET20200308123822XNPil', 'WXAPPLET202003052150305VbKb', 'WXAPPLET202003072145224hjGW',
                    'WXAPPLET20200308062647rlJS1', 'WXAPPLET202003070810198XNkY', 'WXAPPLET20200307221841qmUe4',
                    'WXAPPLET20200306104745wpgpA', 'WXAPPLET20200308062353m2yqz', 'WXAPPLET20200308180844kCCYO',
                    'WXAPPLET20200306143640PaznO', 'WXAPPLET20200305151330motea', 'WXAPPLET20200306101952kVgkp',
                    'WXAPPLET202003051506109AOru', 'WXAPPLET20200307231603hbdjg', 'WXAPPLET20200307220532kGrPJ',
                    'WXAPPLET20200305185356TQUTa', 'WXAPPLET202003061550489Axm3', 'WXAPPLET20200308155040kF16W',
                    'WXAPPLET20200308083235IKoF7', 'WXAPPLET20200308115659kyj7T', 'WXAPPLET20200307102101QMYOZ',
                    'WXAPPLET20200305224450iTZ0U', 'WXAPPLET20200307215131KA5yE', 'WXAPPLET20200308105415REsm4',
                    'WXAPPLET202003061820156s8Pq', 'WXAPPLET202003081500020wdso', 'WXAPPLET20200308161140O1GGk',
                    'WXAPPLET20200308175223QOSJS', 'WXAPPLET20200305190011czFlS', 'WXAPPLET20200307152950Nh6hO',
                    'WXAPPLET202003070206022YDcK', 'WXAPPLET20200308163552sPERe', 'WXAPPLET20200305131002hFpfx',
                    'WXAPPLET20200306134330Ujrrj', 'WXAPPLET20200306204240NVSA9', 'WXAPPLET20200306173257B6ute',
                    'WXAPPLET20200306094701oNsfo', 'WXAPPLET20200306052936IJDhZ', 'WXAPPLET20200305123804v73YZ',
                    'WXAPPLET20200307231904EJXZf', 'WXAPPLET20200307141604qkI81', 'WXAPPLET20200306143901FBjWt',
                    'WXAPPLET20200305154430SV52h', 'WXAPPLET20200306172405JWsHC', 'WXAPPLET20200307163353DEtYc',
                    'WXAPPLET20200308101858a1Kvm', 'WXAPPLET20200305162610fVK8A', 'WXAPPLET20200307231803s4TDV',
                    'WXAPPLET20200306202653pzAio', 'WXAPPLET20200307104510qT7vY', 'WXAPPLET20200308123457Ddexh',
                    'WXAPPLET20200305220704xGa1C', 'WXAPPLET20200305071332rWFFO', 'WXAPPLET20200308180318gBH7g',
                    'WXAPPLET20200305182359gOTPy', 'WXAPPLET20200308152109GVe5g', 'WXAPPLET20200307165333JF9vW',
                    'WXAPPLET20200306223830jrZeI', 'WXAPPLET20200308120019e8Z85', 'WXAPPLET20200308113709yAI4X',
                    'WXAPPLET20200307020621Hor2o', 'WXAPPLET20200307102013AWIoG', 'WXAPPLET20200308160621O5090',
                    'WXAPPLET20200307150314937JW', 'WXAPPLET20200305205343KZsbO', 'WXAPPLET20200306205040MioIg',
                    'WXAPPLET20200306175747aw8XA', 'WXAPPLET20200306193145tb8s6', 'WXAPPLET20200307145041NFqTR',
                    'WXAPPLET20200307231838eEvJR', 'WXAPPLET20200306190604XdaNC', 'WXAPPLET20200307174452FhO9S',
                    'WXAPPLET20200306222822xXaZe', 'WXAPPLET20200305143309zgwjg', 'WXAPPLET20200306025725vVvUp',
                    'WXAPPLET20200306220411cjzX5', 'WXAPPLET20200307143226S5mZf', 'WXAPPLET20200307181611dYBQr',
                    'WXAPPLET20200306225447Y7XhQ', 'WXAPPLET202003061736335PFDV',
                    'WXAPPLET20200304204547mYB1t', 'WXAPPLET20200304214605gotpJ', 'WXAPPLET20200304220801dpxJn', 'WXAPPLET202003042246495E4gA', 'WXAPPLET20200304225458KOL3L', 'WXAPPLET20200304233622Ae67H', 'WXAPPLET20200304235227hziNu', 'WXAPPLET20200305003009dVADO', 'WXAPPLET20200305003943q5Ueh', 'WXAPPLET20200305071021TLbEG', 'WXAPPLET20200305071332rWFFO', 'WXAPPLET20200305081105BH6QA', 'WXAPPLET20200305090315hzEar', 'WXAPPLET20200305090429YRynr', 'WXAPPLET20200305093719krdKr', 'WXAPPLET20200305100040RgkB9', 'WXAPPLET20200305100320DPYiE', 'WXAPPLET20200305101225J1Uxk', 'WXAPPLET20200305101725dGu8L', 'WXAPPLET20200305103314hAVQT', 'WXAPPLET20200305110325LoK0q', 'WXAPPLET20200305112022nb107', 'WXAPPLET20200305112230ZDGUh', 'WXAPPLET20200305112609IaoR2', 'WXAPPLET20200305120021KYaza', 'WXAPPLET20200305120838T3Yot', 'WXAPPLET20200305121711nl1Dd', 'WXAPPLET20200305122818xGk1R', 'WXAPPLET20200305123804v73YZ', 'WXAPPLET20200305131002hFpfx', 'WXAPPLET20200305131257pX0Dx', 'WXAPPLET20200305131849UyPvT', 'WXAPPLET20200305135432j8eMN', 'WXAPPLET20200305135902FOFB3', 'WXAPPLET202003051401233XlW5', 'WXAPPLET20200305140329ZEpCU', 'WXAPPLET20200305141317sZRTD', 'WXAPPLET202003051414359l6Zp', 'WXAPPLET20200305141741qUFX0', 'WXAPPLET202003051422264Ahh0', 'WXAPPLET20200305142400k3Q1P', 'WXAPPLET20200305142515Xg8wA', 'WXAPPLET20200305143309zgwjg', 'WXAPPLET20200305144837adlIL', 'WXAPPLET20200305145341P2rFX', 'WXAPPLET20200305150251DaNmN', 'WXAPPLET202003051506109AOru', 'WXAPPLET20200305151330motea', 'WXAPPLET2020030515355220tUX', 'WXAPPLET20200305154009Tigfl', 'WXAPPLET20200305154032G9OcU', 'WXAPPLET20200305154125SOipj', 'WXAPPLET20200305154430SV52h', 'WXAPPLET20200305154754B7du3', 'WXAPPLET20200305160750EapkA', 'WXAPPLET20200305161500RbvkW', 'WXAPPLET20200305161911sLJe0', 'WXAPPLET20200305162610fVK8A', 'WXAPPLET20200305162644uMXkO', 'WXAPPLET20200305163251AQMOz', 'WXAPPLET20200305164030Vmw9D', 'WXAPPLET20200305165841FbcLB', 'WXAPPLET20200305165859GqP9b', 'WXAPPLET202003051701583oQkm', 'WXAPPLET20200305171014oSndK', 'WXAPPLET2020030517135242Z5c', 'WXAPPLET20200305172329bxrU9', 'WXAPPLET20200305172518VWJdM', 'WXAPPLET20200305172717dkjg1', 'WXAPPLET20200305172956JXZI0', 'WXAPPLET20200305173024tigUq', 'WXAPPLET20200305173535Gk0Kr', 'WXAPPLET20200305173906XlHmv', 'WXAPPLET20200305174130hu1yJ', 'WXAPPLET20200305180008Olq5l', 'WXAPPLET20200305180535adZ2r', 'WXAPPLET20200305180749tOdJf', 'WXAPPLET202003051807598qyKT', 'WXAPPLET20200305181525MdDV4', 'WXAPPLET20200305181525qpS8H', 'WXAPPLET20200305181837TwFZf', 'WXAPPLET20200305182359gOTPy', 'WXAPPLET2020030518320784y0O', 'WXAPPLET20200305183522fUOfo', 'WXAPPLET20200305183701sekVZ', 'WXAPPLET20200305184051NYWoM', 'WXAPPLET20200305184709dqxNk', 'WXAPPLET20200305185501gZFUx', 'WXAPPLET202003051856428pHTn', 'WXAPPLET20200305190011czFlS', 'WXAPPLET20200305190609nchV0', 'WXAPPLET20200305194119LNQRr', 'WXAPPLET20200305194501DCwdO', 'WXAPPLET20200305194737sLBJZ', 'WXAPPLET20200305200148Y3wMI', 'WXAPPLET20200305201218mgHbM', 'WXAPPLET20200305201401ufVb7', 'WXAPPLET20200305202059KQZan', 'WXAPPLET20200305202426JDA9D', 'WXAPPLET20200305204047Ybxfk', 'WXAPPLET20200305205343KZsbO', 'WXAPPLET202003052103353EGy1', 'WXAPPLET20200305210744uT26d', 'WXAPPLET202003052150305VbKb', 'WXAPPLET20200305220704xGa1C', 'WXAPPLET20200305233707MygsG', 'WXAPPLET20200306024238MlUPo', 'WXAPPLET20200306025725vVvUp', 'WXAPPLET20200306033238ifii3', 'WXAPPLET20200306052416EayBr', 'WXAPPLET20200306052936IJDhZ', 'WXAPPLET20200306064418pjryI', 'WXAPPLET202003060649246lHc6', 'WXAPPLET202003060725209lffH', 'WXAPPLET20200306073055e8pZM', 'WXAPPLET20200306073351J8qiQ', 'WXAPPLET20200306083738eEGid', 'WXAPPLET20200306084212Y283i', 'WXAPPLET20200306084600oAGmN', 'WXAPPLET20200306085106V6nq4', 'WXAPPLET20200306085305QTsUD', 'WXAPPLET202003060908330SuNI', 'WXAPPLET202003060911235UsX3', 'WXAPPLET20200306092659OXd8w', 'WXAPPLET20200306094417bS7dy', 'WXAPPLET20200306094701oNsfo', 'WXAPPLET20200306100304OICqa', 'WXAPPLET20200306100712OrvHn', 'WXAPPLET20200306101053j7mlN', 'WXAPPLET202003061017496jfx4', 'WXAPPLET20200306101952kVgkp', 'WXAPPLET202003061022584ZzCz', 'WXAPPLET20200306102553MubIu', 'WXAPPLET20200306104233EDMOb', 'WXAPPLET20200306104421go7ik', 'WXAPPLET20200306104745wpgpA', 'WXAPPLET20200306105339jFTd1', 'WXAPPLET20200306110530UmEFU', 'WXAPPLET20200306110633XqlFE', 'WXAPPLET20200306112914Z5l38', 'WXAPPLET20200306113327GwK3t', 'WXAPPLET20200306113709F3zbk', 'WXAPPLET20200306114706sKzi1', 'WXAPPLET20200306114917CDypW', 'WXAPPLET2020030611544973Oic', 'WXAPPLET20200306123440IX4Ba', 'WXAPPLET20200306124207C7Tav', 'WXAPPLET202003061243272mV0p', 'WXAPPLET20200306124737DmnB2', 'WXAPPLET20200306125945yUu9Y', 'WXAPPLET20200306130359a2xWb', 'WXAPPLET20200306130702EfuHo', 'WXAPPLET20200306131201ZcL9f', 'WXAPPLET20200306132959WVgVB', 'WXAPPLET20200306141744m6m2j', 'WXAPPLET20200306141956YZH0X', 'WXAPPLET20200306142127mHpKI', 'WXAPPLET20200306143640PaznO', 'WXAPPLET20200306143901FBjWt', 'WXAPPLET20200306151642Vl4sx', 'WXAPPLET20200306152012uu81n', 'WXAPPLET20200306152328Tj7FH', 'WXAPPLET20200306153744TVsz5', 'WXAPPLET20200306155134lKOLq', 'WXAPPLET20200306161943arlA7', 'WXAPPLET20200306162926JK20I', 'WXAPPLET20200306163718SW6F4', 'WXAPPLET20200306163848gnML1', 'WXAPPLET20200306164600rl4ou', 'WXAPPLET20200306164947gikkD', 'WXAPPLET20200306165948wmudy', 'WXAPPLET20200306171157fotgE', 'WXAPPLET20200306172405JWsHC', 'WXAPPLET20200306173115Sua5Z', 'WXAPPLET20200306173257B6ute', 'WXAPPLET20200306174500u1O5P', 'WXAPPLET202003061758218UPl9', 'WXAPPLET20200306180328F23Bo', 'WXAPPLET20200306181400cjeHw', 'WXAPPLET20200306181450kJ2VN', 'WXAPPLET202003061820156s8Pq', 'WXAPPLET20200306190545qqOsc', 'WXAPPLET202003061916227Xfw8', 'WXAPPLET20200306191818cCZsp', 'WXAPPLET20200306192310mzsNv', 'WXAPPLET20200306193134F9jk9', 'WXAPPLET20200306193145tb8s6', 'WXAPPLET20200306193429dkenf', 'WXAPPLET20200306193522HDSi3', 'WXAPPLET20200306202250J04qr', 'WXAPPLET20200306202653pzAio', 'WXAPPLET20200306204018UoKFR', 'WXAPPLET20200306204144vME48', 'WXAPPLET20200306204240NVSA9', 'WXAPPLET20200306204433eI6cy', 'WXAPPLET20200306204719fzAf2', 'WXAPPLET20200306205040MioIg', 'WXAPPLET20200306210633ucUF7', 'WXAPPLET20200306211100iy9oC', 'WXAPPLET20200306211252Kw64N', 'WXAPPLET20200306212315vmWGv', 'WXAPPLET20200306212559Dm3Ef', 'WXAPPLET20200306214632EM2PX', 'WXAPPLET20200306220411cjzX5', 'WXAPPLET20200306220631yzUPW', 'WXAPPLET20200306220645IafUY', 'WXAPPLET20200306221510FQUuO', 'WXAPPLET20200306222158NmSFD', 'WXAPPLET20200306222822xXaZe', 'WXAPPLET20200306223213vuBX2', 'WXAPPLET20200306223735sJef1', 'WXAPPLET20200306223830jrZeI', 'WXAPPLET20200306224043vWLMf', 'WXAPPLET20200306225016xCBm1', 'WXAPPLET20200306225447Y7XhQ', 'WXAPPLET20200307000846crkX1', 'WXAPPLET20200307011758lCGfd', 'WXAPPLET202003070206022YDcK', 'WXAPPLET20200307020621Hor2o', 'WXAPPLET20200307074819u5H1y', 'WXAPPLET20200307080158tDhsi', 'WXAPPLET202003070810198XNkY', 'WXAPPLET20200307083512Jjkm8', 'WXAPPLET20200307085922gvkGi', 'WXAPPLET20200307090916K6O1m', 'WXAPPLET202003070915004b6xC', 'WXAPPLET20200307093441M0alu', 'WXAPPLET20200307095211CYyMQ', 'WXAPPLET20200307095314Ae41K', 'WXAPPLET20200307100934zAIvh', 'WXAPPLET20200307101627pXunQ', 'WXAPPLET20200307101702hVfle', 'WXAPPLET20200307101826ayd88', 'WXAPPLET20200307101925YsXSD', 'WXAPPLET20200307102013AWIoG', 'WXAPPLET20200307102101QMYOZ', 'WXAPPLET20200307103551bTndj', 'WXAPPLET20200307104007FnER9', 'WXAPPLET2020030710414439uBC', 'WXAPPLET20200307104256Dis7j', 'WXAPPLET20200307104510qT7vY', 'WXAPPLET20200307105211tFYIf', 'WXAPPLET20200307111144o4xMh', 'WXAPPLET202003071113209RTXK', 'WXAPPLET20200307112100tW15Y', 'WXAPPLET20200307112443n5T8e', 'WXAPPLET202003071129187qR87', 'WXAPPLET202003071134116Zrmd', 'WXAPPLET20200307114212kJ5aN', 'WXAPPLET20200307114800sz7i4', 'WXAPPLET20200307121515bKl7T', 'WXAPPLET20200307121639XVnGX', 'WXAPPLET20200307121644MOHdP', 'WXAPPLET20200307123021RKGLg', 'WXAPPLET20200307130207nuxWl', 'WXAPPLET20200307131712wyUJh', 'WXAPPLET20200307131954pgx13', 'WXAPPLET20200307132607Yby9M', 'WXAPPLET20200307134909VsnOX', 'WXAPPLET20200307135449qlzvB', 'WXAPPLET20200307135541VpPIK', 'WXAPPLET20200307140203veQK2', 'WXAPPLET20200307140617344NI', 'WXAPPLET20200307140841zvFg3', 'WXAPPLET20200307141018JiKL2', 'WXAPPLET20200307141144V5i4W', 'WXAPPLET20200307141604qkI81', 'WXAPPLET20200307142218YDjmA', 'WXAPPLET20200307142352RO4RE', 'WXAPPLET20200307142903dhFYs', 'WXAPPLET20200307143030SiRPx', 'WXAPPLET20200307143226S5mZf', 'WXAPPLET20200307145041NFqTR', 'WXAPPLET20200307145159P87u0', 'WXAPPLET202003071454476PvVt', 'WXAPPLET20200307145901g2odW', 'WXAPPLET20200307145922EKvGJ', 'WXAPPLET20200307150314937JW', 'WXAPPLET20200307150456eyaAp', 'WXAPPLET20200307150546ENbDp', 'WXAPPLET20200307151227OWRwO', 'WXAPPLET20200307151245LFXaZ', 'WXAPPLET20200307152916Pyx8o', 'WXAPPLET20200307152950Nh6hO', 'WXAPPLET20200307153718JXi2e', 'WXAPPLET20200307154310zqz2P', 'WXAPPLET20200307160410we5uz', 'WXAPPLET20200307161753qFhkP', 'WXAPPLET20200307162057CA2K5', 'WXAPPLET20200307162434Ze0Zz', 'WXAPPLET20200307162544EYgOu', 'WXAPPLET20200307163353DEtYc', 'WXAPPLET20200307172322VPUjJ', 'WXAPPLET20200307172850WuG1p', 'WXAPPLET20200307173958Vd5Wj', 'WXAPPLET20200307174452FhO9S', 'WXAPPLET20200307175930mRH9j', 'WXAPPLET20200307180544yrCoi', 'WXAPPLET20200307180728xtppI', 'WXAPPLET202003071817548iqfx', 'WXAPPLET2020030718472549HcA'
                ];
                if (in_array($order->order_id, $arr_orders)) {
                    return $this->getResponse('确认收货成功！');
                }

                //小于200 加成长值 分佣
                //加成长值
                //得到该用户成长值数据
                $obj_data_groth_value = $obj_growth_user_value->where('app_id', $res->app_id)->first();
                //存在该用户数据则累加成长值 否则新建
                if (!empty($obj_data_groth_value)) {
                    $obj_data_groth_value->growth += $can_active;
                    $obj_data_groth_value->save();
                } else {
                    $obj_growth_user_value->app_id = $res->app_id;
                    $obj_growth_user_value->growth = $can_active;
                    $obj_growth_user_value->save();
                }
                //增加该用户的成长值变化记录
                $obj_growth_user_value_change->app_id = $res->app_id;
                $obj_growth_user_value_change->growth_value_before = empty($obj_data_groth_value->growth) ? 0 : $obj_data_groth_value->growth - $can_active; #变化前
                $obj_growth_user_value_change->growth_value = $can_active;                                                                                   #变化值
                $obj_growth_user_value_change->growth_value_after = empty($obj_data_groth_value->growth) ? $can_active : $obj_data_groth_value->growth;      #变化后
                $obj_growth_user_value_change->title = '商品' . time() . $res->good_id;
                $obj_growth_user_value_change->from_type = 5; #购买vip商品直接加成长值为5
                $obj_growth_user_value_change->get_time = strtotime($res->created_at);
                $obj_growth_user_value_change->status = $res->status;
                $obj_growth_user_value_change->save();

                //处理普通用户才升级
                if ($int_user_groupid == 10) {
                    //用户成长值若大于100则升级
                    $growth = $obj_growth_user_value->where('app_id', $res->app_id)->value('growth');
                    if ($growth >= 100) {
                        $obj_change_vip_service->installOrder($res->app_id, 2, '通过成长值大于100升级超级用户');
                        $obj_change_vip_service->upgradeGroup($res->app_id);
                        $obj_change_vip_service->installGrowthOrder($res->app_id, 1);
                        $obj_change_vip_service->updateGrowthUser($res->app_id);
                    }
                }
            }

//            if ($res->id < 3428 && ($shopIndex->isVipGoods($res->good_id))) {
//                $order_alipay = $rechargeOrder->getOrdersById($order->order_id);
//                if ($order_alipay && $order_alipay->status == 1) {
//                    $arr = [
//                        'uid' => $order_alipay->uid,
//                        'money' => '800',
//                        'orderid' => $order->order_id,
//                    ];
//                    $rechargeUserLevel->initOrder($arr);
//                    $rechargeUserLevel->updateExt();
//                    $rechargeUserLevel->returnCommission();//分佣
//                    $rechargeOrder->updateOrderStatus($order->order_id);
//                }
//            }

            DB::commit();
            return $this->getResponse('确认收货成功！');

        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
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
