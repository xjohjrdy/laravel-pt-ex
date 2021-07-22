<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaobaoZeroBuy extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_zero_buy';

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
     * @return \Illuminate\Support\Collection
     * 开放商品
     */
    public function getIndex()
    {
        $time = time();
        return $this->where('start_time', '<', $time)
            ->where('end_time', '>', $time)
            ->limit(5)
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     * 封闭商品
     */
    public function getNo()
    {
        $time = time();
        return $this->where('end_time', '<', $time)
            ->limit(5)
            ->get();
    }

    /**
     * 商品
     * @param $good_id
     * @return Model|null|static
     */
    public function getOne($good_id)
    {
        return $this->where(['good_id' => $good_id])->first();
    }

}
