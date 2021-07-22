<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinShopOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_coin_shop_orders';
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


    public function getOrder($order_id)
    {
        return $this->where(['order_id' => $order_id])->first();
    }

    /**
     * 更新订单状态
     */
    public function updateOrders($order_id)
    {
        return $this->where(['order_id' => $order_id])->update([
            'status' => 1
        ]);
    }

    /**
     * 获得单次限制
     * @param $app_id
     * @param $good_id
     * @return Model|null|static
     */
    public function getAppidGoodid($app_id, $good_id)
    {
        $start_time = date('Y-m-d', time());
        $end_time = date('Y-m-d', (time() + 86400));

        return $this
            ->where('created_at', '<', $end_time)
            ->where('created_at', '>', $start_time)
            ->where([
                'app_id' => $app_id,
                'good_id' => $good_id,
            ])->first();

    }

    /**
     * 获取用户订单列表
     */
    public function getPage($app_id)
    {
        return $this->where('status', '>', '0')->where(['app_id' => $app_id])->orderByDesc('id')->paginate(10, [
            'order_id',
            'collection',
            'phone',
            'zone',
            'detail',
            'app_id',
            'number',
            'type',
            'good_id',
            'little_img',
            'title',
            'normal_price',
//            'custom',
            'fare_price',
            'real_price',
            'coin',
            'status',
//            'pay_type',
            'push_time',
            'express_number',
//            'deleted_at',
//            'updated_at',
            'created_at',
        ]);
    }


}
