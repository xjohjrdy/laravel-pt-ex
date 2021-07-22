<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PretendShopOrdersMaidOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_shop_orders_pretend_maid';
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

}
