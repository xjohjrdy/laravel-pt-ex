<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class ShopOrdersOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_shop_orders';
    public $timestamps = false;

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
