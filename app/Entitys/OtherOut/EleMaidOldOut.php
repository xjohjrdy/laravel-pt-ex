<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class EleMaidOldOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_ele_maid_old';
    public $timestamps = false;

}
