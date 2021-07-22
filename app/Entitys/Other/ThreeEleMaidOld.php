<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThreeEleMaidOld extends Model
{
    protected $connection = 'db001';
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
     * 饿了么上月多级假报销记录
     */
    public function lastAllApplyData($last_month)
    {
        $data = $this->whereBetween('created_at', $last_month)
            ->where(['real' => 0])
            ->count();
        return $data;
    }

    /**
     * 获取分佣
     */
    public function getEleMaidAll($app_id, $status)
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

    /**
     * 根据指定年月获取该用户该月的佣金
     */
    public function getMaidMoneyForMonth($app_id, $type, $real, $begin_time, $end_time)
    {
        return $this
            ->where('app_id', '=', $app_id)
            ->where('type', '=', $type)
            ->where('real', '=', $real)
            ->where('created_at', '>=', $begin_time)
            ->where('created_at', '<=', $end_time)
            ->sum('maid_money');
    }
}