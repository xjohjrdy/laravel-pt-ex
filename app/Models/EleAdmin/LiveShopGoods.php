<?php

namespace App\Models\EleAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\LiveShopGoodsScopes;

class LiveShopGoods extends Model
{
    use LiveShopGoodsScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_live_shop_goods';

    const READ_IS_YES = 1;
    const READ_IS_NO = 0;

    public static $readList = [
        self::READ_IS_YES => '是',
        self::READ_IS_NO => '否',
    ];

    public function goods()
    {
        return $this->hasOne('App\Models\EleAdmin\ShopGoods', 'id', 'good_id');
    }
}