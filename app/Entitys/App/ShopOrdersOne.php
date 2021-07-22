<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopOrdersOne extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_orders_one';
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
     * @param $app_id
     * @param $order_id
     * @param $good_id
     * @param $shop_id
     * @param $desc
     * @param $number
     * @param $real_price
     * @param $profit_value
     * @return $this|Model
     */
    public function addOrdersOne($app_id, $order_id, $good_id, $shop_id, $desc, $number, $real_price, $profit_value)
    {
        $model = $this->create([
            'app_id' => $app_id,
            'order_id' => $order_id,
            'good_id' => $good_id,
            'shop_id' => $shop_id,
            'desc' => $desc,
            'number' => $number,
            'real_price' => $real_price,
            'profit_value' => $profit_value,
            'status' => 0,
        ]);
        return $model;
    }

    /**
     * 获取订单相关的所有商品信息
     * @param $order_id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllGoods($order_id, $status = 99)
    {
        if ($status == 99) {
            $models = $this->where(['order_id' => $order_id])->get();
        } else {
            $models = $this->where(['order_id' => $order_id, 'status' => $status])->get();
        }

        return $models;
    }

    /**
     * 更新订单相对应的商品的状态
     * @param $order_id
     * @param $status
     * @return bool
     */
    public function updateStatusByOrderId($order_id, $status)
    {
        $res = $this->where(['order_id' => $order_id])->update([
            'status' => $status,
        ]);
        return $res;
    }

    /**
     * 更新订单相对应的商品的状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function updateStatusById($id, $status)
    {
        $res = $this->where(['id' => $id])->update([
            'status' => $status,
        ]);
        return $res;
    }

    /**
     * 查询所有的订单数据
     * @param $app_id
     * @param int $status
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllUserOrders($app_id, $status = 99)
    {
        if ($status == 99) {
            $res = $this->where(['app_id' => $app_id])->get();
        } else {
            $res = $this->where(['app_id' => $app_id, 'status' => $status])->get();
        }

        return $res;
    }

    /**
     * 修改单个订单商品的状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function changeStatus($id, $status)
    {
        return $this->where(['id' => $id])
            ->update([
                'status' => $status
            ]);
    }

    /**
     * 获取单个订单
     * @param $id
     * @return Model|null|static
     */
    public function getOneById($id)
    {
        return $this->where(['id' => $id])
            ->first();
    }

    /**
     * 用来探明某件商品已经被购买了几次
     * @param $app_id
     * @param $good_id
     * @return int
     */
    public function getOrderOneByAppIdAndGoodId($app_id, $good_id)
    {
        $res = $this->where(['app_id' => $app_id, 'good_id' => $good_id])
            ->get()->count();
        return $res;
    }

    /**
     * 偏远地区邮费资料备份
     */
    public function noAreaPost($id, $price)
    {
        return $this->where(['id' => $id])
            ->update([
                'postage' => $price
            ]);
    }
}
