<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PtMoneyChangeLog extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_pt_money_change_log';
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
     * 我的币转余额日志记录
     * @param $app_id
     * @param $ptb
     * @param $rmb
     */
    public function ptb2RmbLog($app_id, $ptb, $rmb){
        $this->create([
            'app_id' => $app_id,
            'pt' => $ptb,
            'money' => $rmb,
        ]);
    }

    public function getLogByAppId($app_id){
        return $this->where(['app_id' => $app_id])->first();
    }
}
