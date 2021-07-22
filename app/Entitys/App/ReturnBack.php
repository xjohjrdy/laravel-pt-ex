<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnBack extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_return_back';
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
     * 查询或者创建一个新的退换货申请
     * @param $app_id
     * @param $orders_one_id
     * @return Model
     */
    public function createNewBack($app_id, $orders_one_id)
    {
        $res = $this->firstOrCreate(['app_id' => $app_id, 'orders_one_id' => $orders_one_id], [
            'app_id' => $app_id,
            'orders_one_id' => $orders_one_id,
            'status' => 0
        ]);
        return $res;
    }

    /**
     * 根据用户查询他的所有退款订单
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getAllUserBack($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->orderBy('created_at', 'desc')->paginate(20);;
        return $res;
    }

    /**
     * 获得唯一的记录
     * @param $app_id
     * @param $orders_one_id
     * @return Model|null|static
     */
    public function getBack($app_id, $orders_one_id)
    {
        $res = $this->where(['app_id' => $app_id, 'orders_one_id' => $orders_one_id])->first();
        return $res;
    }

    /**
     * 获得唯一的记录
     * @param $id
     * @return Model|null|static
     */
    public function getBackById($id)
    {
        $res = $this->where(['id' => $id])->first();
        return $res;
    }

    /**
     * 更新参数信息
     * @param $app_id
     * @param $orders_one_id
     * @param $remark_desc
     * @param $remark
     * @param $remark_img
     * @param $type
     * @param int $remark_type
     * @return bool
     */
    public function updateBack($app_id, $orders_one_id, $remark_desc, $remark, $remark_img, $type, $remark_type = 0)
    {
        $res = $this->where(['app_id' => $app_id, 'orders_one_id' => $orders_one_id])->update([
            'remark' => $remark,
            'remark_desc' => $remark_desc,
            'remark_img' => $remark_img,
            'remark_type' => $remark_type,
            'type' => $type,
            'status' => 1
        ]);
        return $res;
    }

    /**
     * 更新快递信息
     * @param $app_id
     * @param $orders_one_id
     * @param $express
     * @return bool
     */
    public function updateExpress($app_id, $orders_one_id, $express)
    {
        $res = $this->where(['app_id' => $app_id, 'orders_one_id' => $orders_one_id])->update([
            'express' => $express,
            'status' => 4
        ]);
        return $res;
    }

    /**
     * 更新要发送的地址信息
     * @param $app_id
     * @param $orders_one_id
     * @param $address
     * @param $phone
     * @param $collection
     * @return bool
     */
    public function updateReturnBack($app_id, $orders_one_id, $address, $phone, $collection)
    {
        $res = $this->where(['app_id' => $app_id, 'orders_one_id' => $orders_one_id])->update([
            'express_back_address' => $address,
            'express_back_collection' => $collection,
            'express_back_phone' => $phone,
            'status' => 6
        ]);
        return $res;
    }
}
