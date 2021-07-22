<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class AppActive extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_active_every_days';
    public $timestamps = false;

    /**
     * 获得某天的活跃度详情
     * @param $uid
     * @param $time
     * @return Model|null|static
     */
    public function getOneDays($uid, $time)
    {
        $res = $this->Where('time', '>=', '1577808000')->where(['uid' => $uid, 'time' => $time])->first(['context']);

        return $res;
    }

    /*
     * 用户前5个月数据值
     */
    public function getUpFiveActiveValue($app_id, $arr_lastFive_month_timestamp)
    {
        return $this->where('uid', $app_id)
            ->whereIn('time', $arr_lastFive_month_timestamp)
            ->pluck('context');

    }

    /*
     * 得到满足条件的最大id
     */
    public function getActiveValueMaxId($app_id)
    {
        return $this->where('uid', $app_id)
            ->max('id');
    }
}
