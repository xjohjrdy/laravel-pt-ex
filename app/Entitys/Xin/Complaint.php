<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_complaint';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
}
