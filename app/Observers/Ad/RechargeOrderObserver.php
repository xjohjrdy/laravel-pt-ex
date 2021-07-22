<?php
namespace App\Observers\Ad;

use App\Entitys\Ad\AdUserInfo;
use App\Services\PutaoRealActive\PutaoRealActive;
use App\Entitys\Ad\RechargeOrder;

class RechargeOrderObserver
{    
    /**
     * 监听更新事件
     * @param AdUserInfo $user
     * @return void
     */
    public function updated(RechargeOrder $rechargeOrder)
    {
        try {
            if ( $rechargeOrder->isDirty('status') && $rechargeOrder->status == 2 && $rechargeOrder->groupid == 23 ) {
                
                $uid = $rechargeOrder->uid;
                $adUserInfo = new AdUserInfo();
                $user = $adUserInfo->getUserById($uid);
                $app_id = $user->pt_id;
                
                //活跃值：2.用户成为VIP
                PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_VIP, 1, 0, 0, $rechargeOrder->submitdate);
            }
        } catch(\Exception $e) {
            //可log
        }
    }
}