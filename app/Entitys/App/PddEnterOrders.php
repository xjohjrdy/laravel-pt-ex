<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PddEnterOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_pdd_enter_orders';
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
        if (!$this->where(['order_sn' => $order['order_sn'], 'goods_id' => $order['goods_id']])->exists()) {
            $this->create($order);
        } else {
            $this->where(['order_sn' => $order['order_sn'], 'goods_id' => $order['goods_id']])->update($order);
        }
    }

    /*
  * 拼多多51天内订单计数总和
  */
    public function getOrdersSum($start_time)
    {
        return $this->where('created_at', '>=', $start_time)
            ->where('order_status', '>=', 1)
            ->count();
    }

    /*
     * 拼多多符合条件数据
     */
    public function getCutData($page_size, $page, $start_time)
    {
        return $this->where('created_at', '>=', $start_time)
            ->where('order_status', '>=', 1)
            ->forPage($page, $page_size)
            ->get();
    }

    /**
     * 获取所有
     */
    public function getUserOrders($user_id, $status)
    {
        return $this->where([
            'app_id' => $user_id,
        ])->whereIn('order_status', $status)->orderByDesc('created_at')
            ->paginate(10);
    }

    /**
     * 必须唯一
     * @param $order_id
     * @param $father_id
     * @return Model|null|static
     */
    public function getPddOne($order_id, $father_id)
    {
        return $this->where([
            'order_sn' => $order_id,
            'app_id' => $father_id,
        ])->first();
    }
}
