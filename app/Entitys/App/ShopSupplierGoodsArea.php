<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopSupplierGoodsArea extends Model
{

    protected $connection = 'app38';
    protected $table = 'lc_shop_supplier_goods_area';
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
     * 增加一个新的地区
     * @param $data
     * @return $this|Model
     */
    public function addArea($data)
    {
        return $this->create($data);
    }

    /**
     * 更新一个新的地区
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateArea($id, $data)
    {
        return $this->where(['supplier_good_id' => $id])->update($data);
    }

    /**
     * <这边可以用于商品id来查询需要的偏远地区信息>
     * 获取对应商品的偏远地区
     * @param $good_id
     * @return Model|null|static
     */
    public function getArea($good_id)
    {
        return $this->where(['good_id' => $good_id])->first();
    }

    /**
     * 获取对应商品的偏远地区《特殊查询》
     * @param $supplier_good_id
     * @return Model|null|static
     */
    public function getAreaBySupplier($supplier_good_id)
    {
        return $this->where(['supplier_good_id' => $supplier_good_id])->first();
    }
}
