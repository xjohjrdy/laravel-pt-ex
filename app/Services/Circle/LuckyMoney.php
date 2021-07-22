<?php

namespace App\Services\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\CircleCityKing;
use App\Entitys\App\CircleMaid;
use App\Entitys\App\CircleRed;
use App\Entitys\App\CircleRedTime;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;

class LuckyMoney
{
    public function getOrderId()
    {
        return 'CC' . date('YmdHis') . rand(100000, 999999);
    }

    /*
     * 发红包扣款
     * 通过用户 app_id 扣除相应我的币，并记录日志
     * $value 为我的币值
     * （独立方法，可直接调用）
     */
    public function takePtb($app_id, $value)
    {
        try {
//            $obj_user = new AdUserInfo();
//            $obj_info = $obj_user->appToAdUserId($app_id);
//            $user_uid = $obj_info->uid;
//            $username = $obj_info->username;
//            $obj_account = new UserAccount();
//            $user_ptb = $obj_account->getUserAccount($user_uid)->extcredits4;
//            $obj_account->subtractPTBMoney($value, $user_uid);
//            $obj_credit_log = new UserCreditLog();
//            $obj_about_log = new UserAboutLog();
//            $insert_id = $obj_credit_log->addLog($user_uid, "RSP", ['extcredits4' => -$value]);
//            $obj_about_log->addLog($insert_id, $user_uid, $username, $app_id, ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb - $value]);

            //扣除我的币改为扣除余额
            $obj_user_money = new UserMoney();
            $obj_user_money->minusCnyAndLog($app_id, $value/10, '', "RSP");
        } catch (\Exception $e) {
            throw new ApiException('网络异常，扣费失败，请联系客服！', 5004);
        }

        return true;
    }

    /*
     * 圈子发红包扣余额
     */
    public function takeMoney($app_id, $value)
    {
        try {
            //扣除余额
            $obj_user_money = new UserMoney();
            $obj_user_money->minusCnyAndLog($app_id, $value, '71', "RSP");
        } catch (\Exception $e) {
            throw new ApiException('网络异常，扣费失败，请联系客服！', 5004);
        }
        return true;
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
     * 通过id 获取城市所有信息
     */
    public function getKingInfo($king_id)
    {
        $obj_circle = new CircleCityKing();
        return $obj_circle->where('id', $king_id)->first();
    }

    /*
     * 发红包,并返回该红包的id
     */
    public function sendRed($value)
    {
        $obj_red = new CircleRed();
        return $obj_red->create($value)->id;
    }

    /*
     * 领红包
     * 调用此方法必须有 抢红包人id 和 抢到多少红包值
     */
    public function getRed($value)
    {
        try {
            $app_id = $value['to_app_id'];
            $get_ptb = $value['have'];
            $obj_red_time = new CircleRedTime();
            $obj_red_time->create($value);

            $this->addPtb($app_id, $get_ptb);
            return true;
        } catch (ApiException $e) {
            return false;
        }
    }

    /*
     * 通过 App_id 添加用户我的币，并记录日志
     * $value为我的币值
     * （可独立使用）
     * （用做领取到红包，获得我的币）
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
//            $insert_id = $obj_credit_log->addLog($user_uid, "RLP", ['extcredits4' => $value]);
//            $obj_about_log->addLog($insert_id, $user_uid, $username, $app_id, ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb + $value]);
            //加我的币改为加余额
            $obj_user_money = new UserMoney();
            $obj_user_money->plusCnyAndLog($app_id, $value/10, 54);

        } catch (\Exception $e) {
            throw new ApiException('网络异常，分佣失败，请联系客服！', 5004);
        }

        return true;
    }

    /*
     * 添加一条分佣记录
     */
    public function addRedLog($value)
    {
        $obj_maid = new CircleMaid();
        return $obj_maid->create($value);
    }

    /*
     * 通过order_id 拿到该笔红包订单的信息
     */
    public function getInfoByOrderId($order_id)
    {
        $obj_red_order = new  CircleRed();
        $obj_info = $obj_red_order->where('order_id', $order_id)->first();
        return $obj_info;
    }

    /*
     * 通过红包order_id将未支付状态改成支付状态
     */
    public function updateRed($order_id)
    {
        $obj_order = new  CircleRed();
        $arr_params['status'] = 1;
        return $obj_order->where(['order_id' => $order_id])->update($arr_params);
    }

    /*
     * 查询该红包的资料
     */
    public function getRedInfo($params)
    {
        $obj_red = new CircleRed();
        return $obj_red->where($params)->first();
    }

    /*
     * 查询用户是否领取过该红包
     * type = 1
     */
    public function isGetRed($app_id, $red_id)
    {
        $obj_red = new CircleRedTime();
        return $obj_red->where(['to_app_id' => $app_id, 'red_id' => $red_id, 'type' => 1])->exists();
    }

    /*
     * 扣除该红包剩余次数以及红包钱
     */
    public function takeRed($red_id, $red_where)
    {
        $obj_red = new CircleRed();
        return $obj_red->where('id', $red_id)->decrement('red_have', 1, $red_where);
    }

    /*
     * 获取该圈子当前可用红包
     */
    public function getCircleList($circle_id)
    {
        $obj_red = new CircleRed();
        $list_info = $obj_red
            ->where(['circle_id' => $circle_id, 'status' => 1])
            ->where('red_have', '>', 0)
            ->orderByDesc('id')
            ->get(['id', 'red_have', 'remain_price', 'comment']);
        if ($list_info->isEmpty()) {
            return [];
        }
        return $list_info;
    }

    /*
     * 获取全部可用红包
     */
    public function getAllList()
    {
        $obj_red = new CircleRed();
        $list_info = $obj_red
            ->where(['status' => 1])
            ->where('red_have', '>', 0)
            ->orderByDesc('id')
            ->get(['id', 'red_have', 'remain_price', 'comment', 'area_land', 'circle_id']);
        if ($list_info->isEmpty()) {
            return [];
        }
        return $list_info;
    }

    /*
     * getAddList
     * 获取用户领取记录
     */
    public function getAddList($app_id)
    {
        $obj_red_time = new CircleRedTime();
        $list_info = $obj_red_time->where(['to_app_id' => $app_id, 'type' => 1])->pluck('red_id');
        if ($list_info->isEmpty()) {
            return [];
        }
        return $list_info->toArray();
    }

    /*
     * 判断用户是否属于该圈子
     */
    public function belongToCircle($app_id, $circle_id)
    {
        $obj_add = new CircleRingAdd();
        return $obj_add->where(['app_id' => $app_id, 'circle_id' => $circle_id])->exists();
    }

    /*
     * 获取该红包的 全部领取记录
     */
    public function getRecord($red_id)
    {
        $obj_red_record = new CircleRedTime();

        return $obj_red_record
            ->where('red_id', $red_id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['time as no', 'to_app_username as name', 'to_app_img as img', 'have as ptb', 'created_at as time', 'type']);

    }

    /*
     * 获取该用户今日领取红包的次数
     */
    public function getRedCount($app_id)
    {
        $obj_red_record = new CircleRedTime();
        $time_zone = [
            date("Y-m-d 00:00:00"),
            date("Y-m-d 23:59:59"),
        ];

        $count = $obj_red_record
            ->where(['to_app_id' => $app_id, 'type' => 1])
            ->whereBetween('created_at', $time_zone)
            ->count();

        return $count;
    }
}
