<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class Advertising extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_advertising';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
}
