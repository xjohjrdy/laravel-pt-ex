<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InviteRealUser extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_pull_real_user';
    public $timestamps = false;
}
