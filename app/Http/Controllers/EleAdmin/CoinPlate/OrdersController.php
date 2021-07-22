<?php

namespace App\Http\Controllers\EleAdmin\CoinPlate;

use App\Entitys\App\CoinShopOrders;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    //
    /**
     * 拉取订单列表
     */

    public function getList(Request $request, CoinShopOrders $coinShopOrders)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['app_id', 'status', 'type'];
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$item] = $params[$item];
                }
            }
            $list = $coinShopOrders->where($wheres)->orderByDesc('id')->paginate($limit);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /**
     * 发货
     */
    public function push(Request $request, CoinShopOrders $coinShopOrders)
    {
        try {
            $params = $request->input();
            $order_id = $params['order_id'];
            $express_number = $params['express_number'];


            $list = $coinShopOrders->where([
                'order_id' => $order_id
            ])->update([
                'push_time' => time(),
                'status' => 2,
                'express_number' => $express_number
            ]);
            
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /**
     * 新增或更新
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operate(Request $request, CoinShopOrders $coinShopOrders)
    {
        try {
            $params = $request->input();
            $rules = [
            ];
            unset($params['s']);
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            if(empty($params['id'])){
                $coinShopOrders->create($params);
            } else {
                $audit_info = $coinShopOrders->where(['id' => $params['id']])->first();
                if(empty($audit_info)){
                    return $this->getInfoResponse(2000, '为查找到该记录');
                } else {
                    $coinShopOrders->where(['id' => $params['id']])->update($params);
                }
            }

            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /**
     * 非正常订单手动原路退款
     */
    public function refund(Request $request, CoinShopOrders $coinShopOrders)
    {

        try {
            $params = $request->input();
            $order_id = $params['order_id'];


            $list = $coinShopOrders->where([
                'order_id' => $order_id
            ])->update([
                'close_time' => time(),
                'status' => 10,
            ]);

            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

}
