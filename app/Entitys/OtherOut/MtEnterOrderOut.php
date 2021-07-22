<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MtEnterOrderOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_mt_enter_order';
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
     * 匹配美团唯一订单
     */
    public function getOneOrders($trade_id)
    {
        return $this->where(['unique_item_id' => (string)$trade_id])->first();
    }
}
