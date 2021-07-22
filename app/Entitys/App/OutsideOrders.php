<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutsideOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_outside_orders';
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
     * @param $uid
     * @param $order_id
     * @param $remark
     * @param $money
     * @return Model
     */
    public function createNewOrder($uid,$order_id,$remark,$money)
    {
        $res = $this->create([
            'order_id'=>$order_id,
            'remark'=>$remark,
            'uid'=>$uid,
            'money'=>$money,
            'status'=>1
        ]);

        return $res;
    }
}
