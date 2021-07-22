<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class ShopVipBuyOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_shop_vip_buy';
    public $timestamps = false;
}
