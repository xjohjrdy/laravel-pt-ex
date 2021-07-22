<?php

namespace App\Entitys\App;

use App\Services\Common\CommonFunction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_orders';
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
     * 创建模型保存并导出
     * @param $app_id
     * @param $address_id
     * @param $price
     * @param $all_profit_value
     * @param $isApplet 0 默认，非小程序下单， 1 小程序下单
     * @return $this|Model
     */
    public function addOrders($app_id, $address_id, $price, $all_profit_value, $isApplet = 0)
    {
        $common_function = new  CommonFunction();
        $order_id = date('YmdHis') . $common_function->random(5);
        if($isApplet == 1) {
            $order_id = 'WXAPPLET' . $order_id;
        }
        $model = $this->create([
            'order_id' => $order_id,
            'app_id' => $app_id,
            'address_id' => $address_id,
            'price' => $price,
            'all_profit_value' => $all_profit_value,
            'status' => 0,
        ]);
        return $model;
    }

    /**
     * 通过id找到唯一的订单
     * @param $id
     * @return Model|null|static
     */
    public function getById($id)
    {
        return $this->where(['id' => $id])->first();
    }

    /**
     * 更新订单状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function updateStatusOrders($id, $status)
    {
        $m = ShopOrders::find($id);
        if ( empty($m) ) {
            return 0;
        }
        $m->status = $status;
        $m->save();
        return 1;
    }

    /**
     * 通过id找到唯一的订单
     * @param $id
     * @return Model|null|static
     */
    public function getByOrderId($id)
    {
        return $this->where(['order_id' => $id])->first();
    }

    /**
     * 通过id，进行购买第二步的用户更新操作（重新更新用户的订单信息，进行最后的支付）
     * @param $id
     * @param $real_price
     * @param $ptb_number
     * @param $address_id
     * @param $type
     * @return bool
     */
    public function updateOrders($id, $real_price, $ptb_number, $address_id, $type)
    {
        return $this->where(['id' => $id])->update([
            'real_price' => $real_price,
            'ptb_number' => $ptb_number,
            'rate' => 1,
            'address_id' => $address_id,
            'type' => $type,
        ]);
    }

    /**
     * 查询所有的订单数据
     * @param $app_id
     * @param int $status
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllUserOrders($app_id,$status = 99)
    {
        if ($status == 99){
            $res = $this->where(['app_id'=>$app_id])->orderByDesc('updated_at')->paginate(10);
        }else{
            $res = $this->where(['app_id'=>$app_id,'status'=>$status])->orderByDesc('updated_at')->paginate(10);
        }

        return $res;
    }
    public function getNumberAndMoney($app_id,$obj_three_data)
    {
        dd(($obj_three_data.status));
        $res_data = $this->join($obj_three_data,'lc_shop_orders.app_id','=',$obj_three_data->id)
            ->where('lc_shop_orders.status',3)
            ->where($obj_three_data->status,1)
            ->get();
        if (empty($res_data[0])) {
            return ['money' => 0, 'number' => 0];
        }
        return $res_data[0];
    }
}
