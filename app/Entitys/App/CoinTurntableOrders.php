<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinTurntableOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_coin_turntable_orders';
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
     * 领取成功更新订单状态
     */
    public function updateOrderInfo($app_id, $order_id, $order_data)
    {
        return $this->where(['app_id' => $app_id, 'id' => $order_id])
            ->update($order_data);
    }
}
