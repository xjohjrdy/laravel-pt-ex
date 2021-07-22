<?php
namespace App\Observers\Ad;

use App\Entitys\App\AppUserInfo;
use App\Services\PutaoRealActive\PutaoRealActive;
use App\Entitys\Ad\VoipMoneyOrder;

class VoipMoneyOrderObserver
{    
    /**
     * 监听更新事件
     * @param AppUserInfo $user
     * @return void
     */
    public function updated(VoipMoneyOrder $voipMoneyOrder)
    {
        try {
            if ( $voipMoneyOrder->isDirty('status') && $voipMoneyOrder->status == 1 ) {
                
                $app_id = $voipMoneyOrder->app_id;
                
                //活跃值：3.购买我的通讯
                PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_VOIP, $voipMoneyOrder->id, 0, 0, $voipMoneyOrder->created_at);
            }
        } catch(\Exception $e) {
            //可log
        }
    }
}