<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class ShopOrdersOneOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_shop_orders_one';
    public $timestamps = false;
}
