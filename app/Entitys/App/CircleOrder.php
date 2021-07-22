<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleOrder extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_add_order';
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
     * 通过id找到唯一的订单
     * @param $id
     * @return Model|null|static
     */
    public function getByOrderId($id)
    {
        return $this->where(['order_id' => $id])->first();
    }

}
