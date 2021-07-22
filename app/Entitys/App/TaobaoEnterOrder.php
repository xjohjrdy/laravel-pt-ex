<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaobaoEnterOrder extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_enter_order';
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
     * 通过淘宝订单判断该笔订单是否存在
     */
    public function orderExists($trade_id)
    {
        $boo_res = $this->where('trade_id', (string)$trade_id)->exists();
        return $boo_res;
    }

    public function addOrder($value)
    {
        return $this->create($value);
    }

    /*
     * 通过淘宝订单列表获取
     */
    public function getOrderInfo($ids)
    {
        $res = $this->whereIn('trade_id', $ids)->get();
        if (empty($res)) {
            return false;
        }
        $orders = [];
        foreach ($res as $re) {
            $orders[$re->trade_id] = $re;
        }
        return $orders;
    }

    /**
     * 匹配唯一
     */
    public function getOneOrders($trade_id)
    {
        return $this->where(['trade_id' => (string)$trade_id])->first();
    }


}
