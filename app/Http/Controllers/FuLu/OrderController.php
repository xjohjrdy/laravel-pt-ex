<?php


namespace App\Http\Controllers\FuLu;


use App\Entitys\App\FuluGoodsInfo;
use App\Entitys\App\FuluGoodsType;
use App\Entitys\App\FuluOrder;
use App\Exceptions\ApiException;
use App\Extend\Random;
use App\Http\Controllers\Controller;
use App\Services\Common\CommonFunction;
use App\Services\Common\DingAlerts;
use App\Services\FuLu\FuLuServices;
use App\Services\Growth\UserIncome;
use App\Services\HeMengTong\HeMeToServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    const FU_LU_ORDER_PAY_KEY = 'Fulu_';
    const FU_LU_ORDER_CREATE_KEY = 'HMT_FU_LU_ORDER_CREATE';
    const LOG_DIR = 'hemeto/fulu';
    private $columns = ['app_id', 'product_id', 'order_id', 'buy_num', 'real_price', 'use_money', 'charge_account', 'order_status',
        'pay_type', 'product_type', 'pay_time', 'product_name', 'created_at'];

    /**
     * 生成预支付订单信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function generatePrePayInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'product_id' => 'required',
                'num' => 'required|integer' // 购买数量
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $num = $arrRequest['num'];
            $product_id = $arrRequest['product_id'];
            $app_id = $arrRequest['app_id'];
            $product = $this->validateProduct($product_id);
            $aver_price = $product['purchase_price']; // 单价
            $real_price = round($num * $aver_price, 2);

            $product_name = $product['product_name'];
            $fuluGoodsModel = new FuluGoodsInfo();
            $fuluTypeModel = new FuluGoodsType();
            $productInfo = $fuluGoodsModel->leftJoin($fuluTypeModel->getTable(), $fuluGoodsModel->getTable() . '.two_type', '=', $fuluTypeModel->getTable() . '.type_id')
                ->where(['product_id' => $product_id])->first([$fuluTypeModel->getTable() . '.*', $fuluGoodsModel->getTable() . '.*']);
            $product_type = $product['product_type'];
            $account = empty($arrRequest['account']) ? '' : $arrRequest['account'];
            $data = [
                'app_id' => $app_id,
                'product_id' => $product_id,
                'buy_num' => $num,
                'real_price' => $real_price,
                'product_type' => $product_type,
                'product_name' => $product_name,
                'account' => $account,
                'purchase_price' => $aver_price,
                'img' => $productInfo['img'],
                'details' => $productInfo['details']
            ];
            return $this->getResponse($data);

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /**
     * 校验商品是否有效
     * @param $product_id
     * @return array|mixed
     * @throws ApiException
     */
    private function validateProduct($product_id)
    {
        $fuluService = new FuLuServices();
        $res = $fuluService->getGoodsInfo($product_id);
        $res = json_decode($res, true);
        $product = [];
        if ($res['code'] == 0) {
            $product = json_decode($res['result'], true);
            if ($product['sales_status'] == '下架') { // 销售状态：下架、上架、维护中、库存维护
                throw new ApiException('商品已下架！', '1000');
            }
            return $product;
        } else {
            throw new ApiException($res['message'], '1000');
        }
    }

    /**
     * 生成支付订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function generatePayOrder(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'product_id' => 'required',
                'num' => 'required|integer', // 购买数量
                'pay_type' => Rule::in([10, 11]), // 支付方式 1 支付宝 2 微信
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $num = $arrRequest['num'];
            $product_id = $arrRequest['product_id'];
            $app_id = $arrRequest['app_id'];
            $account = empty($arrRequest['account']) ? null : $arrRequest['account'];
            $pay_type = $arrRequest['pay_type'];
            $heMeToServices = new HeMeToServices();
            $fuluOrderModel = new FuluOrder();
            $product = $this->validateProduct($product_id);
            $aver_price = $product['purchase_price']; // 单价
            $real_price = round($num * $aver_price, 2);
            $product_type = $product['product_type'];
            $product_name = $product['product_name'];
            $key = 'FU_LU_' . $app_id . $product_id;
            if (Cache::has($key)) {
                return $this->getInfoResponse('1001', '操作频繁');
            } else {
                Cache::put($key, 1, 0.1);
            }
            $order_id = '8F' . date('YmdHis') . Random::numeric(5);
            $data = [
                'app_id' => $app_id,
                'product_id' => $product_id,
                'order_id' => $order_id,
                'buy_num' => $num,
                'real_price' => $real_price,
                'charge_account' => $account,
                'order_status' => FuluOrder::NO_PAY,
                'product_type' => $product_type,
                'pay_type' => $pay_type,
                'product_name' => $product_name
            ];
            if ($pay_type == 10) {
                $unid = uniqid('afl', true);
                $res = $heMeToServices->appPayFulu($unid, $real_price, $real_price, $order_id);
                $res = json_decode($res, true);
                if (@$res["fcode"] != 10000) {
                    if (!Cache::has('dding' . @$res["fcode"])) {
                        $dingAlerts = new DingAlerts();
                        $dingAlerts->sendByText('福禄订单生成支付数据失败:' . @$res['fmsg']);
                        Cache::put('dding' . @$res["fcode"], 1, 20);
                    }
                    return $this->getInfoResponse('1001', @$res['fmsg']);//错误返回数据
                }
            }

            if ($pay_type == 11) {
                $unid = uniqid('wfl', true);
                $res = $heMeToServices->appWxPayFulu($unid, $real_price, $order_id, $app_id);
            }
            $data['pay_no'] = $unid;
            $fuluOrderModel->create($data);
            return $this->getResponse($res);

        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 查看单笔订单详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function showOrderDetail(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'order_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $fuluOrderModel = new FuluOrder();
            $heMeToServices = new HeMeToServices();
            $app_id = $arrRequest['app_id'];
            $order_id = $arrRequest['order_id'];
            $pay_type = $arrRequest['pay_type'];

            $item = $fuluOrderModel->where(['app_id' => $app_id, 'order_id' => $order_id])->first($this->columns);
            if (empty($item)) {
                throw new ApiException('订单不存在！', 1000);
            }
            $fuluGoodsModel = new FuluGoodsInfo();
            $fuluTypeModel = new FuluGoodsType();
            $productInfo = $fuluGoodsModel->leftJoin($fuluTypeModel->getTable(), $fuluGoodsModel->getTable() . '.two_type', '=', $fuluTypeModel->getTable() . '.type_id')
                ->where(['product_id' => $item['product_id']])->first([$fuluTypeModel->getTable() . '.*', $fuluGoodsModel->getTable() . '.*']);
            $time = strtotime($item['created_at']);
            $item['details'] = $productInfo['details'];
            $item['img'] = $productInfo['img'];
            if ($item['order_status'] == FuluOrder::NO_PAY) { // 待付款
                if ((time() - $time) > 60 * 30) {
                    $item['status'] = 0;
                    $item['order_status'] = '已失效';
                } else {
                    $item['status'] = 1;
                    $item['order_status'] = '去支付';
                }
            } else if ($item['order_status'] == FuluOrder::PAY_WAIT) { // 已支付
                $item['status'] = 2;
                $item['order_status'] = '订单处理中';
            } else if ($item['order_status'] == FuluOrder::PAY_SUCCESS) { // 已支付
                $item['status'] = 3;
                $item['order_status'] = '支付成功';
            } else if ($item['order_status'] == FuluOrder::REFUND_SUCCESS) { //
                $item['status'] = 4;
                $item['order_status'] = '充值失败，退款成功';
            } else if ($item['order_status'] == FuluOrder::REFUND_FAIL) { // 已支付
                $item['status'] = 5;
                $item['order_status'] = '退款失败，请联系客服';
            }
            if (empty($item)) {
                return $this->getInfoResponse(1000, '订单状态异常！');
            }
            return $this->getResponse($item);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 用户点击去支付接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function toPay(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'order_id' => 'required',
                'pay_type' => Rule::in([10, 11]), // 支付方式 1 支付宝 2 微信
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $fuluOrderModel = new FuluOrder();
            $heMeToServices = new HeMeToServices();
            $app_id = $arrRequest['app_id'];
            $order_id = $arrRequest['order_id'];
            $pay_type = $arrRequest['pay_type'];
            $key = 'hmt_fulu_order_pay_success' . $order_id;
            if (Cache::has($key)) {
                return $this->getInfoResponse(1000, '订单已支付，请勿重复支付！');
            } else {
                $order = $fuluOrderModel->where(['app_id' => $app_id, 'order_id' => $order_id, 'order_status' => FuluOrder::NO_PAY])->first();
                if (empty($order)) {
                    return $this->getInfoResponse(1000, '订单已支付，请勿重复支付！');
                }
                $real_price = $order['real_price'];
                $this->validateProduct($order['product_id']);
                $key = 'FU_LU_' . $order_id;
                if (Cache::has($key)) {
                    return $this->getInfoResponse('1001', '操作频繁');
                } else {
                    Cache::put($key, 1, 0.1);
                }
                if ($pay_type == 10) {
                    $unid = uniqid('afl', true);
                    $res = $heMeToServices->appPayFulu($unid, $real_price, $real_price, $order_id);
                    $res = json_decode($res, true);
                    if (@$res["fcode"] != 10000) {
                        if (!Cache::has('dding' . @$res["fcode"])) {
                            $dingAlerts = new DingAlerts();
                            $dingAlerts->sendByText('福禄订单生成支付数据失败:' . @$res['fmsg']);
                            Cache::put('dding' . @$res["fcode"], 1, 20);
                        }
                        return $this->getInfoResponse('1001', @$res['fmsg']);//错误返回数据
                    }
                }
                if ($pay_type == 11) {
                    $unid = uniqid('wfl', true);
                    $res = $heMeToServices->appWxPayFulu($unid, $real_price, $order_id, $app_id);
                }
                $fuluOrderModel->where(['app_id' => $app_id, 'order_id' => $order_id, 'order_status' => FuluOrder::NO_PAY])->update([
                    'pay_no' => $unid,
                    'pay_type' => $pay_type
                ]);
                return $this->getResponse($res);
            }
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }


    /**
     * 获取卡密信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function orderCardInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'order_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $fuluService = new FuLuServices();
            $order_id = $arrRequest['order_id'];
            $app_id = $arrRequest['app_id'];
            $fuluOrderModel = new FuluOrder();
            $item = $fuluOrderModel->where(['order_id' => $order_id, 'app_id' => $app_id, 'order_status' => FuluOrder::PAY_SUCCESS])->first($this->columns);
            if (empty($item)) {
                throw new ApiException('订单不存在！', 1000);
            }

            $fuluGoodsModel = new FuluGoodsInfo();
            $fuluTypeModel = new FuluGoodsType();
            $productInfo = $fuluGoodsModel->leftJoin($fuluTypeModel->getTable(), $fuluGoodsModel->getTable() . '.two_type', '=', $fuluTypeModel->getTable() . '.type_id')
                ->where(['product_id' => $item['product_id']])->first([$fuluTypeModel->getTable() . '.*', $fuluGoodsModel->getTable() . '.*']);
            $res = $fuluService->getOrderInfo($order_id);
            $res = json_decode($res, true);
            if ($res['code'] != 0) {
                return $this->getInfoResponse('1001', @$res['message']);//错误返回数据
            }
            $res = json_decode($res['result'], true);
            if(!empty($res['cards'])){
                $cards = $res['cards'];
                foreach ($cards as $key=>$item){
                    $res['cards'][$key]['card_pwd'] = $fuluService->decode($item['card_pwd']);
                    $res['cards'][$key]['card_number'] = $fuluService->decode($item['card_number']);
                }
            }
            $res['img'] = $productInfo['img'];
            $res['face_value'] = $productInfo['face_value'];
            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }



    /**
     * 获取订单列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function orderList(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'type' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $fuluGoodsModel = new FuluGoodsInfo();
            $fuluTypeModel = new FuluGoodsType();
            $fuluOrderModel = new FuluOrder();
            $app_id = $arrRequest['app_id'];
            $list = $fuluOrderModel
                ->leftJoin($fuluGoodsModel->getTable(), $fuluOrderModel->getTable() . '.product_id', '=', $fuluGoodsModel->getTable() . '.product_id')
                ->leftJoin($fuluTypeModel->getTable(), $fuluGoodsModel->getTable() . '.two_type', '=', $fuluTypeModel->getTable() . '.type_id')
                ->where(['app_id' => $app_id]);
            $type = $arrRequest['type'];
            if ($type == 0) { //全部

            }
            if ($type == 1) { // 支付成功
                $list = $list->whereIn('order_status', [FuluOrder::PAY_WAIT, FuluOrder::PAY_SUCCESS]);
            }
            if ($type == 2) { // 退款
                $list = $list->whereIn('order_status', [FuluOrder::REFUND_SUCCESS, FuluOrder::REFUND_FAIL]);
            }
            if ($type == 3) { // 已失效
                $list = $list->whereIn('order_status', [FuluOrder::NO_PAY]);
            }
            $list = $list->orderByDesc($fuluOrderModel->getTable() . '.id')->paginate(10, [$fuluOrderModel->getTable() . '.*',
                'details', 'two_type', 'two_type', 'one_type', 'three_type', 'stock_status', 'sales_status', 'img']);
            $list = $list->toArray();
            foreach ($list['data'] as $key => $item) {
                $time = strtotime($item['created_at']);
                if ($item['order_status'] == FuluOrder::NO_PAY) { // 待付款
                    if ((time() - $time) > 60 * 30) {
                        $list['data'][$key]['status'] = 0;
                        $list['data'][$key]['order_status'] = '已失效';
                    } else {
                        $list['data'][$key]['status'] = 1;
                        $list['data'][$key]['order_status'] = '去支付';
                    }
                } else if ($item['order_status'] == FuluOrder::PAY_WAIT) { // 已支付
                    $list['data'][$key]['status'] = 2;
                    $list['data'][$key]['order_status'] = '订单处理中';
                } else if ($item['order_status'] == FuluOrder::PAY_SUCCESS) { // 已支付
                    $list['data'][$key]['status'] = 3;
                    $list['data'][$key]['order_status'] = '支付成功';
                } else if ($item['order_status'] == FuluOrder::REFUND_SUCCESS) { //
                    $list['data'][$key]['status'] = 4;
                    $list['data'][$key]['order_status'] = '退款成功';
                } else if ($item['order_status'] == FuluOrder::REFUND_FAIL) { // 已支付
                    $list['data'][$key]['status'] = 5;
                    $list['data'][$key]['order_status'] = '退款失败，请联系客服';
                }
            }
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    public function enterOrderCallback(Request $request)
    {

        $data = $request->getContent();
        $data = json_decode($data, true);
//        $data = array (
//            'order_id' => '20050821857419031423',
//            'charge_finish_time' => '2020-05-08 14:23:15',
//            'customer_order_no' => '8F2020050814220414970',
//            'order_status' => 'success',
//            'recharge_description' => '充值成功',
//            'product_id' => '10018435',
//            'price' => '0.0100', // 单价
//            'buy_num' => '1',
//            'operator_serial_number' => '--',
//            'sign' => '22b39bfcb296fb1b093bf5e4b92fc38c',
//            's' => '//callback/fulu_order_call_back',
//        );
        $fuluService = new FuLuServices();
        $data2 = $data;
        unset($data2['sign'], $data2['s']);
        $msg = '';
        $fuluOrderModel = new FuluOrder();
        $hmtSerice = new HeMeToServices();
        CommonFunction::callbackLog(json_encode($data, 320), static::LOG_DIR, '_');
        if ($fuluService->getSign($data2) == $data['sign']) {
            $order_id = $data2['customer_order_no'];
            $price = $data2['price'];
            $product_id = $data2['product_id'];
            try {
                $msg = '验证通过';
                $order = $fuluOrderModel->where(['order_id' => $order_id, 'product_id' => $product_id, 'order_status' => FuluOrder::PAY_WAIT])->first();
                if ($data2['order_status'] == 'success') {
                    $msg = $msg . '=> 下单成功！';

                    if (empty($order)) {
                        $msg = $msg . '=> 订单不存在！';
                    } else {
                        $price2 = round($order['real_price'] / $order['buy_num'], 2);
                        if ($price2 == round($price, 2)) {
                            $msg = $msg . '=> 价格一致！' . $price2 . '：' . round($price, 2);
                        } else {
                            $msg = $msg . '=> 价格不等！' . $price2 . '：' . round($price, 2);
                        }
                        // 下单成功后无法退款，即使不等继续处理，只做记录
                        $fuluOrderModel->where(['order_id' => $order_id, 'product_id' => $product_id, 'order_status' => FuluOrder::PAY_WAIT])->update([
                            'order_status' => FuluOrder::PAY_SUCCESS
                        ]);
                        $msg = $msg . ' ==> 更新状态' . FuluOrder::PAY_SUCCESS;
                    }
                } else {
                    // 执行退款
                    $msg = $msg . '=> 下单失败！执行退款';
                    $order_status = FuluOrder::PAY_FAIL;
                    $refund_res = $hmtSerice->orderRefund($order['pay_no'], $order['real_price']);
                    $refund_res = json_decode($refund_res, true);
//                                    $this->log($refund_res, $dir);
//                                    $this->log($order['pay_no']. '===' . $order['real_price'], $dir);
                    if (@$refund_res["fcode"] != 10000) {
                        $order_status = FuluOrder::REFUND_FAIL;
                        $msg = $msg . ' ==> ' . '退款失败【' . @$refund_res['fmsg'] . '】';
                    } else {
                        $order_status = FuluOrder::REFUND_SUCCESS;
                        $msg = $msg . ' ==> ' . '退款成功';
                    }
                    $fuluOrderModel->where(['order_id' => $order_id])->update([
                        'order_status' => $order_status
                    ]);
                    $msg = $msg . ' ==> 更新状态' . $order_status;
                }
            } catch (\Throwable $exception) {
                $msg = $msg . '=> 异常:' . $exception->getMessage();
            }
        } else {
            $msg = '验签失败';
        }
        CommonFunction::callbackLog($msg . ' end', static::LOG_DIR, '_');
        return 'success';
    }

}