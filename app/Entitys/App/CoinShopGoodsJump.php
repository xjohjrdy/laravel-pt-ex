<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinShopGoodsJump extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_coin_shop_goods_jump';
    use SoftDeletes;

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 获取跳出弹窗
     */
    public function getJump()
    {
        return $this->where(['id' => 1])->first();
    }

}
