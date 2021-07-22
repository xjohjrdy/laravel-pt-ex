<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ShopCarts extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_carts';
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
     * 获取用户唯一的商店里的商品购物车
     * @param $app_id
     * @param $good_id
     * @param $shop_id
     * @return Model|null|static
     */
    public function getOneCart($app_id, $good_id, $shop_id,$desc)
    {
        $res = $this->where(['app_id' => $app_id, 'good_id' => $good_id, 'shop_id' => $shop_id,'desc'=>$desc])
            ->first();
        return $res;
    }

    /**
     * 通过唯一的id查询购物车数据
     * @param $id
     * @return Model|null|static
     */
    public function getOneById($id)
    {
        return $this->where(['id'=>$id])
            ->first();
    }

    /**
     * 获取用户所有商品的信息
     * @param $app_id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCarts($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->where('number','>','0')
            ->get(['id', 'app_id', 'good_id', 'shop_id', 'desc','number']);
        return $res->toArray();
    }

    /**
     * 软删除购物车商品
     * @param $app_id
     * @param $id
     * @return Model|null|static
     * @throws \Exception
     */
    public function deleteOneGood($app_id, $id)
    {
        $res = $this->where(['app_id' => $app_id, 'id' => $id])
            ->first();
        if ($res) {
            $res->delete();
        }
        return $res;
    }

    /**
     * 删除所有购物车商品
     * @param $app_id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function deleteAllGood($app_id)
    {
        $res = $this->where(['app_id' => $app_id])
            ->get();
        if ($res) {
            $res->map(function ($model) {
                $model->delete();
            });
        }
        return $res;
    }

    /**
     * 增加用户购物车内容，前提是用户购物车正常
     * @param $app_id
     * @param $good_id
     * @param $shop_id
     * @param $number
     * @return Model
     */
    public function addShopInCarts($app_id, $good_id, $shop_id, $number, $desc)
    {
        $res = $this->firstOrCreate([
            'app_id' => $app_id,
            'good_id' => $good_id,
            'shop_id' => $shop_id,
            'number' => $number,
            'desc' => $desc,
        ]);

        return $res;
    }

    /**
     * 增加用户的商品数量
     * @param $id
     * @param $number
     * @return bool
     */
    public function addNumber($id,$number)
    {
        return $this->where(['id' => $id])
            ->update(['number' => DB::raw("number + " . $number)]);
    }

    /**
     * 减少用户商品的数量
     * @param $id
     * @param $number
     * @return bool
     */
    public function reduceNumber($id,$number)
    {
        return $this->where(['id' => $id])
            ->update(['number' => DB::raw("number - " . $number)]);
    }
}
