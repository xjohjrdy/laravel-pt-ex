<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopIndexBoomRecommend extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_index_boom_recommend';
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
     * 获取爆款推荐
     * @param int $type_sell_number
     * @param int $type_new_number
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPage($type_sell_number = 1, $type_new_number = 0)
    {
        $res = $this->paginate(20);
        if (empty($type_new_number)) {
            $res = $this->orderByDesc('type_sell_number')->paginate(20);
        }
        if (empty($type_sell_number)) {
            $res = $this->orderByDesc('type_new_number')->paginate(20);
        }

        return $res;
    }

    /**
     * 小程序获取爆款推荐
     * @param int $type_sell_number
     * @param int $type_new_number
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPageMini($type_sell_number = 1, $type_new_number = 0)
    {
        $res = $this->paginate(10);
        if (empty($type_new_number)) {
            $res = $this->orderByDesc('type_sell_number')->paginate(10);
        }
        if (empty($type_sell_number)) {
            $res = $this->orderByDesc('type_new_number')->paginate(10);
        }

        return $res;
    }
}
