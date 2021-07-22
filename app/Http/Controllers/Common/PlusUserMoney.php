<?php


namespace App\Http\Controllers\Common;


use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\TaobaoUser;
use App\Entitys\Other\UserThreeUpMaid;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\Common\UserMoney;
use App\Services\Qmshida\OtherUserMoneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlusUserMoney extends Controller
{
    public function plusMoney(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'sign' => 'required',
                'app_id' => 'required',
                'money' => 'required',
                'type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                return $this->getResponse('sajkdh');
            }
            if($arrRequest['sign'] != 'pt1118'){
                return $this->getResponse('sajkdh');
            }
            $pay_money = $arrRequest['money'];
            $type = $arrRequest['type'];
            $from_info = empty($arrRequest['from_info']) ? '' : $arrRequest['from_info'];
            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($arrRequest['app_id'], $pay_money, $type, $from_info);
            return $this->getResponse($arrRequest);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    public function minusMoney(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'sign' => 'required',
                'app_id' => 'required',
                'money' => 'required',
                'type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                return $this->getResponse('sajkdh');
            }
            if($arrRequest['sign'] != 'pt1118'){
                return $this->getResponse('sajkdh');
            }
            $from_info = empty($arrRequest['from_info']) ? '' : $arrRequest['from_info'];
            $pay_money = $arrRequest['money'];
            $type = $arrRequest['type'];
            $userMoneyService = new UserMoney();
            $userMoneyService->minusCnyAndLog($arrRequest['app_id'], $pay_money, $type, $from_info);
            return $this->getResponse($arrRequest);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    public function minusMoneyList(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'sign' => 'required',
                'type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                return $this->getResponse('sajkdh');
            }
            if($arrRequest['sign'] != 'pt1118'){
                return $this->getResponse('sajkdh');
            }
            $list = [

            ];
            foreach ($list as $item){
                $app_id = $item[0];
                $from_info = empty($arrRequest['from_info']) ? '' : $arrRequest['from_info'];
                $pay_money = $item[1];
                $userMoneyService = new UserMoney();
                try{
                    $userMoneyService->minusCnyAndLog($app_id, $pay_money, '29998', $from_info);
                } catch (\Exception $e){
                    continue;
                }
            }

            return $this->getResponse($arrRequest);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 用户确认收货后退款扣除佣金
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function minusMoneyByRefundOrder(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'sign' => 'required',
                'type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                return $this->getResponse('sajkdh');
            }
            if($arrRequest['sign'] != 'pt1118'){
                return $this->getResponse('sajkdh');
            }
            $list = $request->input()['list'];
            /**
             * ID：6114118 订单编号: WXAPPLET20200324094425Jpr1k,金额：79.80元。确认收货后退款，帮扣除3月活跃值奖励 3.99分活跃值
             * ID：7055426 订单编号：WXAPPLET20200310191440yJ0t7 君狮人参颗粒冲剂无糖型4g*40袋/盒。
             * ID：2878464 订单编号：WXAPPLET20200405173920pKhzq 金额：65.80元，扣除该笔订单奖励
             */
            $shopMaidModel = new ShopOrdersMaid();
            $threeMaidModel = new UserThreeUpMaid();
            $userMoneyService = new UserMoney();
            $otherMoneyService = new OtherUserMoneyService();
            foreach ($list as $item){
                $order_id = $item;
                if($arrRequest['type'] == 1){ //扣除app36
                    $order_info = $shopMaidModel->where(['order_id' => $order_id])->get();
                    foreach ($order_info as $order){

                        $taobao_user = new TaobaoUser();//用户真实分佣表
                        $taobao_user->getUserMoney($order['app_id']);
                        $userMoneyService->minusCnyAndLog($order['app_id'], $order['money'] / 10, '20005', $order['order_id']);
                    }
                }
                if($arrRequest['type'] == 2){ // 扣除管理费
                    $three_orders = $threeMaidModel->where(['order_id' => $order_id])->get();
                    foreach ($three_orders as $order){
                        $otherMoneyService->minusThreeUserMoney($order['app_id'], $order['money'], 0, 'REFUND');
                    }
                }

            }
            return $this->getResponse($list);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}