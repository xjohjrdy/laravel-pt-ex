<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaobaoEnterOrderOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_taobao_enter_order';
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
     * 匹配唯一
     */
    public function getOneOrders($trade_id)
    {
        return $this->where(['trade_id' => (string)$trade_id])->first();
    }
}
