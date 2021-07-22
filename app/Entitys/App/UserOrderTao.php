<?php

namespace App\Entitys\App;

use App\Services\Common\Time as TimeTools;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class UserOrderTao extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_user_order';
    public $timestamps = false;


    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /*
     * 获取待审核订单
     */
    public function getToReviewList($app_id)
    {
        $res = $this
            ->where('user_id', $app_id)
            ->whereIn('status', [0])
            ->orderByDesc('confirm_time')
            ->paginate(10, ['id', 'is_normal', 'order_number', 'cashback_amount', 'status', 'confirm_time']);

        return $res;
    }

    /*
     * 获取审核通过订单
     */
    public function getReviewedList($app_id)
    {
        $res = $this
            ->where('user_id', $app_id)
            ->whereIn('status', [3, 4, 9])
            ->orderByDesc('confirm_time')
            ->paginate(10, ['id', 'is_normal', 'order_number', 'cashback_amount', 'status', 'confirm_time']);

        return $res;
    }

    /*
     * 获取审核未通过订单
     */
    public function getReviewFailureList($app_id)
    {
        $res = $this
            ->where('user_id', $app_id)
            ->whereIn('status', [1, 2])
            ->orderByDesc('create_time')
            ->paginate(10, ['id', 'is_normal', 'order_number', 'create_time', 'reason', 'status', 'confirm_time']);

        return $res;
    }

    /*
     * 上月时间范围内,过审订单数
     */
    public function getLastMonthOrder($timestamp, $app_id)
    {
        return $this->where('user_id' , $app_id)
            ->whereBetween('confirm_time',$timestamp)
            ->WhereIn('status', [3, 4])
            ->sum('cashback_amount');
    }
    /*
     * 次月提现余额
     */
    public function getUserTotalNextMonthCash($app_id,$get_next_month_timestamp)
    {
         $data = (float)$this->where('user_id' , $app_id)
            ->whereBetween('confirm_time',$get_next_month_timestamp)
            ->WhereIn('status', [3, 4])
            ->sum('cashback_amount');
        return $data ? $data : 0;
    }
    /*
     * 用户当年提现记录
     */
    public function getMonthLog($app_id,$int_month_timestamp)
    {
        $datas = $this->where('user_id' , $app_id)
            ->where('create_time','>=',$int_month_timestamp)
            ->WhereIn('status', [3, 4,9])
            ->get(['cashback_amount', 'create_time']);
        $need = [];
        foreach ($datas as $data) {
            $month = date('n', $data->create_time);
            $need[$month]['month'] = $month;
            $need[$month]['amount'] = empty($need[$month]['amount'])
                ? $data->cashback_amount
                : $need[$month]['amount'] + $data->cashback_amount;
        }
        return array_values($need);
    }
    /*
     * 得到指定状态订单数
     */
    public function getOrderTotalByStatus($app_id, $status)
    {
        return (int)$this->where(['user_id' => $app_id, 'status' => $status])->count('id');
    }
    /*
     * 团队本月过审订单合格数
     */
    public function getTeamCurrentMonthPassedOrder($app_id)
    {
        $current_month = TimeTools::getNextMonthTimestamp();
        $res = DB::connection('app38')
            ->select("
            select count(uo.id) as res from `lc_user_order` as uo
      INNER JOIN (
        SELECT
                        *
        FROM
                        lc_user
        WHERE
                        id = {$app_id}
        UNION
        SELECT
                        t2.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
        WHERE
                        t1.id = {$app_id}
        UNION
        SELECT
                        t3.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
        WHERE
                        t1.id = {$app_id}
        UNION
        SELECT
                        t4.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                        INNER JOIN lc_user t4 ON t3.id = t4.parent_id
        WHERE
                        t1.id = {$app_id}
        ) as tt2
        on uo.user_id = tt2.id
	and uo.status in (3,4,9) and uo.confirm_time between {$current_month[0]} and {$current_month[1]} and uo.cashback_amount > 0.1
        
            ");
        return (float)$res[0]->res;
    }
    /*
     * 团队次月提现可报销款
     */
    public function teamNextMonthCash($app_id)
    {
        $current_month = TimeTools::getNextMonthTimestamp();
        $res = DB::connection('app38')
            ->select("
            select sum(uo.cashback_amount) as res from `lc_user_order` as uo
        INNER JOIN (
        SELECT
                        *
        FROM
                        lc_user
        WHERE
                        id = {$app_id}
        UNION
        SELECT
                        t2.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
        WHERE
                        t1.id = {$app_id}
        UNION
        SELECT
                        t3.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
        WHERE
                        t1.id = {$app_id}
        UNION
        SELECT
                        t4.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                        INNER JOIN lc_user t4 ON t3.id = t4.parent_id
        WHERE
                        t1.id = {$app_id}
        ) as tt2
        on uo.user_id = tt2.id
and uo.status in (3,4) and uo.confirm_time between {$current_month[0]} and {$current_month[1]}
            ");
        return (float)$res[0]->res;
    }
    /*
     * 团队上月过审订单总金额
     */
    public function teamLastMonthOrderAmount($app_id)
    {
        $last_month = TimeTools::getLastMonthTimestamp();
        $res = DB::connection('app38')
            ->select("
            select sum(uo.cashback_amount) as res from `lc_user_order` as uo
        INNER JOIN (
        SELECT
                        *
        FROM
                        lc_user
        WHERE
                        id = {$app_id}
        UNION
        SELECT
                        t2.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
        WHERE
                        t1.id = {$app_id}
        UNION
        SELECT
                        t3.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
        WHERE
                        t1.id = {$app_id}
        UNION
        SELECT
                        t4.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                        INNER JOIN lc_user t4 ON t3.id = t4.parent_id
        WHERE
                        t1.id = {$app_id}
        ) as tt2
        on uo.user_id = tt2.id
and uo.status in (3,4) and uo.confirm_time between {$last_month[0]} and {$last_month[1]}
            ");
        return (float)$res[0]->res;
    }
}
