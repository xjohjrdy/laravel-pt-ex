<?php

namespace App\Services\Shop;

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
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\ShopOrdersOne;
use App\Entitys\App\ShopSupplierGoodsArea;
use App\Entitys\App\ShopVipBuy;
use App\Entitys\App\TaobaoUser;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\UpgradeVip\ChangeVipService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class Order
{
    protected $shopGoods;
    protected $shopOrders;
    protected $shopOrdersOne;
    protected $shopCarts;
    protected $shopAddress;
    protected $adUserInfo;
    protected $userAccount;
    protected $userCreditLog;
    protected $userAboutLog;
    protected $shopOrdersMaid;
    protected $appUserInfo;
    protected $rechargeOrder;
    protected $creditLog;
    protected $aboutLog;
    protected $rechargeUserLevel;
    protected $pretendShopOrdersMaid;
    protected $shopIndex;
    protected $shopSupplierGoodsArea;

    public function __construct(ShopGoods $shopGoods, ShopSupplierGoodsArea $shopSupplierGoodsArea, ShopIndex $shopIndex, PretendShopOrdersMaid $pretendShopOrdersMaid, RechargeUserLevel $rechargeUserLevel, UserCreditLog $creditLog, UserAboutLog $aboutLog, RechargeOrder $rechargeOrder, ShopOrders $shopOrders, ShopOrdersOne $shopOrdersOne, ShopCarts $shopCarts, ShopAddress $shopAddress, AdUserInfo $adUserInfo, UserAccount $userAccount, UserCreditLog $userCreditLog, UserAboutLog $userAboutLog, ShopOrdersMaid $shopOrdersMaid, AppUserInfo $appUserInfo)
    {
        $this->shopCarts = $shopCarts;
        $this->shopGoods = $shopGoods;
        $this->shopOrders = $shopOrders;
        $this->shopOrdersOne = $shopOrdersOne;
        $this->shopAddress = $shopAddress;
        $this->adUserInfo = $adUserInfo;
        $this->userAccount = $userAccount;
        $this->userCreditLog = $userCreditLog;
        $this->userAboutLog = $userAboutLog;
        $this->shopOrdersMaid = $shopOrdersMaid;
        $this->appUserInfo = $appUserInfo;
        $this->rechargeOrder = $rechargeOrder;
        $this->creditLog = $creditLog;
        $this->aboutLog = $aboutLog;
        $this->rechargeUserLevel = $rechargeUserLevel;
        $this->pretendShopOrdersMaid = $pretendShopOrdersMaid;
        $this->shopIndex = $shopIndex;
        $this->shopSupplierGoodsArea = $shopSupplierGoodsArea;
    }

    /**
     * ??????????????????
     * @param $app_id
     * @param $arr
     * @param $isApplet 0 ?????????????????????????????? 1 ???????????????
     * @return array
     * @throws ApiException
     */
    public function generateOrder($app_id, $arr, $isApplet = 0)
    {
        $address_id = 0;
        $all_price = 0;
        $all_profit_value = 0;
        $is_need_vip = 0;
        $address = $this->shopAddress->getUserDefaultAddress($app_id);
        $user_for_uid = $this->adUserInfo->appToAdUserId($app_id);
        if ($user_for_uid && $user_for_uid->groupid >= 23) {
            $is_need_vip = 1;
        }
        if ($address) {
            $address_id = $address->id;
        }
        foreach ($arr as $k => $v) {
            if ($v['number'] <= 0) {
                throw new ApiException('???????????????????????????????????????????????????', '4011');
            }
            $good = $this->shopGoods->getOneById($v['good_id']);
            if ($good) {
                if ($is_need_vip) {
                    $good->price = $good->vip_price;
                }
                if ($good->volume <= 0) {
                    throw new ApiException('?????????????????????????????????????????????????????????', '4009');
                }
                if ($good->volume < $v['number']) {
                    throw new ApiException('???????????????????????????????????????????????????', '4015');
                }

                if ($this->shopIndex->isVipGoods($v['good_id']) && @$user_for_uid->groupid >= 23) {
                    throw new ApiException('?????????????????????????????????????????????', '4019');
                }
                if ($v['good_id'] == 102) {
                    $count_order = $this->shopOrdersOne->getOrderOneByAppIdAndGoodId($app_id, $v['good_id']);
                    if ($count_order) {
                        throw new ApiException('?????????????????????????????????????????????????????????????????????', '4010');
                    }
                }
                $all_price = $all_price + $good->price * $v['number'];
                $all_profit_value = $all_profit_value + $good->profit_value * $v['number'];
            }
        }
        $model_oreders = $this->shopOrders->addOrders($app_id, $address_id, $all_price, $all_profit_value, $isApplet);
        foreach ($arr as $k => $v) {
            $good = $this->shopGoods->getOneById($v['good_id']);
            if (!$good) {
                throw new ApiException('??????????????????????????????????????????', '4004');
            }
            if ($good && $is_need_vip) {
                $good->price = $good->vip_price;
            }
            $model_order_one[$k] = $this->shopOrdersOne->addOrdersOne($app_id, $model_oreders->id, $v['good_id'], $v['shop_id'], $v['desc'], $v['number'], $good->price, $good->profit_value);
            $model_order_one[$k]->title = $good->title;
            $model_order_one[$k]->header_img = json_decode($good->header_img);
        }
        $all_express = $this->noArea($address_id, $model_oreders->id);
        $userModel = new TaobaoUser();
        $account_money = $userModel->getUserMoney($app_id);
        $deduct_money = 0;
        if ($all_price >= $account_money) {
            $deduct_money = $account_money;
        } else {
            $deduct_money = $all_price;
        }
//        $user = $this->adUserInfo->appToAdUserId($app_id);
//        $userAccount = $this->userAccount->getUserAccount($user->uid);
//
//        if ($all_price * 10 >= $userAccount->extcredits4) {
//            $deduct_ptb = $userAccount->extcredits4;
//        } else {
//            $deduct_ptb = $all_price * 10;
//        }

        return [
            'address' => $address,
            'order_id' => $model_oreders->id,
            'all_express' => (string) $all_express,
            'all_price' => (string)$all_price,
            'account_ptb' => '0',
            'deduct_ptb' => '0',
            'account_money' => (string) $account_money,
            'deduct_money' => (string) $deduct_money,
            'order_detail' => $model_order_one
        ];
    }

    /**
     * ????????????????????????????????????
     * @param $uid
     * @param $price
     * @param $order_id
     * @return bool
     */
    public function installOrder($uid, $price, $order_id)
    {
        $arrOrderParam = array(
            'orderid' => $order_id,
            'status' => '1',
            'uid' => $uid,
            'groupid' => 23,
            'amount' => 9999,
            'price' => $price,
            'desc' => '????????????-??????',
            'submitdate' => time(),
            'confirmdate' => time(),
            'a' => '',
            'b' => '',
            'c' => '',
            'd' => 0,
            'e' => 0,
        );
        $res = $this->rechargeOrder->insert($arrOrderParam);

        return $res;
    }

    /**
     * ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????
     * @param $order_id ?????????id
     * @return int
     */
    public function processOrder($order_id)
    {
        try {
            $order = $this->shopOrders->getByOrderId($order_id);
            $order_one = $this->shopOrdersOne->getAllGoods($order->id);

            if ($order->status) {
                return 0;
            }
            $order_one->map(function ($model) {
                if ($model->status) {
                    return 0;
                }
                $this->shopGoods->where(['id' => $model->good_id])->update(['sale_volume' => DB::raw("sale_volume + " . $model->number)]);
                $this->shopGoods->increaseSaleNumber($model->good_id, $model->number);
                $this->shopCarts->where(['good_id' => $model->good_id, 'desc' => $model->desc])->delete();
                $this->shopGoods->where(['id' => $model->good_id])->update(['volume' => DB::raw("volume - " . $model->number)]);
                @Redis::publish('msg_shop_orders', '{"code":200,"data":{"good_id":"' . $model->good_id . '","desc":"' . $model->desc . '","number":"' . $model->number . '"}}');
            });


            $this->shopOrders->updateStatusOrders($order->id, 1);
            $this->shopOrdersOne->updateStatusByOrderId($order->id, 1);
            if ($order->type == 3) {
                $user = $this->adUserInfo->appToAdUserId($order->app_id);
                $account = $this->userAccount->getUserAccount($user->uid);
                $ptb_number = $order->ptb_number;
                $extcredits4_change = $account->extcredits4 - $ptb_number;
                if ($extcredits4_change < 0) {
                    Storage::disk('local')->append('callback_document/this_is_no_happen_error.txt', var_export($order->id . '/', true));
                    return 1;
                }
                $this->userAccount->subtractPTBMoney($ptb_number, $user->uid);
                $insert_id = $this->creditLog->addLog($user->uid, "SHX", ['extcredits4' => -$ptb_number]);
                $this->aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
            }
            $shop_orders_one = $this->shopOrdersOne->getAllGoods($order->id);
            foreach ($shop_orders_one as $k => $item) {
                $obj_shop_vip_buy = new ShopVipBuy();
                $obj_growth_user_value = new GrowthUserValue();
                $obj_change_vip_service = new ChangeVipService();
                $obj_growth_user_value_change = new GrowthUserValueChange();
                $obj_ad_user_info = new AdUserInfo();
                $obj_growth_user_value_Config = new GrowthUserValueConfig();

                //vip????????? ???????????????vip??????
                $obj_data_shop_vip = $obj_shop_vip_buy->where('vip_id', $item->good_id)->first();
                //???vip???????????????????????? can_active
                if (!empty($obj_data_shop_vip)) {
                    //?????????vip???????????????????????????
                    $can_active = $obj_data_shop_vip->can_active;
                } else {//?????????vip???????????????
                    $shopGoods = new ShopGoods();
                    $res = $shopGoods->getOneGood($item->good_id);
                    $num_shop_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');
                    $can_active = round($res->profit_value / $num_shop_growth_value, 2);
                }
                //???????????????????????????99999 ?????????????????????
                if ($can_active >= 99999) {
                    //??????????????????????????????
                    $obj_data_groth_value = $obj_growth_user_value->where('app_id', $item->app_id)->first();
                    //??????????????????????????????????????? ????????????
                    if (!empty($obj_data_groth_value)) {
                        $obj_data_groth_value->growth += $can_active;
                        $obj_data_groth_value->save();
                    } else {
                        $obj_growth_user_value->app_id = $item->app_id;
                        $obj_growth_user_value->growth = $can_active;
                        $obj_growth_user_value->save();
                    }
                    //???????????????????????????????????????
                    $obj_growth_user_value_change->app_id = $item->app_id;
                    $obj_growth_user_value_change->growth_value_before = empty($obj_data_groth_value->growth) ? 0 : $obj_data_groth_value->growth - $can_active; #?????????
                    $obj_growth_user_value_change->growth_value = $can_active;                                                                                   #?????????
                    $obj_growth_user_value_change->growth_value_after = empty($obj_data_groth_value->growth) ? $can_active : $obj_data_groth_value->growth;      #?????????
                    $obj_growth_user_value_change->title = '??????' . time() . $item->good_id;
                    $obj_growth_user_value_change->from_type = 5; #??????vip???????????????????????????5
                    $obj_growth_user_value_change->get_time = strtotime($item->created_at);
                    $obj_growth_user_value_change->status = $item->status;
                    $obj_growth_user_value_change->save();

                    //??????????????? ???????????????????????????
                    $int_user_groupid = $obj_ad_user_info->where(['pt_id' => $item->app_id])->value('groupid');
                    if ($int_user_groupid == 10) {
                        //????????????????????????100?????????
                        $growth = $obj_growth_user_value->where('app_id', $item->app_id)->value('growth');
                        if ($growth >= 100) {
                            $obj_change_vip_service->installOrder($item->app_id, 2, '?????????????????????100??????????????????');
                            $obj_change_vip_service->upgradeGroup($item->app_id);
                            $obj_change_vip_service->installGrowthOrder($item->app_id, 1);
                            $obj_change_vip_service->updateGrowthUser($item->app_id);
                        }
                    }
                }
//                    $user_uid = $obj_ad_user_info->getUidById($item->app_id);
//                    $rechargeOrder = new RechargeOrder();
//                    $rechargeUserLevel = $this->rechargeUserLevel;
//                    $order_alipay = $rechargeOrder->getOrdersById($order->order_id);
//                    if ($order_alipay && $order_alipay->status == 1) {
//                        $arr = [
//                            'uid' => $user_uid,
//                            'money' => '800',
//                            'orderid' => $order->order_id,
//                        ];
//                        $rechargeUserLevel->initOrder($arr);
//                        $rechargeUserLevel->updateExt();#????????????
//                        $rechargeUserLevel->returnCommission();#??????
//                        $rechargeUserLevel->handleArticle();#???????????????
//                        $rechargeOrder->updateOrderStatus($order->order_id);
//                    }

                //????????????????????????
//                if ($this->shopIndex->isVipGoods($item->good_id)) {
//                    $rechargeOrder = new RechargeOrder();
//                    $rechargeUserLevel = $this->rechargeUserLevel;
//                    $order_alipay = $rechargeOrder->getOrdersById($order->order_id);
//                    if ($order_alipay && $order_alipay->status == 1) {
//                        $arr = [
//                            'uid' => $order_alipay->uid,
//                            'money' => '800',
//                            'orderid' => $order->order_id,
//                        ];
//                        $rechargeUserLevel->initOrder($arr);
//                        $rechargeUserLevel->updateExt();
//                        $rechargeUserLevel->returnCommission();
//                        $rechargeUserLevel->handleArticle();
//                        $rechargeOrder->updateOrderStatus($order->order_id);
//                    }
//                }
            }

            $app_user = $this->appUserInfo->getUserById($order->app_id);


            foreach ($shop_orders_one as $v) {
                //vip????????? ???????????????vip??????
                $obj_data_shop_vip = $obj_shop_vip_buy->where('vip_id', $v->good_id)->first();
                //?????????vip??????
                if (!empty($obj_data_shop_vip)) {
                    //???????????????
                    $this->returnVipCommissionV2($order->order_id);
                } else {
                    if ($order->all_profit_value <> 0.00) {
                        //????????????
                        $this->newPretendReturnCommission($order->order_id, $order->all_profit_value, $app_user->parent_id);
                    }
                }
            }
//            if ($order->all_profit_value <> 0.00) {
//                $this->newPretendReturnCommission($order->order_id, $order->all_profit_value, $app_user->parent_id);
//            }
            $user_info = $this->adUserInfo->where('pt_id', $order->app_id)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (!empty($user_info) && $order->type <> 1) {
                $this->userCreditLog->addLog($user_info->uid, "APS", ['extcredits1' => $order->real_price]);
            }
        } catch (\Exception $e) {
            Storage::disk('local')->append('callback_document/test_alipay_notify_shop_error.txt', var_export($e->getLine() . '/' . $e->getMessage(), true));
        }

        return 1;
    }


    /**
     * ????????????????????????
     * ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????
     * @param $order_id ?????????id
     * @param $from  ?????? 0 ????????? 1 ?????????
     * @return int
     */
    public function processOrderV1($order_id, $from = 0)
    {
        try {
            $order = $this->shopOrders->getByOrderId($order_id);
            $order_one = $this->shopOrdersOne->getAllGoods($order->id);

            if ($order->status) {
                return 0;
            }
            $order_one->map(function ($model) {
                if ($model->status) {
                    return 0;
                }
                $this->shopGoods->where(['id' => $model->good_id])->update(['sale_volume' => DB::raw("sale_volume + " . $model->number)]);
                $this->shopGoods->increaseSaleNumber($model->good_id, $model->number);
                $this->shopCarts->where(['good_id' => $model->good_id, 'desc' => $model->desc])->delete();
                $this->shopGoods->where(['id' => $model->good_id])->update(['volume' => DB::raw("volume - " . $model->number)]);
                @Redis::publish('msg_shop_orders', '{"code":200,"data":{"good_id":"' . $model->good_id . '","desc":"' . $model->desc . '","number":"' . $model->number . '"}}');
            });


            $this->shopOrders->updateStatusOrders($order->id, 1);
            $this->shopOrdersOne->updateStatusByOrderId($order->id, 1);
            if ($order->type == 3) {
                $userModel = new TaobaoUser();
                $account_money = $userModel->getUserMoney($order->app_id);
                if ($account_money <= 0) {
                    Storage::disk('local')->append('callback_document/this_is_no_happen_error.txt', var_export($order->id . '/', true));
                    return 1;
                }
                $cny = $order->ptb_number / 10;
                $userMoneyService = new UserMoney();
                $from_type = '20002'; // ????????????????????????
                if ($from == 0) {
                    $from_type = '20002';
                }
                if ($from == 1) {
                    $from_type = '20001';
                }
                $userMoneyService->minusCnyAndLog($order->app_id, $cny, $from_type, 'mix');
//                $user = $this->adUserInfo->appToAdUserId($order->app_id);
//                $account = $this->userAccount->getUserAccount($user->uid);
//                $ptb_number = $order->ptb_number;
//                $extcredits4_change = $account->extcredits4 - $ptb_number;
//                if ($extcredits4_change < 0) {
//                    Storage::disk('local')->append('callback_document/this_is_no_happen_error.txt', var_export($order->id . '/', true));
//                    return 1;
//                }
//                $this->userAccount->subtractPTBMoney($ptb_number, $user->uid);
//                $insert_id = $this->creditLog->addLog($user->uid, "SHX", ['extcredits4' => -$ptb_number]);
//                $this->aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
            }
            $shop_orders_one = $this->shopOrdersOne->getAllGoods($order->id);
            foreach ($shop_orders_one as $k => $item) {
                $obj_shop_vip_buy = new ShopVipBuy();
                $obj_growth_user_value = new GrowthUserValue();
                $obj_change_vip_service = new ChangeVipService();
                $obj_growth_user_value_change = new GrowthUserValueChange();
                $obj_ad_user_info = new AdUserInfo();
                $obj_growth_user_value_Config = new GrowthUserValueConfig();

                //vip????????? ???????????????vip??????
                $obj_data_shop_vip = $obj_shop_vip_buy->where('vip_id', $item->good_id)->first();
                //???vip???????????????????????? can_active
                if (!empty($obj_data_shop_vip)) {
                    //?????????vip???????????????????????????
                    $can_active = $obj_data_shop_vip->can_active;
                } else {//?????????vip???????????????
                    $shopGoods = new ShopGoods();
                    $res = $shopGoods->getOneGood($item->good_id);
                    $num_shop_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');
                    $can_active = round($res->profit_value / $num_shop_growth_value, 2);
                }
                //???????????????????????????99999 ?????????????????????
                if ($can_active >= 99999) {
                    //??????????????????????????????
                    $obj_data_groth_value = $obj_growth_user_value->where('app_id', $item->app_id)->first();
                    //??????????????????????????????????????? ????????????
                    if (!empty($obj_data_groth_value)) {
                        $obj_data_groth_value->growth += $can_active;
                        $obj_data_groth_value->save();
                    } else {
                        $obj_growth_user_value->app_id = $item->app_id;
                        $obj_growth_user_value->growth = $can_active;
                        $obj_growth_user_value->save();
                    }
                    //???????????????????????????????????????
                    $obj_growth_user_value_change->app_id = $item->app_id;
                    $obj_growth_user_value_change->growth_value_before = empty($obj_data_groth_value->growth) ? 0 : $obj_data_groth_value->growth - $can_active; #?????????
                    $obj_growth_user_value_change->growth_value = $can_active;                                                                                   #?????????
                    $obj_growth_user_value_change->growth_value_after = empty($obj_data_groth_value->growth) ? $can_active : $obj_data_groth_value->growth;      #?????????
                    $obj_growth_user_value_change->title = '??????' . time() . $item->good_id;
                    $obj_growth_user_value_change->from_type = 5; #??????vip???????????????????????????5
                    $obj_growth_user_value_change->get_time = strtotime($item->created_at);
                    $obj_growth_user_value_change->status = $item->status;
                    $obj_growth_user_value_change->save();

                    //??????????????? ???????????????????????????
                    $int_user_groupid = $obj_ad_user_info->where(['pt_id' => $item->app_id])->value('groupid');
                    if ($int_user_groupid == 10) {
                        //????????????????????????100?????????
                        $growth = $obj_growth_user_value->where('app_id', $item->app_id)->value('growth');
                        if ($growth >= 100) {
                            $obj_change_vip_service->installOrder($item->app_id, 2, '?????????????????????100??????????????????');
                            $obj_change_vip_service->upgradeGroup($item->app_id);
                            $obj_change_vip_service->installGrowthOrder($item->app_id, 1);
                            $obj_change_vip_service->updateGrowthUser($item->app_id);
                        }
                    }
                }
//                    $user_uid = $obj_ad_user_info->getUidById($item->app_id);
//                    $rechargeOrder = new RechargeOrder();
//                    $rechargeUserLevel = $this->rechargeUserLevel;
//                    $order_alipay = $rechargeOrder->getOrdersById($order->order_id);
//                    if ($order_alipay && $order_alipay->status == 1) {
//                        $arr = [
//                            'uid' => $user_uid,
//                            'money' => '800',
//                            'orderid' => $order->order_id,
//                        ];
//                        $rechargeUserLevel->initOrder($arr);
//                        $rechargeUserLevel->updateExt();#????????????
//                        $rechargeUserLevel->returnCommission();#??????
//                        $rechargeUserLevel->handleArticle();#???????????????
//                        $rechargeOrder->updateOrderStatus($order->order_id);
//                    }

                //????????????????????????
//                if ($this->shopIndex->isVipGoods($item->good_id)) {
//                    $rechargeOrder = new RechargeOrder();
//                    $rechargeUserLevel = $this->rechargeUserLevel;
//                    $order_alipay = $rechargeOrder->getOrdersById($order->order_id);
//                    if ($order_alipay && $order_alipay->status == 1) {
//                        $arr = [
//                            'uid' => $order_alipay->uid,
//                            'money' => '800',
//                            'orderid' => $order->order_id,
//                        ];
//                        $rechargeUserLevel->initOrder($arr);
//                        $rechargeUserLevel->updateExt();
//                        $rechargeUserLevel->returnCommission();
//                        $rechargeUserLevel->handleArticle();
//                        $rechargeOrder->updateOrderStatus($order->order_id);
//                    }
//                }
            }

            $app_user = $this->appUserInfo->getUserById($order->app_id);


            foreach ($shop_orders_one as $v) {
                //vip????????? ???????????????vip??????
                $obj_data_shop_vip = $obj_shop_vip_buy->where('vip_id', $v->good_id)->first();
                //?????????vip??????
                if (!empty($obj_data_shop_vip)) {
                    //???????????????
                    $this->returnVipCommissionV2($order->order_id);
                } else {
                    if ($order->all_profit_value <> 0.00) {
                        //????????????
                        $this->newPretendReturnCommission($order->order_id, $order->all_profit_value, $app_user->parent_id, $v->good_id);
                    }
                }
            }
//            if ($order->all_profit_value <> 0.00) {
//                $this->newPretendReturnCommission($order->order_id, $order->all_profit_value, $app_user->parent_id);
//            }
            $user_info = $this->adUserInfo->where('pt_id', $order->app_id)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (!empty($user_info) && $order->type <> 1) {
                $this->userCreditLog->addLog($user_info->uid, "APS", ['extcredits1' => $order->real_price]);
            }
        } catch (\Exception $e) {
            Storage::disk('local')->append('callback_document/test_alipay_notify_shop_error.txt', var_export($e->getLine() . '/' . $e->getMessage(), true));
        }

        return 1;
    }

    /*
     * vip?????????????????????
     */
    public function returnVipCommissionV2($orderid)
    {
        //???????????????vip?????? ?????????????????????
        $obj_shop_orsers = new ShopOrders();
        $obj_shop_orsers_one = new ShopOrdersOne();
        $obj_shop_vip_buy = new ShopVipBuy();
        $order = $obj_shop_orsers->where('order_id', $orderid)->first();
        $order_one = $obj_shop_orsers_one->where('order_id', $order->id)->first();
        $maid = $obj_shop_vip_buy->where('vip_id', $order_one->good_id)->value('maid');

        $obj_ad = new AdUserInfo();
        $user_uid = $obj_ad->getUidById($order_one->app_id);
        $uid = $user_uid;
        $obj_ad_info = AdUserInfo::where(['uid' => $uid])->first();
        if (empty($obj_ad_info)) {
            return false;
        }
        $due_ptb = 0;
        $count_partner = 0;
        $tmp_next_id = $obj_ad_info->pt_pid;
        for ($i = 0; $i < 50; $i++) {
            if (empty($tmp_next_id)) {
                return false;
            }
            $parent_info = $this->getParentInfo($tmp_next_id);
            if (empty($parent_info)) {
                return false;
            }
            $p_groupid = $parent_info['groupid'];
            $p_pt_pid = $parent_info['pt_pid'];
            $p_pt_id = $parent_info['pt_id'];

            $tmp_next_id = $p_pt_pid;

            if ($i == 0) {
                if ($p_groupid == 23) {
                    $due_ptb = 0.56 * $maid * 10;
                } elseif ($p_groupid == 24) {
                    $due_ptb = 0.67 * $maid * 10;
                } elseif ($p_groupid == 10) {
                    $due_ptb = 0.05 * $maid * 10;
                }
            } else {
                break;
//                if ($p_groupid != 24) {
//                    continue;
//                }
//                if ($count_partner == 0) {
//                    $due_ptb = 0.3 * $maid * 10;
//                } else {
//                    $due_ptb = 0.11 * $maid * 10;
//                }
            }
            if (empty($due_ptb)) {
                continue;
            }
            if ($p_groupid == 24) {
                $count_partner += 1;
            }
            if ($this->pretendShopOrdersMaid->where(['app_id' => $p_pt_id, 'order_id' => $orderid])->exists()) {
                Log::warning('???????????????????????????', [$p_pt_id => $orderid]);
                continue;
            }
            $this->pretendShopOrdersMaid->addMaidLog($p_pt_id, $orderid, $due_ptb);
            if ($count_partner >= 2) {
                break;
            }
        }
        return true;
    }

    public function getParentInfo($ptPid)
    {
        $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if (empty($parentInfo)) {
            return false;
        }

        return $parentInfo->toArray();

    }

    /**
     * ??????????????????????????????
     */
    public function newPretendReturnCommission($order_id, $commission, $ptPid, $goods_id = 0)
    {
        $commission = $commission * 0.41;
        $signBool = false;
        $signOk = false;
        $specialBool = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (empty($parentInfo)) {
                break;
            }
            $parentInfo = $parentInfo->toArray();
            $ptPid = $parentInfo['pt_pid'];
            $commission_percent = 6;

            if ($i > 0) {
                break;
//                $commission_percent *= 0.5;
//                if ($parentInfo['groupid'] != 24) {
//                    continue;
//                }
//
//                if ($signBool) {
//                    $commission_percent *= 0.5;
//                    $signOk = true;
//                } else {
//                    $signBool = true;
//                }
//
//                if ($specialBool) {
//                    $commission_percent *= 0.5;
//                }

            } else {
                if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
//                    $commission_percent *= 0.5;
                    $shopGoods = new ShopGoods();
                    $res_good = $shopGoods->getGoodData($goods_id);
                    if (!(@$res_good->can_active > 0)) {
                        $commission_percent *= 0.5;
                    } else {
                        //???????????? ???????????? ??????????????????????????? ???????????????*0.05 ?????????????????????
                        $commission = 1;
                        $commission_percent = number_format(@$res_good->profit_value * 0.41 * 0.05 * 10, 2);
                    }
                }
                if ($parentInfo['groupid'] == 24) {
                    $commission_percent *= 1.2;
                    $specialBool = true;
                    $signBool = true;
                }
            }
            if ($this->pretendShopOrdersMaid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Log::warning('???????????????????????????', [$parentInfo['pt_id'] => $order_id]);
                continue;
            }
            $commission_result = $commission * $commission_percent;
            $this->pretendShopOrdersMaid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result);


            if ($signOk) {
                break;
            }
        }

        return true;
    }

    /**
     * @param $order_id
     * @param $commission
     * @param $ptPid
     * @return bool
     */
    public function newReturnCommission($order_id, $commission, $ptPid, $goods_id = 0)
    {
        $commission = $commission * 0.41;
        $signBool = false;
        $signOk = false;
//        $specialBool = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (empty($parentInfo)) {
                break;
            }
            $parentInfo = $parentInfo->toArray();
            $ptPid = $parentInfo['pt_pid'];
            $commission_percent = 6;

            if ($i > 0) {
                break;
//                $commission_percent *= 0.5;
//                if ($parentInfo['groupid'] != 24) {
//                    continue;
//                }
//
//                if ($signBool) {
//                    $commission_percent *= 0.5;
//                    $signOk = true;
//                } else {
//                    $signBool = true;
//                }
//
//                if ($specialBool) {
//                    $commission_percent *= 0.5;
//                }

            } else {
                if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                    $shopGoods = new ShopGoods();
                    $res_good = $shopGoods->getGoodData($goods_id);
                    if (!(@$res_good->can_active > 0)) {
                        $commission_percent *= 0.5;
                    } else {
                        //???????????? ???????????? ??????????????????????????? ???????????????*0.05 ?????????????????????
                        $commission = 1;
                        $commission_percent = number_format(@$res_good->profit_value * 0.41 * 0.05 * 10, 2);
                    }
                }
                if ($parentInfo['groupid'] == 24) {
                    $commission_percent *= 1.2;
//                    $specialBool = true;
                    $signBool = true;
                }
            }
            if ($this->shopOrdersMaid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Log::warning('???????????????????????????', [$parentInfo['pt_id'] => $order_id]);
                continue;
            }
            $commission_result = $commission * $commission_percent;
            if ($commission_result > 0 && $commission_result < 1) {
                $commission_result = 1;
            }
            $this->shopOrdersMaid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result);
            $this->pretendShopOrdersMaid->updateStatus($parentInfo['pt_id'], $order_id);

//            $this->userAccount->where('uid', $parentInfo['uid'])->update(['extcredits4' => DB::raw("extcredits4 + " . $commission_result)]);
//            $perentAcount = $this->userAccount->getUserAccount($parentInfo['uid'])->extcredits4;
//            $insert_id = $this->userCreditLog->addLog($parentInfo['uid'], "SPT", ['extcredits4' => $commission_result]);
//            $this->userAboutLog->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $commission_result]);
            //???????????????????????????
            $obj_user_money = new UserMoney();
            $obj_user_money->plusCnyAndLog($parentInfo['pt_id'], $commission_result / 10, 51);

            if ($signOk) {
                break;
            }
        }

        return $signBool;
    }

    /****************= ?????? =****************/
    public function returnCommission($order_id, $commission, $ptPid)
    {
        $commission = $commission * 0.41;
        $signBool = false;
        $signOk = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (empty($parentInfo)) {
                break;
            }
            $parentInfo = $parentInfo->toArray();
            $ptPid = $parentInfo['pt_pid'];
            $commission_percent = 2.8;

            if ($i > 2) {
                if ($parentInfo['groupid'] != 24) {
                    continue;
                }

                if ($signBool) {
                    $commission_percent *= 0.5;
                    $signOk = true;
                } else {
                    $signBool = true;
                }

            } else {
                if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                    $commission_percent *= 0.5;
                }
                if ($parentInfo['groupid'] == 24) {
                    $signBool = true;
                }
            }
            if ($this->shopOrdersMaid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Log::warning('???????????????????????????', [$parentInfo['pt_id'] => $order_id]);
                continue;
            }
            $commission_result = $commission * $commission_percent;
            if ($commission_result > 0 && $commission_result < 1) {
                $commission_result = 1;
            }
            $this->shopOrdersMaid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result);
            $this->pretendShopOrdersMaid->updateStatus($parentInfo['pt_id'], $order_id);
            $this->userAccount->where('uid', $parentInfo['uid'])->update(['extcredits4' => DB::raw("extcredits4 + " . $commission_result)]);
            $perentAcount = $this->userAccount->getUserAccount($parentInfo['uid'])->extcredits4;
            $insert_id = $this->userCreditLog->addLog($parentInfo['uid'], "SPT", ['extcredits4' => $commission_result]);
            $this->userAboutLog->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $commission_result]);

            if ($signOk) {
                break;
            }
        }

        return true;
    }

    /****************= ???????????????????????????????????????????????? =****************/
    public function pretendReturnCommission($order_id, $commission, $ptPid)
    {
        $commission = $commission * 0.41;
        $signBool = false;
        $signOk = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (empty($parentInfo)) {
                break;
            }
            $parentInfo = $parentInfo->toArray();
            $ptPid = $parentInfo['pt_pid'];
            $commission_percent = 2.8;

            if ($i > 2) {
                if ($parentInfo['groupid'] != 24) {
                    continue;
                }

                if ($signBool) {
                    $commission_percent *= 0.5;
                    $signOk = true;
                } else {
                    $signBool = true;
                }

            } else {
                if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                    $commission_percent *= 0.5;
                }
                if ($parentInfo['groupid'] == 24) {
                    $signBool = true;
                }
            }
            if ($this->pretendShopOrdersMaid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Log::warning('???????????????????????????', [$parentInfo['pt_id'] => $order_id]);
                continue;
            }
            $commission_result = $commission * $commission_percent;
            $this->pretendShopOrdersMaid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result);


            if ($signOk) {
                break;
            }
        }

        return true;
    }

    /**
     * ????????????id?????????????????????????????????????????????????????????
     * @param $address_id
     * @param $order_id
     * @return int
     */
    public function noArea($address_id, $order_id)
    {
        $add_express = 0;
        $address = $this->shopAddress->getOneAddress($address_id);
        if (!$address) {
            return $add_express;
        }
        $shop_orders_one = $this->shopOrdersOne->getAllGoods($order_id);
        $one_express = 0;
        foreach ($shop_orders_one as $k => $item) {
            $good_area = $this->shopSupplierGoodsArea->getArea($item->good_id);
            if (empty($good_area)) {
                if (stristr($address->zone, '?????????') ||
                    stristr($address->zone, '??????') ||
                    stristr($address->zone, '??????') ||
                    stristr($address->zone, '??????') ||
                    stristr($address->zone, '??????') ||
                    stristr($address->zone, '?????????')) {
                    $good = $this->shopGoods->getOneById($item->good_id, 0);
                    $good_express = 8;
                    if ($good) {
                        if (empty($good->express) || $good->express == 0.00) {
                            $good_express = 0;
                        } else {
                            $good_express = $good->express;
                        }
                    }
                    $one_express = ((int)($item->number / 5) + 1) * $good_express;
                    $is_have_weight = $good->real_weight * $item->number;
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
                $area = $good_area->area;
                $address = $address->zone;
                $arr_area = explode(',', $area);

                foreach ($arr_area as $one_area) {
                    if (stristr($address, $one_area)) {
                        $good = $this->shopGoods->getOneById($item->good_id, 0);
                        $good_express = 8;
                        if ($good) {
                            if (empty($good->express) || $good->express == 0.00) {
                                $good_express = 0;
                            } else {
                                $good_express = $good->express;
                            }
                        }
                        $one_express = ((int)($item->number / 5) + 1) * $good_express;
                        $is_have_weight = $good->real_weight * $item->number;
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
            $this->shopOrdersOne->noAreaPost($item->id, $one_express);
        }

        return $add_express;
    }

    /**
     * ?????????????????????????????????????????????????????????
     * @param $order_one_id
     * @return int
     */
    public function refundOrderOne($order_one_id)
    {
        $order_one = $this->shopOrdersOne->getOneById($order_one_id);
        $ptb_number = ($order_one->real_price * $order_one->number) * 10 / 2;
        $ptb_number = (int)$ptb_number;
        $user = $this->adUserInfo->appToAdUserId($order_one->app_id);
        $account = $this->userAccount->getUserAccount($user->uid);
        $this->userAccount->addUserPTBMoney($ptb_number, $user->uid);
        $insert_id = $this->creditLog->addLog($user->uid, "SAS", ['extcredits4' => +$ptb_number]);
        $extcredits4_change = $account->extcredits4 + $ptb_number;
        $this->aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);
        return 1;
    }
}
