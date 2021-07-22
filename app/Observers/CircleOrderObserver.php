<?php
namespace App\Observers;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleOrder;
use App\Services\PutaoRealActive\PutaoRealActive;

class CircleOrderObserver
{
    /**
     * 监听创建事件
     * @param CircleOrder $user
     * @return void
     */
    public function created(CircleOrder $circleOrder)
    {
        try {
            if ( $circleOrder->money == 600 && $circleOrder->status == 1 ) {
                
                $app_id = $circleOrder->app_id;
                
                //活跃值：7.用户购买圈子（600元的）
                PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_CIRCLE, $circleOrder->id, 0, 0, $circleOrder->created_at);
            }
        } catch (\Exception $e) {
            //可log
        }
    }
    
    /**
     * 监听更新事件
     * @param AppUserInfo $user
     * @return void
     */
    public function updated(CircleOrder $circleOrder)
    {
        try {
            if ( $circleOrder->isDirty('status') && $circleOrder->money == 600 && $circleOrder->status == 1 ) {
                
                $app_id = $circleOrder->app_id;
                
                //活跃值：7.用户购买圈子（600元的）
                PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_CIRCLE, $circleOrder->id, 0, 0, $circleOrder->created_at);
            }
        } catch(\Exception $e) {
            //可log
        }
    }
}