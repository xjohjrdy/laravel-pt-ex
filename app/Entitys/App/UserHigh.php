<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class UserHigh extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_user_high';

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
     * 拿到当前用户的特殊转正状态记录
     * @param $app_id
     * @return Model|null|static
     */
    public function getUserHigh($app_id)
    {
        $res = $this->firstOrCreate([
            'app_id' => $app_id,
        ]);

        return $res;
    }

    /**
     * 添加记录，注意区分第一次，与传入
     * @param $app_id
     * @param $remark 新的值
     * @param string $old_remark 传入旧的值
     * @param int $time 是否是第一次
     * @return bool
     */
    public function addLog($app_id, $remark,$old_remark = '',$time = 1)
    {
        if ($time == 1) {
            $res = $this->where(['app_id'=>$app_id])
                ->update([
                    'remark'=>$remark,
                    'number'=> DB::raw("number + " . 1),
                ]);
        } else {
            $res = $this->where(['app_id'=>$app_id])
                ->update([
                    'remark'=>$old_remark.','.$remark,
                    'number'=>DB::raw("number + " . 1),
                ]);
        }
        return $res;
    }
    /*
     * 得到用户等级信息
     */
    public function getUserHighInfo($app_id)
    {
        return $this->where('app_id',$app_id)->first();
    }
}
