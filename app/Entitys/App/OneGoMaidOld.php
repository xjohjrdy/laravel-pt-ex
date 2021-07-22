<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OneGoMaidOld extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_one_go_maid_old';
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
     * 分佣金额不同删除该订单的分佣记录
     */
    public function delOrder($trade_id)
    {
        try {
            return $this->where('trade_id', $trade_id)
                ->delete();
        } catch (\Exception $e) {
            return false;
        }
    }

    /*
     * 添加补贴
     */
    public function addSubsidy($trade_id, $app_id, $maid_money, $get_money)
    {
        return $this->create([
            'trade_id' => $trade_id,
            'app_id' => $app_id,
            'maid_money' => $maid_money,
            'get_money' => $get_money,
        ]);
    }

}
