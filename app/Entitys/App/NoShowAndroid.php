<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoShowAndroid extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_no_show_android_from_version';
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
     * 获取单个版本
     */
    public function getOneVersion()
    {
        return $this->where(['id' => 1])->value('version');
    }
}
