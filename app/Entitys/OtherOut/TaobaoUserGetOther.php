<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaobaoUserGetOther extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_taobao_user_get';


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
     * 获取用户的记录信息
     */
    public function getByAppId($app_id)
    {
        return $this->where(['app_id' => $app_id])->orderByDesc('created_at')->paginate(10);
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
}
