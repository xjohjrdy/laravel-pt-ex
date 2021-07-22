<?php

namespace App\Services\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleCityKing;
use App\Entitys\App\CircleMaid;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\CircleUserAdd;
use App\Services\Common\UserMoney;
use App\Services\Other\CircleCommissionService;
use Illuminate\Support\Facades\Storage;

class Add
{
    protected $circleUserAdd;
    protected $circleMaid;
    protected $appUserInfo;
    protected $adUserInfo;
    protected $userAccount;
    protected $aboutLog;
    protected $creditLog;
    protected $circleRingAdd;
    protected $circleRing;
    protected $circleCityKing;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CircleUserAdd $circleUserAdd, CircleCityKing $circleCityKing, CircleRing $circleRing, CircleRingAdd $circleRingAdd, CircleMaid $circleMaid, UserAboutLog $aboutLog, UserCreditLog $creditLog, AdUserInfo $adUserInfo, UserAccount $userAccount, AppUserInfo $appUserInfo)
    {
        $this->circleUserAdd = $circleUserAdd;
        $this->circleMaid = $circleMaid;
        $this->appUserInfo = $appUserInfo;
        $this->adUserInfo = $adUserInfo;
        $this->userAccount = $userAccount;
        $this->aboutLog = $aboutLog;
        $this->creditLog = $creditLog;
        $this->circleRingAdd = $circleRingAdd;
        $this->circleRing = $circleRing;
        $this->circleCityKing = $circleCityKing;
    }

    /**
     * 兼容支付宝回调
     * 结束当前订单，保证当前订单已经支付过
     * @param $order_id
     * @return int
     */
    public function overOrder($order_id)
    {
        $order = $this->circleUserAdd->getOrder($order_id);
        $app_user = $this->appUserInfo->getUserById($order->app_id);
        $user = $this->adUserInfo->appToAdUserId($order->app_id);
        $circle_ring = $this->circleRing->getById($order->circle_id, 1);
        $circle_ring_app_id = 0;
        $king_circle_app_id = 0;
        if (!empty($circle_ring)) {
            $circle_ring_app_id = $circle_ring->app_id;
            $king_circle = $this->circleCityKing->getById($circle_ring->king_id);
            if (!empty($king_circle)) {
                $king_circle_app_id = $king_circle->app_id;
            }
        }
        $this->circleUserAdd->checkOrder($order_id);
        $add_data = [
            'circle_id' => $order->circle_id,
            'app_id' => $order->app_id,
            'real_name' => $app_user->real_name,
            'use_time' => time() + $order->use_time,
            'use_price' => $order->money,
            'status' => '2',
        ];
        $this->circleRingAdd->createAdd($add_data);
        $this->circleRing->addNumber($order->circle_id, 'number_person', 1);
        $this->addMoneyPtb($circle_ring_app_id, 0.6, $order->money);
        if ($circle_ring_app_id <> 0) {
            $obj_maid = new CircleMaid();
            $obj_app_info = $this->appUserInfo->getUserById($circle_ring_app_id);
            $add_price_ptb = $order->money * 10 * 0.6;
            if ($add_price_ptb <> 0 || $order->money <> 0) {
                $obj_maid->create([
                    'app_id' => $circle_ring_app_id,
                    'from_user_name' => $obj_app_info->real_name,
                    'from_user_phone' => $obj_app_info->phone,
                    'from_user_img' => $obj_app_info->avatar,
                    'from_circle_name' => $circle_ring->ico_title,
                    'from_circle_img' => $circle_ring->ico_img,
                    'order_id' => $order_id,
                    'order_money' => $order->money * 10,
                    'money' => $add_price_ptb,
                    'type' => 6,
                ]);
            }
        }
        $this->addMoneyPtb($king_circle_app_id, 0.05, $order->money);

        //剥离多级分
        $obj_circle_commission_service = new CircleCommissionService();
        $this->newBonus($order_id);//加入圈子 直属分
        $obj_circle_commission_service->newBonusOther($order_id);//加入圈子 第三方分

        $user_info = $this->adUserInfo->where('pt_id', $order->app_id)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if (!empty($user_info) && $order->buy_type <> 1) {
            $this->creditLog->addLog($user_info->uid, "APC", ['extcredits1' => $order->money]);
        }
        return 1;
    }

    /**
     * 把加钱分佣的行为写成一个方法
     * @param $user_id
     * @param $percent
     * @param $money
     * @return int
     */
    public function addMoneyPtb($user_id, $percent, $money)
    {
        if ($user_id == 0 || $money <= 0) {
            return 1;
        }
        $user = $this->adUserInfo->appToAdUserId($user_id);
        $add_price_ptb = $money * 10 * $percent;

//        $user_account = $this->userAccount->getUserAccount($user->uid);
//        $res_account = $this->userAccount->addUserPTBMoney($add_price_ptb, $user->uid);
//        if ($add_price_ptb > 0) {
//            $insert_id = $this->creditLog->addLog($user->uid, "CAG", ['extcredits4' => $add_price_ptb]);
//            $extcredits4_change = $user_account->extcredits4 - $add_price_ptb;
//            $this->aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $user_account->extcredits4], ["extcredits4" => $extcredits4_change]);
//        }
        //加葡萄币改为加余额
        $obj_user_money = new UserMoney();
        $obj_user_money->plusCnyAndLog($user->pt_id, $add_price_ptb / 10, 51);


        return 1;
    }

    /**
     * @param $msg
     */
    public function log($msg)
    {
        Storage::disk('local')->append('callback_document/circle_alipay_notify_add.txt', var_export($msg, true));
    }


    /*
         * 圈子购买抽佣
         * 通过订单分佣，仅首次购买有分佣
           【购买者】的上级代理商抽：15%（仅直推可抽佣，不是三级抽佣），
           【购买者】的上级合伙人无限代抽10%，遇同级抽1%。

    该付费用户的上级
    （普通用户或代理商都可以，不看级别的）
    可抽：10%（仅直推可抽佣，不是三级抽佣），
    合伙人无限代抽5%，遇同级抽1%。
         */
    public function newBonus($order_id)
    {

        $order_info = $this->circleUserAdd->getOrder($order_id);

        $app_id = $order_info->app_id;
        $money = $order_info->money;
        $circle_id = $order_info->circle_id;
        $obj_circle = new CircleRing();
        $obj_circle_info = $obj_circle->where('id', $circle_id)->first();

//        $commission = $money;
        $obj_ad_user = new AdUserInfo();
        $obj_app_user = new AppUserInfo();
        $obj_ad_info = $obj_ad_user->where('pt_id', $app_id)->first();
        $obj_app_info = $obj_app_user->where('id', $app_id)->first();
        $ptPid = $obj_ad_info->pt_pid;
//        $signBool = false;
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
            if ($i == 0) {
                $commission = $money * 10 * 0.1;
//                if ($parentInfo['groupid'] == 24) {
//                    $signBool = true;
//                }

            } else {
                break;
//                $commission = $money * 10 * 0.05;
//                if ($signBool) {
//                    $commission *= 0.2;
//                    $signOk = true;
//                }
//
//                if ($parentInfo['groupid'] != 24) {
//                    continue;
//                }
//                $signBool = true;
            }
            $obj_maid = new CircleMaid();
            if ($obj_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id, 'type' => 2])->exists()) {
                Storage::disk('local')->append('callback_document/circle_again_log.txt', var_export(var_export($parentInfo['pt_id'] . '--' . $order_id, true), true));
                continue;
            }

            if ($commission <> 0 || $money <> 0) {
                $obj_maid->create([
                    'app_id' => $parentInfo['pt_id'],
                    'from_user_name' => $obj_app_info->real_name,
                    'from_user_phone' => $obj_app_info->phone,
                    'from_user_img' => $obj_app_info->avatar,
                    'from_circle_name' => $obj_circle_info->ico_title,
                    'from_circle_img' => $obj_circle_info->ico_img,
                    'order_id' => $order_id,
                    'order_money' => $money * 10,
                    'money' => $commission,
                    'type' => 2,
                ]);
            }

//            $obj_account = new UserAccount();
//            $user_ptb = $obj_account->getUserAccount($parentInfo['uid'])->extcredits4;
            if ($commission > 0) {
//                $obj_account->addUserPTBMoney($commission, $parentInfo['uid']);
//                $obj_credit_log = new UserCreditLog();
//                $obj_about_log = new UserAboutLog();
//                $insert_id = $obj_credit_log->addLog($parentInfo['uid'], "CAF", ['extcredits4' => $commission], 0);
//                $obj_about_log->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb + $commission]);
                //加葡萄币改为加余额
                $obj_user_money = new UserMoney();
                $obj_user_money->plusCnyAndLog($parentInfo['pt_id'], $commission / 10, 53);

            }


            if ($signOk) {
                break;
            }
        }

    }

    /**
     * 分佣方法
     * 抄另一个service的
     */
    public function maid($order_id)
    {
        $order_info = $this->circleUserAdd->getOrder($order_id);

        $app_id = $order_info->app_id;
        $money = $order_info->money;
        $circle_id = $order_info->circle_id;
        $obj_circle = new CircleRing();
        $obj_circle_info = $obj_circle->where('id', $circle_id)->first();

        $commission = $money;
        $obj_ad_user = new AdUserInfo();
        $obj_app_user = new AppUserInfo();
        $obj_ad_info = $obj_ad_user->where('pt_id', $app_id)->first();
        $obj_app_info = $obj_app_user->where('id', $app_id)->first();
        $ptPid = $obj_ad_info->pt_pid;
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
            $obj_maid = new CircleMaid();
            if ($obj_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Storage::disk('local')->append('callback_document/circle_again_log.txt', var_export(var_export($parentInfo['pt_id'] . '--' . $order_id, true), true));
                continue;
            }

            if ($commission <> 0 || $money <> 0) {
                $obj_maid->create([
                    'app_id' => $parentInfo['pt_id'],
                    'from_user_name' => $obj_app_info->real_name,
                    'from_user_phone' => $obj_app_info->phone,
                    'from_user_img' => $obj_app_info->avatar,
                    'from_circle_name' => $obj_circle_info->ico_title,
                    'from_circle_img' => $obj_circle_info->ico_img,
                    'order_id' => $order_id,
                    'order_money' => $money * 10,
                    'money' => $commission,
                    'type' => 2,
                ]);
            }

//            $obj_account = new UserAccount();
//            $user_ptb = $obj_account->getUserAccount($parentInfo['uid'])->extcredits4;
//            $obj_account->addUserPTBMoney($commission, $parentInfo['uid']);
//            $obj_credit_log = new UserCreditLog();
//            $obj_about_log = new UserAboutLog();
//            $insert_id = $obj_credit_log->addLog($parentInfo['uid'], "CAF", ['extcredits4' => $commission], 0);
//            $obj_about_log->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb + $commission]);
            //加葡萄币改为加余额
            $obj_user_money = new UserMoney();
            $obj_user_money->plusCnyAndLog($parentInfo['pt_id'], $commission / 10, 53);

            if ($signOk) {
                break;
            }
        }

        return true;
    }

    public function getParentInfo($ptPid)
    {
        $obj_ad_user = new AdUserInfo();
        $parentInfo = $obj_ad_user->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if (empty($parentInfo)) {
            return false;
        }

        return $parentInfo->toArray();

    }
}
