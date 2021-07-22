<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class ShopOrdersPretendMaidOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_shop_orders_pretend_maid';
    public $timestamps = false;

    /*
     * 更新假订单状态
     */
    public function updateStatus($app_id, $order_id)
    {
        $res = $this->where([
            'app_id' => $app_id,
            'order_id' => $order_id,
        ])->first();

        if (!$res) {
            return true;
        }

        return $this->where([
            'app_id' => $app_id,
            'order_id' => $order_id,
        ])->update(['status' => 1]);
    }
}
