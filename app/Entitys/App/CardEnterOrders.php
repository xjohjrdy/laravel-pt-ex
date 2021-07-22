<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardEnterOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_card_enter_orders';
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
     * 新增或更新操作
     * @param $order
     */
    public function insertOrUpdate($order)
    {
        if (!$this->where(['record_id' => $order['record_id']])->exists()) {
            $this->create($order);
        } else {
            $this->where(['record_id' => $order['record_id']])->update($order);
        }
    }
}
