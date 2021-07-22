<?php
namespace App\Observers;

use App\Entitys\App\UserOrderNew;
use App\Services\PutaoRealActive\PutaoRealActive;

class UserOrderNewObserver
{
    /**
     * 监听更新事件
     * @param UserOrderNew $userOrderNew
     * @return void
     */
    public function updated(UserOrderNew $userOrderNew)
    {
        try {
            $active_status = [3,4,9];//活跃值有效状态
            $fail_status = [1,2];//活跃值无效状态
            
            if ( $userOrderNew->isDirty('status') ) {
                $id         = $userOrderNew->id;
                $old_status = $userOrderNew->getOriginal('status');
                $new_status = $userOrderNew->status;
                
                $create_time = $userOrderNew->create_time;
                $confirm_time = $userOrderNew->confirm_time;
                
                $app_id     = $userOrderNew->user_id;
                
                //非3,4,9 -> 3,4,9 计算活跃值
                if ( !in_array($old_status, $active_status) && in_array($new_status, $active_status) ) {
                    $cashback_amount = $userOrderNew->cashback_amount;//报销金额
                    
                    PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_CASHBACK, $id, $cashback_amount, 0, $create_time );
                }
                
                //3,4,9 -> 1,2 说明之间加过，这里需要扣减对冲，
                if ( in_array($old_status, $active_status) && in_array($new_status, $fail_status) ) {
                    $cashback_amount = $userOrderNew->getOriginal('cashback_amount');//因为报销金额可能会变0，所以得拿旧的报销金额来做对冲
                    
                    PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_CASHBACK, -$id, -$cashback_amount, 0, $create_time, null, $confirm_time );
                }                                
            }
        } catch( \Exception $e ) {
            //可log
        }
    }
}