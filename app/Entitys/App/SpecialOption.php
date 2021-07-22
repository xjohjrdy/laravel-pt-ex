<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SpecialOption extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_special_option';
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
     * 获取当前所有需要查询的用户
     * @return \Illuminate\Support\Collection
     */
    public function getALlUser()
    {
        $res = $this->groupBy('app_id')->get(['app_id']);

        return $res;
    }

    /**
     * 获取所有用户的当前期权记录
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getAllOptionByAppId($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->orderBy('compute_time', 'asc')->get();
        return $res;
    }

    /**
     * 清理掉当前用户的期权
     * @param $app_id
     * @return bool|null
     * @throws \Exception
     */
    public function clearUserOption($app_id)
    {
        return $this->where(['app_id' => $app_id])->delete();
    }

    /**
     * 增加一个字段
     * @param $app_id
     * @param $active_value
     * @return Model
     */
    public function addNewOption($app_id, $active_value)
    {
        if (date('j') == 1) {
            $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
            $today_month = strtotime(date('Y-m', $begin));
        } else {
        $today_month = strtotime(date('Y-m', time()));
        }
        $option_value = $active_value * 20;
        $res = $this->updateOrCreate(['app_id' => $app_id, 'compute_time' => $today_month], [
            'app_id' => $app_id,
            'compute_time' => $today_month,
            'option_value' => $option_value,
            'active_value' => $active_value,
        ]);
        return $res;
    }

    /*
     * 期权收益
     */
    public function countMoney($app_id)
    {
        return $this->where('app_id', $app_id)
            ->sum('option_value');
    }
}
