<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;

class TaobaoMaidOther extends Model
{
    protected $connection = 'db001';
    protected $table = 'lc_taobao_maid';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
}
