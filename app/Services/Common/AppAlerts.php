<?php

namespace App\Services\Common;

use App\Services\Dplus\Dplus;
use App\Services\JPush\JPush;
use Illuminate\Support\Facades\Redis;

class AppAlerts
{
    const W_LIST_APP_ALERTS = 'w_list_app_alerts';
    const W_KEY_APP_ALERTS_LOOK = 'w_key_app_alerts_look';

    /**
     * 将通知消息推送至redis队列等待处理
     * @param $msg
     * @return int
     */
    public function pushMessage($msg)
    {

        try {
            if (is_string($msg)) {
                return Redis::RPUSH(self::W_LIST_APP_ALERTS, $msg);
            }
        } catch (\Exception $e) {
            //什么都不做
        }
        return 0;
    }

    /**
     * 约定好的 json 数据
     * @param $msg
     * @return bool
     */
    public function mPush($msg)
    {


        try {
            $arr_msg = json_decode($msg, true);

            $title = @$arr_msg['title']; //通知标题
            $app_id = @$arr_msg['app_id']; //接收人app_id
            $state = @$arr_msg['state']; //1.公告  2.工作提醒  3.共享提醒
            $inform_sign = @$arr_msg['inform_sign']; //标记 0忽略 1原生 2网页
            $inform_url = @$arr_msg['inform_url']; //1=公告 2工单 3新增粉丝 http=网页
            $inform_data = @$arr_msg['inform_data']; //约定的 {key，value}
            $type = @$arr_msg['type']; //0 单独推送个人 1 推送全体
            $push_meng_apk = new Dplus(1);
            $push_meng_ios = new Dplus(2);


            $ticker = $title;// 提示文字
            $text = $title; //提示描述

            //$demo = new Dplus(1); // 1 安卓  2 ios
            //$demo->sendAndroidBroadcast(); // 安卓发送全部
            //$demo->sendAndroidCustomizedcast(); // 安卓单个用户发送
            //$demo->sendIOSBroadcast(); // ios 发送全部
            //$demo->sendIOSCustomizedcast(); // ios 发送单个用户
            if (empty($type)) {
                $push_meng_apk->sendAndroidCustomizedcast($ticker, $title, $text, $app_id, $state, $inform_sign, $inform_url, $inform_data); // 安卓单个用户发送
                $push_meng_ios->sendIOSCustomizedcast($title, $app_id, $state, $inform_sign, $inform_url, $inform_data); // ios 发送单个用户
//                JPush::push_user($title, $app_id, $state, $inform_sign, $inform_url, $inform_data);
            } else {
                $push_meng_apk->sendAndroidBroadcast($ticker, $title, $text, $state, $inform_sign, $inform_url, $inform_data); // 安卓发送全部
                $push_meng_ios->sendIOSBroadcast($title, $state, $inform_sign, $inform_url, $inform_data); // ios 发送全部
//                JPush::push_user_all($title, $state, $inform_sign, $inform_url, $inform_data);
            }


            return true;
        } catch (\Exception $e) {
            $this->unlock();
        }
        return false;
    }


    /**
     * 获取一条需要通知的消息
     * 如果获取不到的则做解锁操作
     * @return bool
     */
    public function getAlert()
    {
        try {
            $alert_msg = Redis::LPOP(self::W_LIST_APP_ALERTS);

            if (empty($alert_msg)) {
                $this->unlock();
                return false;
            }
            return $alert_msg;

        } catch (\Exception $e) {
            //什么都不做
        }
        return false;
    }

    /**
     * 给消息队列上锁
     */
    public function look()
    {
        Redis::SET(self::W_KEY_APP_ALERTS_LOOK, 1);
    }

    /**
     * 给消息队列解锁
     */
    public function unlock()
    {
        Redis::DEL(self::W_KEY_APP_ALERTS_LOOK);
    }

    /**
     * 判断消息状态
     */
    public function isLock()
    {
        $status = Redis::GET(self::W_KEY_APP_ALERTS_LOOK);
        if (empty($status)) {
            return false;
        }
        return true;
    }

}