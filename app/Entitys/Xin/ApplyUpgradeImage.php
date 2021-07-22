<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class ApplyUpgradeImage extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_apply_upgrade_image';
    public $timestamps = false;
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
}
