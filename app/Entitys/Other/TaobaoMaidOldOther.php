<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class TaobaoMaidOldOther extends Model
{
    protected $connection = 'db001';
    protected $table = 'lc_taobao_maid_old';
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
     * 上月报销记录
     */
    public function lastAllApplyData($last_month)
    {
        $data = $this->whereBetween('created_at', $last_month)
            ->where(['real' => 0])
            ->count();
        return $data;
    }

    /*
     * 以每次500个app_id的范围做数据分片查询,得到每个用户的佣金总和
     */
    public function groupSeekData($page)
    {
//        return $this->whereBetween('app_id', $current_user_id_scope)
//            ->select(DB::raw('sum(maid_money) as add_value, user_id'))
//            ->groupBy('app_id')
//            ->get();
        return $this->groupBy('app_id')
            ->forPage($page, 1000)
            ->select(DB::raw('sum(maid_money) as add_value, app_id'))
            ->get();
    }

    /**
     * 获取对应时间
     */
    public function getTime($app_id, $type = 1, $time = 0)
    {

        $begin = mktime(0, 0, 0, date('m') - $time, 1, date('Y'));
        $end = mktime(23, 59, 59, date('m') - $time, date('t', $begin), date('Y'));

        $begin = date('Y-m-d H:i:s', $begin);
        $end = date('Y-m-d H:i:s', $end);
        return $this
            ->where('created_at', '>=', $begin)
            ->where('created_at', '<=', $end)
            ->where('app_id', '=', $app_id)
            ->where('type', '=', $type)
            ->sum('maid_money');
    }

    /**
     * 根据指定年月获取该用户该月的佣金
     */
    public function getMaidMoneyForMonth($app_id, $type, $real, $begin_time, $end_time)
    {
        return $this
            ->where('app_id', '=', $app_id)
            ->where('type', '=', $type)
            ->where('real', '=', $real)
            ->where('created_at', '>=', '2020-01-01 00:00:00')
            ->where('created_at', '>=', $begin_time)
            ->where('created_at', '<=', $end_time)
            ->sum('maid_money');
    }


    /**
     * 特殊处理统计
     * @param $id
     * @return mixed
     * 这里返回的参数，是一个整数，也就是符合标准的总数
     */
    public function getTimeMySpecial($app_id, $type = 1, $time = 0)
    {

        $begin = mktime(0, 0, 0, date('m') - $time, 1, date('Y'));
        $end = mktime(23, 59, 59, date('m') - $time, date('t', $begin), date('Y'));

        $begin = date('Y-m-d H:i:s', $begin);
        $end = date('Y-m-d H:i:s', $end);

        $sql = "
	SELECT sum(t1.`maid_money`) as ct FROM
	lc_taobao_maid_old as t1,lc_user as t2 WHERE
	t1.father_id = t2.id 
	AND t2.parent_id = " . $app_id . "
  AND t1.type = " . $type . "
  AND t1.app_id = " . $app_id . "
  AND t1.created_at >= '" . $begin . "'
  AND t1.deleted_at is NULL
  AND t1.created_at <= '" . $end . "'
        ";

        $res = DB::connection("app38")->select($sql);
        return $res[0]->ct;
    }

    /**
     * 获取分佣
     */
    public function getTaobaoMaidAll($app_id, $status)
    {
        if ($status == 2) {
            return $this->onlyTrashed()->where([
                'type' => 1,
                'app_id' => $app_id
            ])->orderByDesc('created_at')->paginate(10);
        }
        return $this->where([
            'app_id' => $app_id,
            'type' => 1,
            'real' => $status,
        ])->orderByDesc('created_at')->paginate(10);
    }
}
