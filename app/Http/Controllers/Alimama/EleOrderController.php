<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\EleEnterOrder;
use App\Entitys\App\EleMaidOld;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use ETaobao\Factory;

class EleOrderController extends Controller
{
    /*
     * 得到饿了么自己报销订单
     */
    public function getEleMaidOrder(Request $request, EleEnterOrder $eleEnterOrder, EleMaidOld $eleMaidOld, AppUserInfo $appUserInfo)
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

            $orders = $eleMaidOld->getEleMaidAll($arrRequest['app_id'], $status);
            $orders = $orders->toArray();

            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $eleEnterOrder->getOneOrders($order['trade_id']);
                $app_user = $appUserInfo->getUserById($order['father_id']);
                if (empty($enter_order)) {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = '商品名字同步中...';
                    $arr_orders[$k]['money'] = '同步中..';
                    $arr_orders[$k]['img'] = '';
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                } else {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = $enter_order->item_title;
                    $config = config('taobao.config_taobao_one');
                    $app = Factory::Tbk($config);
                    $param = [
                        'num_iids' => $enter_order->num_iid,
                    ];
                    $res = $app->item->getInfo($param);
                    if (empty($res->results->n_tbk_item[0]->pict_url)) {
                        $img = '';
                    } else {
                        $img = $res->results->n_tbk_item[0]->pict_url;
                    }
                    $arr_orders[$k]['img'] = $img;
                    $arr_orders[$k]['money'] = (string)round($enter_order->alipay_total_price, 2);
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
     * 得到饿了么直属下级报销订单
     */
    public function getDirectlyEleMaidOrder(Request $request, EleEnterOrder $eleEnterOrder, EleMaidOld $eleMaidOld, AppUserInfo $appUserInfo)
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

            $orders = $eleMaidOld->getDirectlyEleMaidAll($arr_id, $status);
            $orders = $orders->toArray();

            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $eleEnterOrder->getOneOrders($order['trade_id']);
                $app_user = $appUserInfo->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                if (empty($enter_order)) {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = $app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = '商品名字同步中...';
                    $arr_orders[$k]['money'] = '同步中..';
                    $arr_orders[$k]['img'] = '';
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                } else {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = $app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = $enter_order->item_title;
                    $config = config('taobao.config_taobao_one');
                    $app = Factory::Tbk($config);
                    $param = [
                        'num_iids' => $enter_order->num_iid,
                    ];
                    $res = $app->item->getInfo($param);
                    if (empty($res->results->n_tbk_item[0]->pict_url)) {
                        $img = '';
                    } else {
                        $img = $res->results->n_tbk_item[0]->pict_url;
                    }
                    $arr_orders[$k]['img'] = $img;
                    $arr_orders[$k]['money'] = (string)round($enter_order->alipay_total_price, 2);
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
