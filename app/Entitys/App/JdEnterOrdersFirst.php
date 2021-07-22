<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class JdEnterOrdersFirst
 * @package App\Entitys\App
 * 京东订单查询model
 */
class JdEnterOrdersFirst extends Model
{
    //
    protected $connection = 'app38';
    protected $table = 'lc_jd_enter_orders_first';
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
        if (!$this->where(['orderId' => $order['orderId'], 'skuId' => $order['skuId']])->exists()) {
            $this->create($order);
        } else {
            $this->where(['orderId' => $order['orderId'], 'skuId' => $order['skuId']])->update($order);
        }
    }

    /**
     * 获取所有
     */
    public function getUserOrders($user_id, $status)
    {
        return $this->where([
            'app_id' => $user_id,
        ])->whereIn('validCode', $status)->orderByDesc('orderTime')
            ->paginate(10);
    }
}
