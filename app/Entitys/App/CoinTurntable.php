<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinTurntable extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_coin_turntable';
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
     * 根据转盘id得到转盘信息
     */
    public function getTurntableInfo($id)
    {
        return $this->where('id', $id)
            ->where('status', 1)
            ->first(['id', 'title', 'turntable_img', 'pointer_img']);
    }
}
