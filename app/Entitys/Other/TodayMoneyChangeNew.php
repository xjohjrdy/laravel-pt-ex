<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TodayMoneyChangeNew extends Model
{
    //lc_circle_active_no_say
    protected $connection = 'db001';
    protected $table = 'lc_today_money_change_new';
    use SoftDeletes;

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 获取当天的每日统计数据
     */
    public function getByTime($today_time)
    {
        return $this->where(['change_time' => $today_time])->first();
    }
}
