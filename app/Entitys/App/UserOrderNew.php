<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserOrderNew extends Model
{
    protected $connection = 'app38';
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


    /**
     * 获取所有
     */
    public function getUserOrders($user_id, $status)
    {
        return $this->where([
            'user_id' => $user_id,
        ])->whereIn('status', $status)->orderByDesc('create_time')
            ->paginate(10);
    }
}
