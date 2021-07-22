<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopSupplierGoods extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_supplier_goods';
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
     * 获取供应商的id信息
     */
    public function getAllByAppId($supplier_id)
    {
        return $this->where(['app_id' => $supplier_id])->where('review_status', '<>', '1')->paginate(10);
    }

    /**
     * 发布一个商品
     * @param $data
     * @return $this|Model
     */
    public function pushGoods($data)
    {
        return $this->create($data);
    }

    /**
     * 创建一个新的商品
     * @param $data
     * @return bool
     */
    public function updateGoods($data)
    {
        return $this->where([
            'id' => $data['id']
        ])->update($data);
    }

    /**
     * 审核通过
     */
    public function pass($id, $cost_price = -1, $price = -1, $tao_jd_price = -1, $vip_price = -1, $detail_desc = -1, $sort = -1)
    {
        $arr = [
            'review_status' => 1,
        ];
        if ($cost_price <> -1) {
            $arr['cost_price'] = $cost_price;
        }
        if ($price <> -1) {
            $arr['price'] = $price;
        }
        if ($tao_jd_price <> -1) {
            $arr['tao_jd_price'] = $tao_jd_price;
        }
        if ($vip_price <> -1) {
            $arr['vip_price'] = $vip_price;
        }
        if ($detail_desc <> -1) {
            $arr['detail_desc'] = $detail_desc;
        }
        if ($sort <> -1) {
            $arr['sort'] = $sort;
        }

        return $this->where([
            'id' => $id
        ])->update($arr);
    }

    /**
     * 审核失败
     * @param $id
     * @return bool
     */
    public function fail($id, $reason)
    {
        return $this->where([
            'id' => $id,
        ])->update([
            'review_status' => '2',
            'review_fail_reason' => $reason,
        ]);
    }

    /**
     *  获取单个数据
     */
    public function getById($id)
    {
        return $this->where([
            'id' => $id
        ])->first();
    }
}
