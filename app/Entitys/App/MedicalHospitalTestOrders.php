<?php

namespace App\Entitys\App;

use App\Services\ZhongKang\ZhongKangServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalHospitalTestOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_hospital_test_orders';
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

    /*
     * get Unpaid order
     */
    public function getUnpaidByOrderId($order_id)
    {
        return $this->where(['my_order' => $order_id, 'status' => 0])->first();
    }

    /**
     * get orders by my
     * @param $app_id
     */
    public function getOrders($app_id, $status = 1)
    {
        $change_status = [1];

        if ($status == 1) {
            $change_status = [1];
        }
        if ($status == 2) {
            $change_status = [2];
        }
        if ($status == 3) {
            $change_status = [5, 6];
        }
        if ($status == 4) {
            $change_status = [4];
        }

        return $this->where(['app_id' => $app_id])
            ->whereIn('status', $change_status)->paginate(10);
    }

    /**
     * can refund
     */
    public function getCanRefundOrder($app_id, $id)
    {
        return $this->where(['app_id' => $app_id, 'id' => $id, 'status' => '1'])->first();
    }

    /**
     * refund
     */
    public function refundOrder($app_id, $id, $reason)
    {
        return $this->where(['app_id' => $app_id, 'id' => $id, 'status' => '1'])->update([
            'status' => 4,
            'no_in_reason' => $reason,
        ]);
    }

    /**
     * because order need to write off ,just to de update orders
     */
    public function updateOrder($app_id)
    {
        $zhongKangServices = new ZhongKangServices();
        $all_orders = $this->where(['app_id' => $app_id, 'status' => 1])->get();
        foreach ($all_orders as $order) {
            $res = $zhongKangServices->consumeStatus($order->my_order);
            if (empty($res)) {
                continue;
            }
            $res_arr = json_decode($res, true);
            if (empty($res_arr['data']['consume_status'])) {
                continue;
            }
            if ($res_arr['data']['consume_status'] == 2) {
                $this->writeOff($order->id);
            }
        }

        return 1;
    }

    /**
     * fit order
     * @param $id
     * @return bool
     */
    public function writeOff($id)
    {
        return $this->where(['id' => $id])->update([
            'status' => 2
        ]);
    }
}
