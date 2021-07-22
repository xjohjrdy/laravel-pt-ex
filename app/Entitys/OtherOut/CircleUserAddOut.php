<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class CircleUserAddOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_circle_ring_user_add_order';
    public $timestamps = false;

    /*
     * 获取单笔订单
     */
    public function getOrder($order_id)
    {
        $res = $this->where(['order_id' => $order_id])->first();
        return $res;
    }
}
