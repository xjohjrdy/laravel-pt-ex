<?php
namespace App\Observers;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\FansCount;
use App\Services\PutaoRealActive\PutaoRealActive;

class UserObserver
{
    /**
     * 监听创建事件
     * @param AppUserInfo $user
     * @return void
     */
    public function created(AppUserInfo $user)
    {
        try {
            //如果有上级也更新同步
            $parent_id = $user->getAttribute('parent_id');
            if ( $parent_id > 0 ) {
                //重新计算上级用户的直属粉丝数量
                FansCount::recount($parent_id);
                
                //活跃值：5.直推新增
                PutaoRealActive::eventListen( $parent_id, PutaoRealActive::EVENT_FANS, $user->id, 0, 0, $user->create_time);
            }
        } catch (\Exception $e) {
            PutaoRealActive::debug_log($e, 'UserObserver');
        }
    }
    
    /**
     * 监听更新事件
     * @param AppUserInfo $user
     * @return void
     */
    public function updated(AppUserInfo $user)
    {
        try {
            //用户关系发生变化
            if ( $user->isDirty('parent_id') && $user->parent_id > 0 ) {
                
                $parent_id = $user->parent_id;
                
                //重新计算上级用户的直属粉丝数量
                FansCount::recount($parent_id);
                
                //活跃值：5.直推新增
                PutaoRealActive::eventListen( $parent_id, PutaoRealActive::EVENT_FANS, $user->id, 0, 0, $user->create_time);
            }
        } catch(\Exception $e) {
            PutaoRealActive::debug_log($e, 'UserObserver');
        }
    }
}