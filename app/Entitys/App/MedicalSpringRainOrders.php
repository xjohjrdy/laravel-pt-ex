<?php

namespace App\Entitys\App;

use App\Services\ZhongKang\ZhongKangServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalSpringRainOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_spring_rain_orders';
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

    public function addOrder($params)
    {
        return $this->create($params);
    }

    public function upOrder($out_order_no, $params)
    {
        return $this->where('my_order', $out_order_no)->update($params);
    }

    public function getUnpaidByOrderId($order_id)
    {
        return $this->where(['my_order' => $order_id, 'status' => 0])->first();
    }

}
