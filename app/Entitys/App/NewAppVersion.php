<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewAppVersion extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_new_app_version';
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
     * 获取当前app版本信息
     * @param $tag
     * @return Model|null|static
     */
    public function getAppVersion($tag)
    {
        $res = $this->where(['tag' => $tag])->orderByDesc('id')->first();
        return $res;
    }
}
