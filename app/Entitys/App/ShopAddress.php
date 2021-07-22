<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopAddress extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_address';
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
     * 获得用户所有的地址
     * @param $app_id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllAddress($app_id)
    {
        $address = $this->where(['app_id' => $app_id])->get();
        return $address;
    }

    /**
     * 获得单个用户默认地址
     * @param $app_id
     * @return Model|null|static
     */
    public function getUserDefaultAddress($app_id)
    {
        $address = $this->where(['app_id' => $app_id,'is_default'=>1])->first();
        return $address;
    }

    /**
     * 获得单个用户的地址
     * @param $id
     * @return Model|null|static
     */
    public function getOneAddress($id)
    {
        $address = $this->withTrashed()->where(['id' => $id])->first();
        return $address;
    }

    /**
     * 软删除地址
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteAddress($id)
    {
        return $this->where(['id'=>$id])->delete();
    }

    /**
     * 更新地址
     * @param $id
     * @param $collection
     * @param $phone
     * @param $zone
     * @param $detail
     * @param $is_default
     * @return bool
     */
    public function updateOneAddress($id, $collection, $phone, $zone, $detail, $is_default)
    {
        return $this->where(['id' => $id])->update([
            'collection' => $collection,
            'phone' => $phone,
            'zone' => $zone,
            'detail' => $detail,
            'is_default' => $is_default,
        ]);
    }

    /**
     * 把所有默认的全部置为0
     * @param $app_id
     * @return bool
     */
    public function unsetAllDefault($app_id)
    {
        return $this->where(['app_id' => $app_id])->update(['is_default' => 0]);
    }

    /**
     * 创建地址
     * @param $app_id
     * @param $collection  收货人
     * @param $phone 用户电话
     * @param $zone 用户所在地区
     * @param $detail 用户详细地址
     * @param $is_default 是否是默认的地址
     * @return $this|Model
     */
    public function addShopAddress($app_id, $collection, $phone, $zone, $detail, $is_default)
    {
        $res = $this->Create([
            'app_id' => $app_id,
            'collection' => $collection,
            'phone' => $phone,
            'zone' => $zone,
            'detail' => $detail,
            'is_default' => $is_default,
        ]);
        return $res;
    }
}
