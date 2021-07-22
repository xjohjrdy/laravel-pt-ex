<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinVirtualGoods extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_coin_virtual_goods';
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

    /*
     * 根据类型得到配置的虚拟商品
     */
    public function getConfigByType($type)
    {
        return $this->where('type', $type)
            ->get(['id', 'coin_number', 'real_price']);
    }

}
