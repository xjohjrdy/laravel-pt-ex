<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityAlertConfig extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_activity_alert_config';
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
     * 获取首页弹窗配置
     */
    public function getIndex()
    {
        $time = time();
        $res =  $this->where(['id' => 1, 'show_flag' => 1])->where('begin_time', '<=', $time)
            ->where('end_time', '>=', $time)->first([
                'image_url', 'text', 'redirect_type', 'redirect_url', 'extra_params', 'show_flag', 'index', 'grade', 'hide_flag', 'desc', 'login_flag', 'begin_time', 'end_time', 'min_ios_version', 'min_android_version'
            ]);
        if(empty($res)){
            $res = json_decode("{}");
        }
        return $res;
    }
}
