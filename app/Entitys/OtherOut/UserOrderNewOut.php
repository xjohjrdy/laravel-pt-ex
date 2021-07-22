<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserOrderNewOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_user_order_new';
    public $timestamps = false;


    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /*
    * 获取满足条件的最大user_id
    */
    public function getLastMonthOrder($timestamp)
    {
         $data = $this->whereBetween('confirm_time', $timestamp)
             ->WhereIn('status', [3, 4])
            ->max('user_id');
        return $data;
    }
//    /*
//     * 以每次500个user_id的范围做数据分片查询,得到每个用户的佣金总和
//     */
//    public function groupSeekData($current_user_id_scope)
//    {
//        return $this->whereBetween('user_id', $current_user_id_scope)
//        ->select(DB::raw('sum(cashback_amount) as add_value, user_id'))
//        ->groupBy('user_id')
//        ->get();
//    }


    /**
     * 获取所有
     */
    public function getUserOrders($user_id, $status)
    {
        return $this->where([
            'user_id' => $user_id,
        ])->whereIn('status', $status)
            ->paginate(10);
    }
}
