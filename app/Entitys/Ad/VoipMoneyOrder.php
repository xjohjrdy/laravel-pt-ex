<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoipMoneyOrder extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_voip_money_order';

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
     * 生成订单
     * @param $app_id
     * @param $to_phone
     * @param $real_price
     * @param $price
     * @param $title
     * @param $remark
     * @return $this|Model
     */
    public function generateOrder($app_id, $to_phone, $real_price, $price, $title, $remark, $image, $buy_type, $time)
    {
        $res = $this->create([
            'app_id' => $app_id,
            'phone' => $to_phone,
            'real_price' => $real_price,
            'price' => $price,
            'title' => $title,
            'remark' => $remark,
            'image' => $image,
            'time' => $time,
            'buy_type' => $buy_type,
            'status' => 0,
        ]);

        return $res;
    }

    /**
     * 生成订单（新）
     * 血和泪的教训，教育我们要多用一个order_id
     * @param $app_id
     * @param $to_phone
     * @param $real_price
     * @param $price
     * @param $title
     * @param $remark
     * @return $this|Model
     */
    public function generateNewOrder($app_id, $to_phone, $real_price, $price, $title, $remark, $image, $buy_type, $time, $order_id)
    {
        $res = $this->create([
            'app_id' => $app_id,
            'order_id' => $order_id,
            'phone' => $to_phone,
            'real_price' => $real_price,
            'price' => $price,
            'title' => $title,
            'remark' => $remark,
            'image' => $image,
            'time' => $time,
            'buy_type' => $buy_type,
            'status' => 0,
        ]);

        return $res;
    }

    /**
     * 更新订单状态
     * @param $id
     * @return bool
     */
    public function updateOrderStatus($id)
    {
        $m = VoipMoneyOrder::find($id);
        if (empty($m)) {
            return 0;
        }
        $m->status = 1;
        $m->save();
        return 1;
    }

    /**
     * 通过id找到订单
     * @param $id
     * @return Model|null|static
     */
    public function getById($id)
    {
        $res = $this->where(['id' => $id])->first();
        return $res;
    }

    /**
     * 订单id查询
     * @param $order_id
     * @return Model|null|static
     */
    public function getByOrderId($order_id)
    {
        $res = $this->where(['order_id' => $order_id])->first();
        return $res;
    }

    /**
     * 获取所有订单
     * @param $app_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllOrderByUser($app_id)
    {
        $limit = 20;
        $res = $this->where(['app_id' => $app_id, 'status' => 1])->where('created_at', '>', '2020-01-01 00:00:00')->orderByDesc('created_at')->paginate($limit);
        return $res;
    }
}
