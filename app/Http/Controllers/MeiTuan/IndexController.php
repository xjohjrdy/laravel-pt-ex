<?php

namespace App\Http\Controllers\MeiTuan;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\MtEnterOrder;
use App\Entitys\App\MtMaidOld;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\MeiTuan\MeiTuanServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    /*
     * 得到有二级分佣美团投放链接
     */
    public function getYesRebateUrl(Request $request, MeiTuanServices $meiTuanServices)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',  //必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];

            /***********************************/
            //开始处理逻辑问题
            $obj_user = new AppUserInfo();
            $obj_user_info = $obj_user->getUserData($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '查询不到该用户！');
            }

            $url = $meiTuanServices->getYesRebateUrl($app_id);
            return $this->getResponse($url);//正常返回数据

            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到美团自己报销订单
     */
    public function getMtMaidOrder(Request $request, MtEnterOrder $mtEnterOrder, MtMaidOld $mtMaidOld, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = 0;

            if ($arrRequest['status'] == 0) {
                $status = 0;
            }

            if ($arrRequest['status'] == 1) {
                $status = 1;
            }

            if ($arrRequest['status'] == 2) {
                $status = 2;
            }

            $orders = $mtMaidOld->getMtMaidAll($arrRequest['app_id'], $status);
            $orders = $orders->toArray();

            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $mtEnterOrder->getOneOrders($order['trade_id']);
                $app_user = $appUserInfo->getUserById($order['father_id']);
                if (empty($enter_order)) {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = '商品名字同步中...';
                    $arr_orders[$k]['money'] = '同步中..';
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                } else {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = $enter_order->shop_name;
                    $arr_orders[$k]['money'] = (string)round($enter_order->actual_item_amount, 2);
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                }
            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到美团直属下级报销订单
     */
    public function getDirectlyMtMaidOrder(Request $request, MtEnterOrder $mtEnterOrder, MtMaidOld $mtMaidOld, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = 0;

            if ($arrRequest['status'] == 0) {
                $status = 0;
            }

            if ($arrRequest['status'] == 1) {
                $status = 1;
            }

            if ($arrRequest['status'] == 2) {
                $status = 2;
            }

            //得到所有下级id
            $adUserModel = new AdUserInfo();
            $obj_id = $adUserModel->where(['pt_pid' => $arrRequest['app_id']])->pluck('pt_id');
            $arr_id = $obj_id->toArray();
            if (empty($arr_id)){
                return $this->getInfoResponse('1001', '你还没有粉丝哦~，暂时没有订单');//错误返回数据
            }

            $orders = $mtMaidOld->getDirectlyMtMaidAll($arr_id, $status);
            $orders = $orders->toArray();

            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $mtEnterOrder->getOneOrders($order['trade_id']);
                $app_user = $appUserInfo->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                if (empty($enter_order)) {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = '商品名字同步中...';
                    $arr_orders[$k]['money'] = '同步中..';
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                } else {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = $enter_order->shop_name;
                    $arr_orders[$k]['money'] = (string)round($enter_order->actual_item_amount, 2);
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                }
            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
