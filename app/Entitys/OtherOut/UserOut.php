<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class UserOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_user';
    public $timestamps = false;
}
