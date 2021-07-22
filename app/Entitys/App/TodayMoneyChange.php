<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TodayMoneyChange extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_today_money_change';
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
