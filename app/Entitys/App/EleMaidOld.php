<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EleMaidOld extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_ele_maid_old';
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
     * 获取饿了么分佣
     */
    public function getEleMaidAll($app_id, $status)
    {
        if ($status == 2) {
            return $this->onlyTrashed()->where([
                'type' => 2,
                'app_id' => $app_id
            ])->orderByDesc('created_at')->paginate(10);
        }
        return $this->where([
            'app_id' => $app_id,
            'type' => 2,
            'real' => $status,
        ])->orderByDesc('created_at')->paginate(10);
    }

    /*
     * 获取饿了么直属分佣
     */
    public function getDirectlyEleMaidAll($arr_id, $status)
    {
        if ($status == 2) {
            return $this->onlyTrashed()->whereIn('father_id', $arr_id)->where('type', 1)->orderByDesc('created_at')->paginate(10);
        }
        return $this->where([
            'type' => 1,
            'real' => $status,
        ])->whereIn('father_id', $arr_id)->orderByDesc('created_at')->paginate(10);
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
}
