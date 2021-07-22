<?php

namespace App\Http\Controllers\AppMine;

use App\Entitys\App\TaobaoEnterOrder;
use App\Entitys\App\UserOrderTao;
use App\Exceptions\ApiException;
use App\Services\Common\TaobaokeService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MyOrderController extends Controller
{
    /*
     * 得到待审核、审核通过、审核失败订单
     */
    public function getMyOrder(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
                'type' => Rule::in([1, 2, 3]),
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type'];

            #*****************************************#
            $model_order = new UserOrderTao();
            switch ($type) {
                case 1:
                    $obj_orders = $model_order->getToReviewList($app_id);
                    break;
                case 2:
                    $obj_orders = $model_order->getReviewedList($app_id);
                    break;
                case 3:
                    $obj_orders = $model_order->getReviewFailureList($app_id);
                    break;
            }
            $order_ids = [];
            foreach ($obj_orders->items() as $item) {
                $order_ids[] = $item->order_number;
            }
            $taobao_orders = [];
            if (!empty($order_ids)) {
                $model_taobao_order = new TaobaoEnterOrder();
                $taobao_orders = $model_taobao_order->getOrderInfo($order_ids);
            }
            if (!empty($taobao_orders)) {
                $num_iids = [];
                foreach ($taobao_orders as $order) {
                    $num_iids[] = $order->num_iid;
                }
                $str_iids = implode(",", $num_iids);
                $ser_tbk = new TaobaokeService();
                $arr_imgs = $ser_tbk->getGoodsPictUrlByIds($str_iids);
            }
            foreach ($obj_orders->items() as $item) {
                $v_title = '旧数据无商品标题';
                $v_img_url = 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/goods_default.png?x-oss-process=style/yasuo-123';
                $v_pay_price = 0;
                $total_commission_fee = 0;

                if (isset($taobao_orders[$item->order_number])) {
                    $taobao_order_info = $taobao_orders[$item->order_number];
                    $v_title = $taobao_order_info->item_title;
                    $v_pay_price = $taobao_order_info->pay_price;

                    if (isset($arr_imgs[$taobao_order_info->num_iid])) {
                        $v_img_url = $arr_imgs[$taobao_order_info->num_iid];
                    }
                }

                $item->title = $v_title;
                $item->pay_price = $v_pay_price;
                $item->img_url = $v_img_url;
            }

            #*****************************************#
            return $this->getResponse($obj_orders);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }
}
