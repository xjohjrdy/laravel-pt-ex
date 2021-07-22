<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class GrowthUserValueConfig extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_growth_user_value_config';

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
}
