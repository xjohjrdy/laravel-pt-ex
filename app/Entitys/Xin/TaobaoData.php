<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class TaobaoData extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_data';
    public $timestamps = false;
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /*
     * 得到指定订单号信息
     */
    public function getOrderInfo($order_number)
    {
        return $this->where('order_number', (string)$order_number)->first();
    }
}
