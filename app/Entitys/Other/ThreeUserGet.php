<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ThreeUserGet  用户账户提现表
 * @package App\Entitys\Other
 */
class ThreeUserGet extends Model
{
    //lc_test_jd_wh
    protected $connection = 'db001';
    protected $table = 'lc_three_user_get';
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
     * 获取用户的提现记录
     * @param $app_id
     * @return ThreeUserGet|Model|null
     */
    public function getUserWithDrawList($app_id){
        $list = $this->where(['app_id' => $app_id])->orderByDesc('created_at')->paginate(20);
        return $list;
    }

    /**
     * 获取总数
     */
    public function getSum($app_id, $status = -1)
    {
        if ($status == -1) {
            return $this->where(['app_id' => $app_id])->sum('money');
        }

        return $this->where(['app_id' => $app_id, 'type' => $status])->sum('money');
    }

    /**
     * 增加用户数据
     * @param $data
     */
    public function addLog($data)
    {
        return $this->create($data);
    }

    /*
   * 得到用户已经在处理申请的信息
   */
    public function getDisposeApplyCash($app_id)
    {
        return $this->where(['app_id' => $app_id, 'type' => 0])->count('id');
    }

    /*
    * 获取当月用户申请的数量
    */
    public function getMonthApplyCount($app_id)
    {
        $begin = mktime(0, 0, 0, date('m') , 1, date('Y'));
        $end = mktime(23, 59, 59, date('m'), date('t', $begin), date('Y'));

        $begin = date('Y-m-d H:i:s', $begin);
        $end = date('Y-m-d H:i:s', $end);
        return $this->where(['app_id' => $app_id, 'type' => 1])->whereBetween('created_at', [$begin, $end])->count('id');
    }

}
