<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class WorkOrderImg extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_work_order_img';
    public $timestamps = false;
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
}
