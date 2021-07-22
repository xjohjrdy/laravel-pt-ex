<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoipMoneyOrderMaid extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_voip_money_orders_maid';

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
     * 查询分佣情况（单个）
     * @param $id
     * @return Model|null|static
     */
    public function getById($id)
    {
        $res = $this->where(['order_id' => $id])->first();
        return $res;
    }

    /**
     * 增加分佣记录
     * @param $app_id
     * @param $order_id
     * @param $money
     * @param $order_money
     * @return $this|Model
     */
    public function addMaidLog($app_id, $order_id, $money, $order_money)
    {
        $res = $this->create([
            'app_id' => $app_id,
            'order_id' => $order_id,
            'order_money' => $order_money,
            'money' => $money,
        ]);
        return $res;
    }

    /**
     * 获取分佣记录
     * @param $app_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllMaid($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->where('created_at', '>', '2020-01-01 00:00:00')->orderByDesc('created_at')->paginate(20);
        return $res;
    }
}
