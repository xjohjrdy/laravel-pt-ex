<?php

namespace App\Services\Recharge;

use App\Entitys\Ad\AdUserFieldForum;
use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\CommissionLog;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\RechargeSetting;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\ShopOrdersOne;
use App\Entitys\App\ShopVipBuy;
use App\Entitys\Article\Agent;
use App\Services\Common\UserMoney;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchaseUserGroup
{
    protected $rechargeSetting;
    protected $adUserInfo;
    protected $rechargeOrder;
    protected $userAccount;
    protected $adUserFieldForum;
    protected $agent;
    protected $commissionLog;
    protected $userCreditLog;
    protected $aboutLog;

    public $tempUserInfo;
    protected $tempRechargeSetting;
    public $timestamp;

    public function __construct(
        RechargeSetting $rechargeSetting,
        AdUserInfo $adUserInfo,
        RechargeOrder $rechargeOrder,
        UserAccount $userAccount,
        AdUserFieldForum $adUserFieldForum,
        Agent $agent,
        CommissionLog $commissionLog,
        UserCreditLog $userCreditLog,
        UserAboutLog $aboutLog
    )
    {
        $this->rechargeSetting = $rechargeSetting;
        $this->adUserInfo = $adUserInfo;
        $this->rechargeOrder = $rechargeOrder;
        $this->userAccount = $userAccount;
        $this->adUserFieldForum = $adUserFieldForum;
        $this->agent = $agent;
        $this->commissionLog = $commissionLog;
        $this->userCreditLog = $userCreditLog;
        $this->aboutLog = $aboutLog;

        $this->timestamp = time();
    }

    public function getRechargeSetting()
    {
        $listRechargeSetting = $this->rechargeSetting->orderBy('displayorder')->get();
        $this->tempRechargeSetting = $listRechargeSetting->toArray();
        return $this->tempRechargeSetting;
    }

    public function getUserCommonMember($uid)
    {
        $singleUserInfo = $this->adUserInfo->where('uid', $uid)->first();
        $this->tempUserInfo = $singleUserInfo->toArray();
        return $this->tempUserInfo;
    }

    /*Array
(
    [uid] => 1
    [email] => admin@admin.com
    [username] => admin
    [password] => Gjdkr883u#$@d37
    [secret] => 6b8805731495a3294b0c64feb9b17154
    [status] => 0
    [emailstatus] => 0
    [avatarstatus] => 0
    [videophotostatus] => 0
    [adminid] => 1
    [groupid] => 24
    [groupexpiry] => 4294967295
    [extgroupids] => 24	24	23	9	10	12	22
    [regdate] => 1501834260
    [credits] => -363910
    [notifysound] => 0
    [timeoffset] =>
    [newpm] => 0
    [newprompt] => 3702
    [accessmasks] => 0
    [allowadmincp] => 1
    [onlyacceptfriendpm] => 0
    [conisbind] => 1
    [freeze] => 0
    [is_bind] => 2
    [pt_id] => 1
    [pt_pid] => 0
    [pt_username] => admin
    [check_code] =>
    [step] => 5
)
*/
    /*
     * 73   10
     * 74   800
     * 75   3000
     * 76   2700
     * 77   2200
     *
     * 3    10  2700
     * 8    10  2200
     * 1    10
     * 0    10  800  3000
     */
    public function isGroupOk($uid, $gid)
    {
        $need = $this->rechargeOrder->getUserType($uid);
        switch ($need) {
            case 3:
                $optional = [73, 76];
                break;
            case 8:
                $optional = [73, 77];
                break;
            case 1:
                $optional = [73];
                break;
            case 0:
                $optional = [73, 74, 75];
                break;
            default:
                return false;
        }
        return in_array($gid, $optional);
    }


    /*
     * Array
(
    [0] => Array
        (
            [id] => 73
            [displayorder] => 1
            [groupid] => 22
            [desc] => 10篇文章/月=10元
            [days] => 31
            [price] => 10
            [rate] => 0.00
        )

    [1] => Array
        (
            [id] => 74
            [displayorder] => 2
            [groupid] => 23
            [desc] => 10篇文章/月=永久
            [days] => 9999
            [price] => 800
            [rate] => 100.00
        )

    [2] => Array
        (
            [id] => 75
            [displayorder] => 3
            [groupid] => 24
            [desc] => 10篇文章/月=永久
            [days] => 9999
            [price] => 3000
            [rate] => 100.00
        )

    [3] => Array
        (
            [id] => 76
            [displayorder] => 5
            [groupid] => 24
            [desc] => 代理商补差价
            [days] => 9999
            [price] => 2700
            [rate] => 100.00
        )

    [4] => Array
        (
            [id] => 77
            [displayorder] => 6
            [groupid] => 24
            [desc] => 代理商补差价
            [days] => 9999
            [price] => 2200
            [rate] => 100.00
        )

)
     */
    public function getUserAccountRMB()
    {
        $uid = $this->tempUserInfo['uid'];
        $userAccountInfo = $this->userAccount->getUserAccount($uid);
        return $userAccountInfo->extcredits3;
    }

    public function getUserAccountPTB($uid)
    {
        $userAccountInfo = $this->userAccount->getUserAccount($uid);
        return $userAccountInfo->extcredits4;
    }

    public function random($length, $numeric = 0)
    {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }

    public function whetherOrder($orderid)
    {
        return $this->rechargeOrder->where('orderid', $orderid)->exists();
    }

    public function addOrder($arrOrderParam)
    {
        return $this->rechargeOrder->insert($arrOrderParam);
    }

    public function updateExtgroupids($extgroupids)
    {
        $uid = $this->tempUserInfo['uid'];
        return $this->adUserInfo->where('uid', $uid)->update(['extgroupids' => $extgroupids]);
    }

    public function getGroupterms()
    {
        $uid = $this->tempUserInfo['uid'];
        $memberfieldforum = $this->adUserFieldForum->where('uid', $uid)->first();
        return $this->dunserialize($memberfieldforum['groupterms']);
    }

    public function dunserialize($data)
    {
        if (($ret = unserialize($data)) === false) {
            $ret = unserialize(stripslashes($data));
        }
        return $ret;
    }

    public function updateGroupterms($arrGroupterms)
    {
        $uid = $this->tempUserInfo['uid'];
        $groupterms = serialize($arrGroupterms);
        return $this->adUserFieldForum->where('uid', $uid)->update(['groupterms' => $groupterms]);
    }

    public function updateUserAccountRMB($extcredits3)
    {
        $uid = $this->tempUserInfo['uid'];
        return $this->userAccount->updateRMBMoney($extcredits3, $uid);
    }

    public function updateCommonMemberGroup($groupid, $groupexpirynew)
    {
        $uid = $this->tempUserInfo['uid'];
        $m = AdUserInfo::where('uid', $uid)->first();
        $m->setKeyName('uid');
        $m->groupid = $groupid;
        $m->groupexpiry = 4070883661;
        $m->save();
    }



    /*****************= wen库 =*****************/

    /*****************= 记录文章 =*****************/
    public function isTimeOk()
    {
        $endTime = strtotime(date('Y-m') . "-1 05:00:00");
        $nowTime = $this->timestamp;
        if ($nowTime > $endTime) {
            return false;
        }
        return true;

    }

    public function getAgentInfo()
    {
        $uid = $this->tempUserInfo['uid'];
        $objAgentInfo = $this->agent->where('uid', $uid)->first();
        return $objAgentInfo ? $objAgentInfo->toArray() : false;
    }

    public function updateAgentInfo($timestamp, $number, $forever)
    {
        $uid = $this->tempUserInfo['uid'];
        if ($forever) {
            $this->agent->where('uid', $uid)->update(['update_time' => $timestamp, 'number' => $number, 'forever' => 1]);
        } else {
            $this->agent->where('uid', $uid)->update(['update_time' => $timestamp, 'number' => $number]);
        }
    }

    public function addAgentInfo($timestamp, $number, $forever)
    {

        $uid = $this->tempUserInfo['uid'];
        $username = $this->tempUserInfo['username'];
        $ptId = $this->tempUserInfo['pt_id'];

        $this->agent->insert([
            'username' => $username,
            'pt_id' => $ptId,
            'uid' => $uid,
            'update_time' => $timestamp,
            'number' => $number,
            'forever' => $forever,
        ]);

    }

    /****************= 返佣 =****************/
    public function returnCommission($orderid, $commission = null)
    {
        if (empty($commission)) {
            $this->rechargeOrder;
        } else {
            $ptPid = $this->tempUserInfo['pt_pid'];
        }
        $signBool = false;
        $signOk = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->getParentInfo($ptPid);
            if (empty($parentInfo)) {
                break;
            }
            $ptPid = $parentInfo['pt_pid'];

            if ($i > 2) {
                if ($parentInfo['groupid'] != 24) {
                    continue;
                }

                if ($signBool) {
                    $commission *= 0.1;
                    $signOk = true;
                } else {
                    $signBool = true;
                }

            } else {
                if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                    continue;
                }
                if ($parentInfo['groupid'] == 24) {
                    $signBool = true;
                }
            }
            if ($this->commissionLog->where(['uid' => $parentInfo['uid'], 'orderid' => $orderid])->exists()) {
                Storage::disk('local')->append('callback_document/again_log.txt', var_export(var_export($parentInfo['uid'] . '--' . $orderid, true), true));
                continue;
            }
            $this->commissionLog->insert([
                'uid' => $parentInfo['uid'],
                'orderid' => $orderid,
                'money' => $commission,
                'dateline' => $this->timestamp
            ]);
            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($parentInfo['pt_id'], $commission / 10, '60');
//            $this->userAccount->where('uid', $parentInfo['uid'])->update(['extcredits4' => DB::raw("extcredits4 + " . $commission)]);
//            $perentAcount = $this->getUserAccountPTB($parentInfo['uid']);
//            $insert_id = $this->userCreditLog->addLog($parentInfo['uid'], "FPT", ['extcredits4' => $commission]);
//            $this->aboutLog->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $commission]);

            if ($signOk) {
                break;
            }
        }

        return true;
    }

    public function returnCommissionV12($orderid, $commission = null)
    {
        if (empty($commission)) {
            $this->rechargeOrder;
        } else {
            $ptPid = $this->tempUserInfo['pt_pid'];
        }
        $signBool = false;
        $signOk = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->getParentInfo($ptPid);
            if (empty($parentInfo)) {
                break;
            }
            $ptPid = $parentInfo['pt_pid'];

            if ($i != 0) {
                if ($parentInfo['groupid'] != 24) {
                    continue;
                }

                if ($signBool) {
                    $commission *= 0.1;
                    $signOk = true;
                } else {
                    $signBool = true;
                }

            } else {
                if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                    continue;
                }
                if ($parentInfo['groupid'] == 24) {
                    $signBool = true;
                }
            }
            if ($this->commissionLog->where(['uid' => $parentInfo['uid'], 'orderid' => $orderid])->exists()) {
                Storage::disk('local')->append('callback_document/again_log.txt', var_export(var_export($parentInfo['uid'] . '--' . $orderid, true), true));
                continue;
            }
            $this->commissionLog->insert([
                'uid' => $parentInfo['uid'],
                'orderid' => $orderid,
                'money' => $commission,
                'dateline' => $this->timestamp
            ]);
            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($parentInfo['pt_id'], $commission / 10, '60');
//            $this->userAccount->where('uid', $parentInfo['uid'])->update(['extcredits4' => DB::raw("extcredits4 + " . $commission)]);
//            $perentAcount = $this->getUserAccountPTB($parentInfo['uid']);
//            $insert_id = $this->userCreditLog->addLog($parentInfo['uid'], "FPT", ['extcredits4' => $commission]);
//            $this->aboutLog->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $commission]);

            if ($signOk) {
                break;
            }
        }

        return true;
    }

    /*
     * 最新返佣方式
     * 代理商或合伙人直推一个代理商奖励20%
     * 合伙人非直推下级，无限代佣金：10%，（避免出现异常，限制最高为50级）
     * 遇到同级佣金为该同级佣金的10%
     *
     * A例子：
        上一级		合伙人	8元
        上一级		无
        上一级		无
        上一级		合伙人	80元
        上一级		代理商	无
        上一级		代理商	160元
        A用户		购买代理商800
     *
     * B例子：
        上一级		合伙人
        上一级		无
        上一级		无
        上一级		合伙人 获得16元
        上一级		代理商	无
        上一级		合伙人 获得160元
        A用户		购买代理商 800
     */
    public function returnCommissionV2($orderid)
    {
        //验证是否为vip商品 是则取除拨出值
        $obj_shop_orsers = new ShopOrders();
        $obj_shop_orsers_one = new ShopOrdersOne();
        $obj_shop_vip_buy = new ShopVipBuy();
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $order = $obj_shop_orsers->where('order_id',$orderid)->first();
        $order_one = $obj_shop_orsers_one->where('order_id',$order->id)->first();
        $maid = $obj_shop_vip_buy->where('vip_id', $order_one->good_id)->value('maid');
        if (empty($maid)) {//非vip商品计算成长值
            $shopGoods = new ShopGoods();
            $res_good = $shopGoods->getOneGood($order_one->good_id);
            $num_shop_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');
            $maid = round($res_good->profit_value / $num_shop_growth_value, 2);

//            Storage::disk('local')->append('callback_document/no_vip_shop.txt', var_export(var_export('该笔订单是非VIP商品,订单order_id='.$orderid.',商品id='.$order_one->good_id, true), true));
//            return $this->getInfoResponse('1001', '异常错误,停止分佣!');
        }

//        $obj_order = new RechargeOrder();
//        $obj_order_info = $obj_order->getOrdersById($orderid);
//        if (empty($obj_order_info)) {
//            return false;
//        }
//        if ($obj_order_info->status != 2) {
//            return false;
//        }
//        $rmb_price = $obj_order_info->price;
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
            $p_uid = $parent_info['uid'];
            $p_groupid = $parent_info['groupid'];
            $p_pt_pid = $parent_info['pt_pid'];
            $p_username = $parent_info['username'];
            $p_pt_id = $parent_info['pt_id'];

            $tmp_next_id = $p_pt_pid;

            if ($i == 0) {

                if ($p_groupid == 23) {
                    $due_ptb = 0.56 * $maid * 10;
//                    $due_ptb = 150 * 10;
                } elseif ($p_groupid == 24) {
                    $due_ptb = 0.67 * $maid * 10;
//                    $due_ptb = 180 * 10;
                } elseif ($p_groupid == 10){
                    $due_ptb = 0.05 * $maid * 10;
                }
            } else {
                if ($p_groupid != 24) {
                    continue;
                }
                if ($count_partner == 0) {
                    $due_ptb = 0.3 * $maid * 10;
//                    $due_ptb = $rmb_price * 0.1 * 10;
                } else {
                    $due_ptb = 0.11 * $maid * 10;
//                    $due_ptb = 30 * 10;
                }
            }

            if (empty($due_ptb)) {
                continue;
            }
            if ($p_groupid == 24) {
                $count_partner += 1;
            }
            if ($this->commissionLog->where(['uid' => $p_uid, 'orderid' => $orderid])->exists()) {
                Storage::disk('local')->append('callback_document/again_log.txt', var_export(var_export($p_uid . '--' . $orderid, true), true));
                continue;
            }
            $this->commissionLog->insert([
                'uid' => $p_uid,
                'orderid' => $orderid,
                'money' => $due_ptb,
                'dateline' => $this->timestamp
            ]);
            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($p_pt_id, $due_ptb / 10, '60');
//            $perentAcount = $this->getUserAccountPTB($p_uid);
//            $this->userAccount->where('uid', $p_uid)->update(['extcredits4' => DB::raw("extcredits4 + " . $due_ptb)]);
//            $insert_id = $this->userCreditLog->addLog($p_uid, "FPT", ['extcredits4' => $due_ptb]);
//            $this->aboutLog->addLog($insert_id, $p_uid, $p_username, $p_pt_id, ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $due_ptb]);

            if ($count_partner >= 2) {
                break;
            }
        }
        $this->externalStatistical($orderid);
        return true;


    }

    public function returnVipCommissionV2($orderid)
    {
        //验证是否为vip商品 是则取除拨出值
        $obj_shop_orsers = new ShopOrders();
        $obj_shop_orsers_one = new ShopOrdersOne();
        $obj_shop_vip_buy = new ShopVipBuy();
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $order = $obj_shop_orsers->where('order_id',$orderid)->first();
        $order_one = $obj_shop_orsers_one->where('order_id',$order->id)->first();
        $maid = $obj_shop_vip_buy->where('vip_id', $order_one->good_id)->value('maid');
        if (empty($maid)) {//非vip商品计算成长值
            $shopGoods = new ShopGoods();
            $res_good = $shopGoods->getOneGood($order_one->good_id);
            $num_shop_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');
            $maid = round($res_good->profit_value / $num_shop_growth_value, 2);

//            Storage::disk('local')->append('callback_document/no_vip_shop.txt', var_export(var_export('该笔订单是非VIP商品,订单order_id='.$orderid.',商品id='.$order_one->good_id, true), true));
//            return $this->getInfoResponse('1001', '异常错误,停止分佣!');
        }

//        $obj_order = new RechargeOrder();
//        $obj_order_info = $obj_order->getOrdersById($orderid);
//        if (empty($obj_order_info)) {
//            return false;
//        }
//        if ($obj_order_info->status != 2) {
//            return false;
//        }
//        $rmb_price = $obj_order_info->price;
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
            $p_uid = $parent_info['uid'];
            $p_groupid = $parent_info['groupid'];
            $p_pt_pid = $parent_info['pt_pid'];
            $p_username = $parent_info['username'];
            $p_pt_id = $parent_info['pt_id'];

            $tmp_next_id = $p_pt_pid;

            if ($i == 0) {
                if ($p_groupid == 23) {
                    $due_ptb = 0.56 * $maid * 10;
//                    $due_ptb = 150 * 10;
                } elseif ($p_groupid == 24) {
                    $due_ptb = 0.67 * $maid * 10;
//                    $due_ptb = 180 * 10;
                } elseif ($p_groupid == 10){
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
            if ($this->commissionLog->where(['uid' => $p_uid, 'orderid' => $orderid])->exists()) {
                Storage::disk('local')->append('callback_document/again_log.txt', var_export(var_export($p_uid . '--' . $orderid, true), true));
                continue;
            }
            $this->commissionLog->insert([
                'uid' => $p_uid,
                'orderid' => $orderid,
                'money' => $due_ptb,
                'dateline' => $this->timestamp
            ]);
            $obj_shop_order_maid = new ShopOrdersMaid();
            $obj_shop_order_maid->addMaidLog($p_pt_id, $orderid, $due_ptb);

//            $perentAcount = $this->getUserAccountPTB($p_uid);
//            $this->userAccount->where('uid', $p_uid)->update(['extcredits4' => DB::raw("extcredits4 + " . $due_ptb)]);
//            $insert_id = $this->userCreditLog->addLog($p_uid, "FPT", ['extcredits4' => $due_ptb]);
//            $this->aboutLog->addLog($insert_id, $p_uid, $p_username, $p_pt_id, ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $due_ptb]);
            //加我的币改为加余额
            $obj_user_money = new UserMoney();
            $obj_user_money->plusCnyAndLog($p_pt_id, $due_ptb/10, 50);

            if ($count_partner >= 2) {
                break;
            }
        }
        $this->externalStatistical($orderid);
        return $count_partner;
    }

    /**
     * 订单直属上级返佣
     * @param $orderid
     * @return bool
     */
    public function returnCommissionOne($orderid)
    {

        $obj_order = new RechargeOrder();
        $obj_order_info = $obj_order->getOrdersById($orderid);
        if (empty($obj_order_info)) {
            return false;
        }
        if ($obj_order_info->status != 2) {
            return false;
        }

        $rmb_price = $obj_order_info->price;
        $uid = $obj_order_info->uid;
        $obj_ad_info = AdUserInfo::where(['uid' => $uid])->first();
        if (empty($obj_ad_info)) {
            return false;
        }

        $pt_pid = $obj_ad_info->pt_pid;
        if (empty($pt_pid)) {
            return false;
        }
        $parent_info = $this->getParentInfo($pt_pid);
        if (empty($parent_info)) {
            return false;
        }
        $p_uid = $parent_info['uid'];
        $p_groupid = $parent_info['groupid'];
        $p_pt_pid = $parent_info['pt_pid'];
        $p_username = $parent_info['username'];
        $p_pt_id = $parent_info['pt_id'];
        $due_ptb = 0;
        if ($p_groupid == 23) {
            $due_ptb = 150 * 10;
        } elseif ($p_groupid == 24) {
            $due_ptb = 180 * 10;
        } else {
            return false;
        }
        $this->externalStatistical($orderid);
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


    public function test()
    {
        return $this->tempRechargeSetting;
    }


    public function externalStatistical($orderId)
    {
        try {
            //暂时废弃该方法 -- 同步订单
//            $curl = curl_init();
//            $request_timestamp = time();
//            $request_content = json_encode(['ord' => $orderId], true);
//            $AcceptToken = md5('putao' . hash('sha256', $request_timestamp . $request_content) . 'putao');
//            curl_setopt_array($curl, array(
//                CURLOPT_URL => "http://pt.qmshidai.com/api/order_count",
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_ENCODING => "",
//                CURLOPT_MAXREDIRS => 10,
//                CURLOPT_TIMEOUT => 30,
//                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_CUSTOMREQUEST => "POST",
//                CURLOPT_POSTFIELDS => $request_content,
//                CURLOPT_HTTPHEADER => array(
//                    "Accept-Timestamp: " . $request_timestamp,
//                    "Accept-Token: " . $AcceptToken,
//                    "Content-Type: application/json",
//                    "cache-control: no-cache"
//                ),
//            ));
//
//            $response = json_decode(curl_exec($curl));
//            $err = curl_error($curl);
//            curl_close($curl);

            return true;
        } catch (\Exception $e) {
            return false;
        }

    }
}
