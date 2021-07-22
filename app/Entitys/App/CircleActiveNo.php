<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleActiveNo extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_active_no';
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
     * 查询动态是否被禁止
     * @param $app_id
     * @return Model|null|static
     */
    public function getByAppId($app_id)
    {
        return $this->where(['app_id' => $app_id])->first();
    }
}
