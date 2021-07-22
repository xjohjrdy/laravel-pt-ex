<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleUserAdd extends Model
{

    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_user_add_order';
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
     * 创建或者是更新订单
     * @param $order_id
     * @param $data
     * @return Model
     */
    public function createOrUpdateAdd($order_id, $data)
    {
        $res = $this->updateOrCreate([
            'order_id' => $order_id
        ], [
            'circle_id' => $data['circle_id'],
            'order_id' => $order_id,
            'money' => $data['money'],
            'app_id' => $data['app_id'],
            'to_app_id' => $data['to_app_id'],
            'use_time' => $data['use_time'],
            'status' => '0',
            'buy_type' => $data['buy_type'],
        ]);
        return $res;
    }

    /**
     * 支付成功
     * @param $order_id
     * @return bool
     */
    public function checkOrder($order_id)
    {
        $res = $this->where(['order_id' => $order_id])->update([
            'status' => '1',
        ]);
        return $res;
    }

    /**
     * 获取单笔订单
     * @param $order_id
     * @return Model|null|static
     */
    public function getOrder($order_id)
    {
        $res = $this->where(['order_id' => $order_id])->first();
        return $res;
    }
}
