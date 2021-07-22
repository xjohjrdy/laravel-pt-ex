<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class ArticleOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_article_orders';
    public $timestamps = false;
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /*
     * 购买广告包生成订单信息
     */
    public function createOrder($app_id, $type, $orderid_tag)
    {
        $arrOrderParam = array(
            'order_id' => $orderid_tag,
            'app_id' => $app_id,
            'pay_type' => $type,
            'pay_status' => 0,
            'pay_price' => 10,
        );
        return $this->create($arrOrderParam);
    }

    public function getUnpaidByOrderId($order_id)
    {
        return $this->where(['order_id' => $order_id, 'pay_status' => 0])->first();
    }

    public function upOrder($out_order_no, $params)
    {
        return $this->where('order_id', $out_order_no)->update($params);
    }

}
