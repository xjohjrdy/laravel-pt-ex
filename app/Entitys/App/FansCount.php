<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use App\Services\Common\CommonFunction;
use Illuminate\Support\Facades\Cache;

class FansCount extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_fans_count';
    protected $primaryKey = 'app_id';
    public $timestamps = false;
    
    /**
     * 初始化记录
     * @param int $app_id
     * @return void
     */
    public static function init( $app_id )
    {
        try {
            $m = new static();
            $m->app_id = $app_id;
            $m->save();
        } catch (\Exception $e) {
        }
    }
    
    /**
     * 重新计算用户直属粉丝数量
     * @param int $app_id
     * @return void
     */
    public static function recount( $app_id )
    {
        if ( $app_id > 0 ) {
            try {
                $fans_count = AppUserInfo::where(['parent_id'=>$app_id,'status'=>1])->count();
                $model = self::find($app_id);
                if ( $model ) {
                    $model->fans_count = $fans_count;
                    $model->save();
                } else {                    
                    $model = new static();
                    $model->app_id = $app_id;
                    $model->fans_count = $fans_count;
                    $model->save();
                }
                $ckey = CommonFunction::getUserFansCountCacheKey($app_id);
                Cache::store('redis')->forever($ckey, $fans_count);
            } catch (\Exception $e) {
            }
        }
    }
    
    /**
     * 获取用户直属粉丝数量
     * @param int $app_id
     * @return int
     */
    public static function getFansCount( $app_id )
    {
        $ckey = CommonFunction::getUserFansCountCacheKey($app_id);
        $cdata = Cache::store('redis')->get($ckey);
        if ( $cdata === null ) {
            $cdata = self::where(['app_id'=>$app_id])->value('fans_count');
            $cdata = $cdata ? $cdata : 0;
            Cache::store('redis')->forever($ckey, $cdata);
        }
        return intval($cdata);
    }
    
    /**
     * 清空全部缓存（如果出现大面积不准，再考虑全部清空修正）
     * @return void
     */
    public static function clearAllCache()
    {
    }
}
