<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class UserManagerLog extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_user_manager_log';
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
     * 根据指定年月获取该用户该月的佣金
     */
    public function getMaidMoneyForMonth($app_id, $begin_time, $end_time)
    {
        $money = $this
            ->where('created_at', '>=', $begin_time)
            ->where('created_at', '<=', $end_time)
            ->where('app_id', '=', $app_id)
            ->sum('money');
        return $money/10;
    }

}
