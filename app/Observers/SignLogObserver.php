<?php
namespace App\Observers;

use App\Entitys\App\AppUserInfo;
use App\Services\PutaoRealActive\PutaoRealActive;
use App\Entitys\App\SignLog;
use App\Entitys\App\ActiveRealSign;

class SignLogObserver
{
    /**
     * 监听创建事件
     * @param AppUserInfo $user
     * @return void
     */
    public function created(SignLog $signLog)
    {
        try {
            $app_id = $signLog->user_id;
            
            $appUserInfo = AppUserInfo::find($app_id);
            if ( empty( $appUserInfo ) || $appUserInfo->status == 2 ) {
                return;
            }
            
            $parent_id = $appUserInfo->parent_id;
            $parent_id = $parent_id > 0 ? $parent_id : 0;
            
            //同步新版签到数据表
            $data = [
                'dayline' => $signLog->date,
                'user_id' => $app_id,
                'parent_id' => $parent_id,
                'created_at' => $signLog->create_time,
            ];
            ActiveRealSign::insertIgnore($data);
            
            //活跃值：1.签到日志
            PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_SIGN, 1, 0, 0, $signLog->create_time, $parent_id );
        } catch (\Exception $e) {
            PutaoRealActive::debug_log($e, 'SignLogObserver');
        }
    }
}