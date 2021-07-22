<?php

namespace App\Models\EleAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\ShopGoodsScopes;

class ShopGoods extends Model
{
    use ShopGoodsScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_shop_goods';

    const STATUS_UP = 1;
    const STATUS_DOWN = 0;

    public static $statusList = [
        self::STATUS_UP => '上架',
        self::STATUS_DOWN => '下架',
    ];
}