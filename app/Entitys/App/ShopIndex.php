<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopIndex extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_index';
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
     * 获取创业礼包数组
     * @return mixed
     */
    public function getVipArray()
    {
        $res = $this->where(['id' => 26])->first();
        $json_data = json_decode($res->content, true);
        return $json_data;
    }

    /**
     * 判断是否是创业礼包商品
     * @param $good_id
     * @return bool
     */
    public function isVipGoods($good_id)
    {
        $res = $this->where(['id' => 26])->first();
        $json_data = json_decode($res->content, true);
        $good_arr = array_keys($json_data);
        return in_array($good_id, $good_arr);
    }
}
