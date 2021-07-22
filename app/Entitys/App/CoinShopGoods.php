<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CoinShopGoods extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_coin_shop_goods';
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
     * 大转盘信息
     * 列表
     */
    public function getCoinTurntable()
    {
        $now_time = time();
        return $this
//            ->where('start_time', '<', $now_time)
//            ->where('end_time', '>', $now_time)
            ->where([
                'type' => 1,
                'status' => 1
            ])
            ->first(['id', 'little_img', 'price', 'coin', 'title']);
    }

    /**
     * 实物信息
     * 列表
     */
    public function getCoinMatter()
    {
        $now_time = time();
        return $this
//            ->where('start_time', '<', $now_time)
//            ->where('end_time', '>', $now_time)
            ->where([
                'type' => 0,
                'status' => 1
            ])
            ->orderByDesc('weight')
            ->paginate(10, ['id', 'little_img', 'price', 'coin', 'title', 'sale_volume']);
    }

    /**
     * 获取单个物品详情
     */
    public function getOne($id)
    {
        return $this->where(['id' => $id])->first([
            'id',
            'header_img',
            'title',
            'price',
            'coin',
            'normal_price',
            'all',
            'little_img',
            'custom',
            'type',
            'sale_volume',
        ]);
    }


    public function getAllOne($id)
    {
        return $this->where(['id' => $id])->first();
    }

    /**
     *
     * @param $good_id
     * @return bool
     */
    public function addOrDel($good_id)
    {
        $this->where(['id' => $good_id])->update([
            'volume' => DB::raw('volume - 1'),
            'sale_volume' => DB::raw('sale_volume + 1'),
        ]);
        return true;
    }


}
