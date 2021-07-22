<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class RechargeOrder extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_aljbgp_order';
    public $timestamps = false;

    /**
     * 获取用户当前已存在订单类型
     * 3：早期代理商用户，存在300订单，只能购买10,2700
     * 8：后期代理商用户，存在800订单，只能购买10,2200
     * 1:合伙人用户：只能购买10
     * 0:普通用户，只能购买10，800,3000
     * @param $uid
     * @return int
     */
    public function getUserType($uid)
    {
        $three = $this->where([
            'uid' => $uid,
            'groupid' => '23',
            'price' => '300',
            'status' => 2,
        ])->first();

        $eight = $this->where([
            'uid' => $uid,
            'groupid' => '23',
            'price' => '800',
            'status' => 2,
        ])->first();

        $partner = $this->where([
            'uid' => $uid,
            'groupid' => '24',
            'status' => 2,
        ])->first();

        if ($three && !$partner) {
            return 3;
        }

        if ($eight && !$partner) {
            return 8;
        }

        if ($partner) {
            return 1;
        }

        return 0;
    }

    /**
     * 获取当前用户的订单
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getUserOrders($uid)
    {
        $orders = $this->where(['uid' => $uid])->get(['orderid', 'status', 'groupid', 'price', 'desc', 'submitdate']);
        return $orders;
    }

    /**
     *
     * 订单号获取订单
     * @param $orderId
     * @return Model|null|static
     */
    public function getOrdersById($orderId)
    {
        $order = $this->Where('submitdate', '>', '1577808000')->where(['orderid' => $orderId])->first(['uid', 'price', 'status']);
        return $order;
    }

    /**
     * 更新订单状态
     * @param $order_id
     * @return bool
     */
    public function updateOrderStatus($order_id)
    {
        $m = RechargeOrder::where('orderid', $order_id)->first();
        if ( empty($m) ) {
            return 0;
        }
        $m->setKeyName('orderid');
        $m->status = 2;
        $m->save();
        return 1;
    }
}
