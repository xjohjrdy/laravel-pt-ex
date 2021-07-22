<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinTurntablePrize extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_coin_turntable_prize';
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

    /*
     * 根据转盘了id 得到所有奖品信息
     */
    public function getTurntablePrizeInfo($turntable_id)
    {
        return $this->where('turntable_id', $turntable_id)
            ->get(['id', 'turntable_id', 'type', 'title', 'img']);
    }

    /*
     * 根据转盘id 得到可抽的奖品信息
     */
    public function getTurntableValidPrizeInfo($turntable_id)
    {
        return $this->where(['turntable_id' => $turntable_id])
            ->where('number', '>', 0)
            ->get();
    }

    /*
     * 根据转盘id 得到奖品指定信息
     */
    public function getTurntablePrizeAssignInfo($turntable_id)
    {
        return $this->where('turntable_id', $turntable_id)
            ->get(['title', 'win_probability']);
    }
}
