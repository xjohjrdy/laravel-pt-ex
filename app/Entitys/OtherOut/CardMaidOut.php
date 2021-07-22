<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 信用卡分佣记录实体
 * Class CardMaid
 * @package App\Entitys\App
 */
class CardMaidOut extends Model
{
    //
    protected $connection = 'app38_out';
    protected $table = 'lc_card_maid';
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
    public function getMaidMoneyForMonth($app_id, $type, $begin_time, $end_time)
    {
        $money = $this
            ->where('created_at', '>=', '2020-01-01 00:00:00')
            ->where('created_at', '>=', $begin_time)
            ->where('created_at', '<=', $end_time)
            ->where('app_id', '=', $app_id)
            ->where('type', '=', $type)
            ->sum('maid_ptb');
        return $money/10;
    }
}
