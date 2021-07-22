<?php

namespace App\Services\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleCityKing;
use App\Entitys\App\CircleCityKingAdd;
use App\Entitys\App\CircleOrder;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\CircleMaid;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class BecomeHost
{
    public function getOrderId()
    {
        return 'CC' . date('YmdHis') . rand(100000, 999999);
    }
    public function createOrderNot($value)
    {
        $obj_order = new CircleOrder();
        $value['order_id'] = $this->getOrderId();
        return $obj_order->create($value)->order_id;
    }

    /*
     * 创建一个圈子,并将圈子ID绑定到支付订单
     * 确认支付成功后才能调用该方法
     * 如果是支付宝状态并修改支付状态为1
     */
    public function createCircle($order_id, $value, $pay = false)
    {
        $obj_ring = new CircleRing();
        $ring_key = $obj_ring->create($value)->id;
        $obj_order = new  CircleOrder();
        $arr_params['circle_id'] = $ring_key;
        if ($pay) {
            $arr_params['status'] = 1;
        }
        $obj_order->where(['order_id' => $order_id])->update($arr_params);
        return $ring_key;
    }

    /*
     * 转移圈主，
     * 确认支付成功后才能调用该方法
     * 如果是支付宝状态并修改支付状态为1
     */
    public function updateCircle($order_id, $circle_id, $value)
    {
        $obj_ring = new CircleRing();
        $ring_key = $obj_ring->where('id', $circle_id)->increment('buy_number', 1, $value);
                
        $obj_order = new  CircleOrder();
        $arr_params['status'] = 1;
        $obj_order->where(['order_id' => $order_id])->update($arr_params);
        
        return $ring_key;
    }

    /*
     * 转移圈主，
     * 确认支付成功后才能调用该方法
     * 如果是支付宝状态并修改支付状态为1
     */
    public function updateCircleNotNumber($order_id, $circle_id, $value)
    {
        try {
            $obj_ring = new CircleRing();
            $obj_ring->where('id', $circle_id)->update($value);
            
            //$obj_order = new  CircleOrder();
            //$arr_params['status'] = 1;
            //$obj_order->where(['order_id' => $order_id])->update($arr_params);
            
            //需要Eloquent模型save监听
            $obj_order = CircleOrder::where('order_id',$order_id)->first();
            $obj_order->status = 1;
            $obj_order->save();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /*
     * 通过app_id取用户的uid
     */
    public function getUid($app_id)
    {
        $obj_user = new AdUserInfo();
        $obj_info = $obj_user->appToAdUserId($app_id);

        if (empty($obj_info)) {
            throw new ApiException('不存在该app_id', 8008);
        }

        return $obj_info->uid;
    }

    /*
     * 校验用户葡萄币是否够用
     */
    public function verifyPtb($user_uid, $value)
    {

        $obj_account = new UserAccount();
        $user_ptb = $obj_account->getUserAccount($user_uid)->extcredits4;
        if ($user_ptb >= $value) {
            return true;
        }

        return false;
    }

    /*
     * 通过用户 app_id 扣除相应葡萄币，并记录日志
     * $value 为葡萄币值
     * （独立方法，可直接调用）
     */
    public function takePtb($app_id, $value)
    {
        try {
            $obj_user = new AdUserInfo();
            $obj_info = $obj_user->appToAdUserId($app_id);
            $user_uid = $obj_info->uid;
            $username = $obj_info->username;
            $obj_account = new UserAccount();
            $user_ptb = $obj_account->getUserAccount($user_uid)->extcredits4;
            $obj_account->subtractPTBMoney($value, $user_uid);
            $obj_credit_log = new UserCreditLog();
            $obj_about_log = new UserAboutLog();
            $insert_id = $obj_credit_log->addLog($user_uid, "CCP", ['extcredits4' => -$value]);
            $obj_about_log->addLog($insert_id, $user_uid, $username, $app_id, ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb - $value]);
        } catch (\Exception $e) {
            throw new ApiException('网络异常，扣费失败，请联系客服！', 5004);
        }

        return true;
    }

    /*
     * 购买圈子扣除余额
     */
    public function takeMoney($app_id, $value)
    {
        try {
            //扣除余额
            $obj_user_money = new UserMoney();
            $obj_user_money->minusCnyAndLog($app_id, $value, '72', "CCP");
        } catch (\Exception $e) {
            throw new ApiException('网络异常，扣费失败，请联系客服！', 5004);
        }
        return true;
    }

    /*
     * 通过 app_id 添加用户葡萄币，并记录日志
     * $value为葡萄币值
     * （可独立使用）
     * （用做圈子被购买，返还葡萄币）
     */
    public function addPtb($app_id, $value)
    {
        try {
//            $obj_user = new AdUserInfo();
//            $obj_info = $obj_user->appToAdUserId($app_id);
//            $user_uid = $obj_info->uid;
//            $username = $obj_info->username;
//            $obj_account = new UserAccount();
//            $user_ptb = $obj_account->getUserAccount($user_uid)->extcredits4;
//            $obj_account->addUserPTBMoney($value, $user_uid);
//            $obj_credit_log = new UserCreditLog();
//            $obj_about_log = new UserAboutLog();
//            $insert_id = $obj_credit_log->addLog($user_uid, "CRP", ['extcredits4' => $value]);
//            $obj_about_log->addLog($insert_id, $user_uid, $username, $app_id, ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb + $value]);
            //加葡萄币改为加余额
            $obj_user_money = new UserMoney();
            $obj_user_money->plusCnyAndLog($app_id, $value/10, 55);

        } catch (\Exception $e) {
            throw new ApiException('网络异常，分佣失败，请联系客服！', 5004);
        }

        return true;
    }

    /*
     * 通过订单分佣，仅首次购买有分佣
     * （可独立调用）
     */
    public function bonus($order_id)
    {
        $order_info = $this->getInfoByOrderId($order_id);

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
                Storage::disk('local')->append('callback_document/circle_again_log.txt', var_export($parentInfo['pt_id'] . '--' . $order_id, true));
                continue;
            }
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
                'type' => 4,
            ]);

//            $obj_account = new UserAccount();
//            $user_ptb = $obj_account->getUserAccount($parentInfo['uid'])->extcredits4;
//            $obj_account->addUserPTBMoney($commission, $parentInfo['uid']);
//            $obj_credit_log = new UserCreditLog();
//            $obj_about_log = new UserAboutLog();
//            $insert_id = $obj_credit_log->addLog($parentInfo['uid'], "CFP", ['extcredits4' => $commission]);
//            $obj_about_log->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb + $commission]);
            //加葡萄币改为加余额
            $obj_user_money = new UserMoney();
            $obj_user_money->plusCnyAndLog($parentInfo['pt_id'], $commission/10, 56);

            if ($signOk) {
                break;
            }
        }

        return true;

    }

    /*
         * 圈子购买抽佣
         * 通过订单分佣，仅首次购买有分佣
           【购买者】的上级代理商抽：15%（仅直推可抽佣，不是三级抽佣），
           【购买者】的上级合伙人无限代抽10%，遇同级抽1%。
         */
    public function newBonus($order_id)
    {
        $order_info = $this->getInfoByOrderId($order_id);

        $app_id = $order_info->app_id;
        $money = $order_info->money;
        $circle_id = $order_info->circle_id;
        $obj_circle = new CircleRing();
        $obj_circle_info = $obj_circle->where('id', $circle_id)->first();
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
                $commission = $money * 10 * 0.15;
                if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                    continue;
                }
//                if ($parentInfo['groupid'] == 24) {
//                    $signBool = true;
//                }

            } else {
                break;
//                $commission = $money * 10 * 0.1;
//                if ($signBool) {
//                    $commission *= 0.1;
//                    $signOk = true;
//                }
//
//                if ($parentInfo['groupid'] != 24) {
//                    continue;
//                }
//                $signBool = true;
            }
            $obj_maid = new CircleMaid();
            if ($obj_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Storage::disk('local')->append('callback_document/circle_again_log.txt', var_export($parentInfo['pt_id'] . '--' . $order_id, true));
                continue;
            }
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
                'type' => 4,
            ]);

//            $obj_account = new UserAccount();
//            $user_ptb = $obj_account->getUserAccount($parentInfo['uid'])->extcredits4;
//            $obj_account->addUserPTBMoney($commission, $parentInfo['uid']);
//            $obj_credit_log = new UserCreditLog();
//            $obj_about_log = new UserAboutLog();
//            $insert_id = $obj_credit_log->addLog($parentInfo['uid'], "CFP", ['extcredits4' => $commission]);
//            $obj_about_log->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb + $commission]);
            //加葡萄币改为加余额
            $obj_user_money = new UserMoney();
            $obj_user_money->plusCnyAndLog($parentInfo['pt_id'], $commission/10, 56);

            if ($signOk) {
                break;
            }
        }

    }

    /*
     * 插入一条圈子被购买分佣记录
     * 原圈主分佣的记录
     * $commission 佣金
     * $app_id 得到该笔佣金的人
     * $from_app_id  该笔订单的发起人
     */
    public function addBoundsLog($app_id, $from_app_id, $order_id, $commission)
    {
        $obj_maid = new CircleMaid();
        if ($obj_maid->where(['app_id' => $app_id, 'order_id' => $order_id])->exists()) {
            Storage::disk('local')->append('callback_document/circle_original_log.txt', var_export($app_id . '--' . $order_id, true));
        }
        $obj_app_info = AppUserInfo::find($from_app_id);
        $order_info = $this->getInfoByOrderId($order_id);
        $money = $order_info->money;
        $circle_id = $order_info->circle_id;
        $obj_circle = new CircleRing();
        $obj_circle_info = $obj_circle->where('id', $circle_id)->first();
        return $obj_maid->create([
            'app_id' => $app_id,
            'from_user_name' => $obj_app_info->real_name,
            'from_user_phone' => $obj_app_info->phone,
            'from_user_img' => $obj_app_info->avatar,
            'from_circle_name' => $obj_circle_info->ico_title,
            'from_circle_img' => $obj_circle_info->ico_img,
            'order_id' => $order_id,
            'order_money' => $money * 10,
            'money' => $commission,
            'type' => 1,
        ]);
    }


    /*
     * 圈子竞价抽佣
     * 通过订单分佣，仅首次购买有分佣
       【新任圈主】的上级代理商抽【加价部分】的15%（18元）（仅直推可抽佣，不是三级抽佣），
       【新任圈主】的上级合伙人无限代抽【加价部分】的10%(12元）遇同级1%（1.2元）
     */
    public function bidBonus($order_id)
    {
        $order_info = $this->getInfoByOrderId($order_id);

        $app_id = $order_info->app_id;
        $money = round($order_info->money / 6, 2);
        $circle_id = $order_info->circle_id;
        $obj_circle = new CircleRing();
        $obj_circle_info = $obj_circle->where('id', $circle_id)->first();
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
                $commission = $money * 10 * 0.15;
                if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                    continue;
                }
//                if ($parentInfo['groupid'] == 24) {
//                    $signBool = true;
//                }

            } else {
                break;
//                $commission = $money * 10 * 0.1;
//                if ($signBool) {
//                    $commission *= 0.1;
//                    $signOk = true;
//                }
//
//                if ($parentInfo['groupid'] != 24) {
//                    continue;
//                }
//                $signBool = true;
            }
            $obj_maid = new CircleMaid();
            if ($obj_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id, 'type' => 5])->exists()) {
                Storage::disk('local')->append('callback_document/circle_again_log.txt', var_export($parentInfo['pt_id'] . '--' . $order_id, true));
                continue;
            }
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
                'type' => 5,
            ]);

//            $obj_account = new UserAccount();
//            $user_ptb = $obj_account->getUserAccount($parentInfo['uid'])->extcredits4;
//            $obj_account->addUserPTBMoney($commission, $parentInfo['uid']);
//            $obj_credit_log = new UserCreditLog();
//            $obj_about_log = new UserAboutLog();
//            $insert_id = $obj_credit_log->addLog($parentInfo['uid'], "CBP", ['extcredits4' => $commission]);
//            $obj_about_log->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb + $commission]);
            //加葡萄币改为加余额
            $obj_user_money = new UserMoney();
            $obj_user_money->plusCnyAndLog($parentInfo['pt_id'], $commission/10, 57);

            if ($signOk) {
                break;
            }
        }

    }


    /*
     * 城主分佣
     * $king_id 该圈子归属城市的id
     * $order_id 抽佣订单
     * 城主抽：10%
     */
    public function kingBonus($king_id, $order_id)
    {
        $obj_king = new CircleCityKing();
        $app_id = $obj_king->where('id', $king_id)->value('app_id');

        if (empty($app_id)) {
            return false;
        }

        $obj_king_add = new  CircleCityKingAdd();
        $king_add_info = $obj_king_add->where(['king_id' => $king_id, 'app_id' => $app_id])->first();
        if (empty($king_add_info)) {
            return false;
        }

        $order_info = $this->getInfoByOrderId($order_id);

        $parentInfo = $this->getParentInfo($app_id);
        $obj_maid = new CircleMaid();
        if ($obj_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
            Storage::disk('local')->append('callback_document/circle_king_again_log.txt', var_export(var_export($parentInfo['pt_id'] . '--' . $order_id, true), true));
            return false;
        }

        $circle_id = $order_info->circle_id;
        $obj_circle = new CircleRing();
        $obj_circle_info = $obj_circle->where('id', $circle_id)->first();

        $commission = $order_info->money * 10 * 0.1;
        $obj_app_user = new AppUserInfo();
        $obj_app_info = $obj_app_user->where('id', $app_id)->first();
        $obj_maid->create([
            'app_id' => $parentInfo['pt_id'],
            'from_user_name' => $obj_app_info->real_name,
            'from_user_phone' => $obj_app_info->phone,
            'from_user_img' => $obj_app_info->avatar,
            'from_circle_name' => $obj_circle_info->ico_title,
            'from_circle_img' => $obj_circle_info->ico_img,
            'order_id' => $order_id,
            'order_money' => $order_info->money * 10,
            'money' => $commission,
            'type' => 4,
        ]);

//        $obj_account = new UserAccount();
//        $user_ptb = $obj_account->getUserAccount($parentInfo['uid'])->extcredits4;
//        $obj_account->addUserPTBMoney($commission, $parentInfo['uid']);
//        $obj_credit_log = new UserCreditLog();
//        $obj_about_log = new UserAboutLog();
//        $insert_id = $obj_credit_log->addLog($parentInfo['uid'], "CFP", ['extcredits4' => $commission]);
//        $obj_about_log->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb + $commission]);
        //加葡萄币改为加余额
        $obj_user_money = new UserMoney();
        $obj_user_money->plusCnyAndLog($parentInfo['pt_id'], $commission/10, 56);
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


    /*
     * 通过order_id 拿到该笔订单的信息
     */
    public function getInfoByOrderId($order_id)
    {
        $obj_order = new  CircleOrder();
        $obj_info = $obj_order->where('order_id', $order_id)->first();
        return $obj_info;
    }

    /*
     * 通过id 获取圈子所有信息
     */
    public function getCircleInfo($circle_id)
    {
        $obj_circle = new CircleRing();
        return $obj_circle->where('id', $circle_id)->first();
    }

    /*
     * 圈主创建圈子添加初始加入记录
     * app_id 圈子id 地域
     */
    public function addCircle($app_id, $circle_id, $area = '')
    {
        try {
            $obj_app_user = new AppUserInfo();
            $obj_app_info = $obj_app_user->where('id', $app_id)->first();
            $real_name = $obj_app_info->real_name;

            $obj_add = new CircleRingAdd();
            $amend = [
                'circle_id' => $circle_id,
                'app_id' => $app_id,
                'real_name' => $real_name,
                'status' => 0,
            ];
            $obj_add->updateOrCreate(
                [
                    'circle_id' => $circle_id,
                    'app_id' => $app_id,
                ]
                , $amend);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /*
     * 将指定圈子的用户降价至普通用户
     */
    public function demotion($app_id, $circle_id)
    {
        $obj_add = new CircleRingAdd();
        return $obj_add->where(['app_id' => $app_id, 'circle_id' => $circle_id])->update(['status' => 2]);
    }

    /*
     * 根据用户app_id
     */
    public function getPtb($app_id)
    {
        $obj_ad_user = new AdUserInfo();
        $obj_ad_info = $obj_ad_user->appToAdUserId($app_id);
        if (empty($obj_ad_info)) {
            return false;
        }

        $obj_account = new UserAccount();

        return $obj_account->getUserAccount($obj_ad_info->uid)->extcredits4;
    }

    /*
     * 根据圈子id取得全部竞价历史
     */
    public function getBidHistory($circle_id)
    {
        $obj_order = new CircleOrder();
        $info_list = $obj_order->where(['circle_id' => $circle_id, 'status' => 1])->get(['circle_id', 'money', 'app_id', 'created_at']);
        return $info_list;
    }

    /*
     * 判断该圈子是否有人竞价>=3次
     */
    public function isLock($circle_id)
    {
        $obj_order = new CircleOrder();
        $isLock = $obj_order
            ->select(DB::raw('count(*) as order_count'))
            ->where(['circle_id' => $circle_id, 'status' => 1])
            ->groupBy('app_id')
            ->havingRaw('count(*) > 2')
            ->exists();

        return $isLock;
    }

    /*
     * 判断某个用户对该圈子竞价的次数
     */
    public function countHistory($app_id, $circle_id)
    {
        $obj_order = new CircleOrder();
        return $obj_order->where(['app_id' => $app_id, 'circle_id' => $circle_id])->count();
    }

    /*
     * 统计用户当前有几个圈子
     */
    public function countCircle($app_id)
    {
//
        $sql = "SELECT count(*) as r FROM ( SELECT DISTINCT circle_id FROM lc_circle_ring_add_order WHERE app_id = " . $app_id . " AND money <> 0  AND `status` <> 0 ) as t";
        $res = DB::connection("app38")->select($sql);

        if (empty($res[0]->r)) {
            $count = 0;
        } else {
            $count = $res[0]->r;
        }
        return $count;
    }

}
