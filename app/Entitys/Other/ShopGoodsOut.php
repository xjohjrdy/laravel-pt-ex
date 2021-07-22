<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;

class ShopGoodsOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_shop_goods';
    public $timestamps = false;
}
