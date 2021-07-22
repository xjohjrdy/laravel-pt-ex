<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class AdminBehaviorLog extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_admin_behavior_log';
    public $timestamps = false;
}
