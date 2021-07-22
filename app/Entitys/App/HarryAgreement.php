<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HarryAgreement extends Model
{
    //lc_test_jd_wh
    protected $connection = 'app38';
    protected $table = 'lc_harry_agreement';
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

}