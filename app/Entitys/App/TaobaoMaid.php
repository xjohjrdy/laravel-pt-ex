<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class TaobaoMaid extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_maid';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
}
