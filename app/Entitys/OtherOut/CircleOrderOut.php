<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class CircleOrderOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_circle_ring_add_order';
    public $timestamps = false;

}
