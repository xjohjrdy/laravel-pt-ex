<?php


namespace App\Http\Controllers\Wechat\Assistant;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\WxAssistantOrder;
use App\Entitys\App\WxAssistantPackage;
use App\Exceptions\ApiException;
use App\Extend\Random;
use App\Http\Controllers\Controller;
use App\Services\Common\CommonFunction;
use App\Services\Common\DingAlerts;
use App\Services\Growth\UserIncome;
use App\Services\HeMengTong\HeMeToServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    const LOG_DIR = 'hemeto/robot';

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
            $arrRequest['num'] = empty($arrRequest['num']) ? 1 : $arrRequest['num'];
            $rules = [
                'app_id' => 'required',
                'package_id' => 'required',
                'pay_type' => Rule::in([10, 11]), // 支付方式 1 支付宝 2 微信
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $package_id = $arrRequest['package_id'];
            $app_id = $arrRequest['app_id'];
            $num = 1;
            $pay_type = $arrRequest['pay_type'];
            $heMeToServices = new HeMeToServices();
            $orderModel = new WxAssistantOrder();
            $packageModel = new WxAssistantPackage();
            $adUserInfoModel = new AdUserInfo();
            $userInfo = $adUserInfoModel->appToAdUserId($app_id);
            $package = $packageModel->where(['id' => $package_id])->first();
            if(empty($package)){
                throw new ApiException('套餐无效', 1000);
            }
            if(empty($userInfo)){
                throw new ApiException('用户ID无效，请联系客服。', 1000);
            }
            $group_id = $userInfo['groupid'];
            $aver_price = $package['common_price']; // 单价
            if($group_id >= 23){ // vip
                $aver_price = $package['vip_price']; // 单价
            }
            $real_price = round($num * $aver_price, 2);
            $key = 'AS_ROBOT_' . $app_id . $package_id;
            if (Cache::has($key)) {
                return $this->getInfoResponse('1001', '操作频繁');
            } else {
                Cache::put($key, 1, 0.1);
            }
            $order_id = 'RB' . date('YmdHis') . Random::numeric(5);
            $data = [
                'app_id' => $app_id,
                'package_id' => $package_id,
                'order_id' => $order_id,
                'num' => $num,
                'month' => $package['get_month'],
                'real_price' => $real_price,
                'order_status' => WxAssistantOrder::NO_PAY,
                'pay_type' => $pay_type
            ];
            if ($pay_type == 10) {
                $unid = uniqid('arb', true);
                $res = $heMeToServices->appPayRobot($unid, $real_price, $real_price, $order_id);
                $res = json_decode($res, true);
                if (@$res["fcode"] != 10000) {
                    if (!Cache::has('dding_rb_pay' . @$res["fcode"])) {
                        $dingAlerts = new DingAlerts();
                        $dingAlerts->sendByText('微信机器人生成支付数据失败:' . @$res['fmsg']);
                        Cache::put('dding_rb_pay' . @$res["fcode"], 1, 20);
                    }
                    return $this->getInfoResponse('1001', @$res['fmsg']);//错误返回数据
                }
            }

            if ($pay_type == 11) {
                $unid = uniqid('wrb', true);
                $res = $heMeToServices->appWxPayRobot($unid, $real_price, $order_id, $app_id);
            }
            $data['pay_no'] = $unid;
            $orderModel->create($data);
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
            $WxAssistantOrderModel = new WxAssistantOrder();
            $app_id = $arrRequest['app_id'];
            $order_id = $arrRequest['order_id'];
            $wxPackageModel = new WxAssistantPackage();
            $item = $WxAssistantOrderModel->leftJoin($wxPackageModel->getTable(), $WxAssistantOrderModel->getTable() . '.package_id', '=' , $wxPackageModel->getTable() . '.id')->where(['app_id' => $app_id, 'order_id' => $order_id])->first();
            if(empty($item)){
                throw new ApiException('订单不存在！', 1000);
            }
            $time = strtotime($item['created_at']);
            if($item['order_status'] == WxAssistantOrder::NO_PAY){ // 待付款
                if((time() - $time) > 60*30){
                    $item['status'] = 0;
                    $item['order_status'] = '已失效';
                } else {

                    $item['status'] = 1;
                    $item['order_status'] = '去支付';
                }
            } else if($item['order_status'] == WxAssistantOrder::PAY_WAIT){ // 已支付
                $item['status'] = 2;
                $item['order_status'] = '订单处理中';
            } else if($item['order_status'] == WxAssistantOrder::PAY_SUCCESS){ // 已支付
                $item['status'] = 3;
                $item['order_status'] = '支付成功';
            } else if($item['order_status'] == WxAssistantOrder::REFUND_SUCCESS){ //
                $item['status'] = 4;
                $item['order_status'] = '购买失败，退款成功';
            } else if($item['order_status'] == WxAssistantOrder::REFUND_FAIL){ // 已支付
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
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $WxAssistantOrderModel = new WxAssistantOrder();
            $wxPackageModel = new WxAssistantPackage();
            $app_id = $arrRequest['app_id'];
            $list = $WxAssistantOrderModel->leftJoin($wxPackageModel->getTable(), $WxAssistantOrderModel->getTable() . '.package_id', '=' , $wxPackageModel->getTable() . '.id')
                ->where(['app_id' => $app_id]);
//            $list = $list->whereIn('order_status', [WxAssistantOrder::PAY_WAIT, WxAssistantOrder::PAY_SUCCESS, WxAssistantOrder::REFUND_SUCCESS, WxAssistantOrder::REFUND_FAIL]);
            $list = $list->where('order_status', '>', WxAssistantOrder::NO_PAY)->orderByDesc($WxAssistantOrderModel->getTable() . '.id')->paginate(10, [
                'real_price','pay_type','month','order_status','order_id',$WxAssistantOrderModel->getTable() . '.created_at','title']);
            $list = $list->toArray();
            foreach ($list['data'] as $key=>$item){
                $time = strtotime($item['created_at']);
                if($item['order_status'] == WxAssistantOrder::NO_PAY){ // 待付款
                    if((time() - $time) > 60*30){
                        $list['data'][$key]['status'] = 0;
                        $list['data'][$key]['order_status'] = '已失效';
                    } else {
                        $list['data'][$key]['status'] = 1;
                        $list['data'][$key]['order_status'] = '去支付';
                    }
                } else if($item['order_status'] == WxAssistantOrder::PAY_WAIT){ // 已支付
                    $list['data'][$key]['status'] = 2;
                    $list['data'][$key]['order_status'] = '订单处理中';
                } else if($item['order_status'] == WxAssistantOrder::PAY_SUCCESS){ // 已支付
                    $list['data'][$key]['status'] = 3;
                    $list['data'][$key]['order_status'] = '支付成功';
                } else if($item['order_status'] == WxAssistantOrder::REFUND_SUCCESS){ //
                    $list['data'][$key]['status'] = 4;
                    $list['data'][$key]['order_status'] = '购买失败，退款成功';
                } else if($item['order_status'] == WxAssistantOrder::REFUND_FAIL){ // 已支付
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



}