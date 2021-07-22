<?php

namespace App\Entitys;

use Illuminate\Database\Eloquent\Model;

class UserIndex extends Model
{
    protected $connection = 'wenzhang';
    protected $table = 'tbl_ad';
}
