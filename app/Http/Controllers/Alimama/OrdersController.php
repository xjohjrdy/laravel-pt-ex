<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\TaobaoEnterOrder;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\App\UserOrderNew;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use ETaobao\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    private $vip_percent = 0.645;
    private $common_percent = 0.42;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, UserOrderNew $userOrderNew, TaobaoEnterOrder $taobaoEnterOrder, TaobaoMaidOld $taobaoMaidOld)
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

            $status = [0];

            if ($arrRequest['status'] == 0) {
                $status = [0];
            }

            if ($arrRequest['status'] == 1) {
                $status = [3, 4, 9];
            }

            if ($arrRequest['status'] == 2) {
                $status = [2];
            }

            if ($arrRequest['status'] == 4) {
                $status = [1];
            }

            $orders = $userOrderNew->getUserOrders($arrRequest['app_id'], $status);

            $orders = $orders->toArray();
            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $maid_old = $taobaoMaidOld->where(
                    [
                        'trade_id' => (string)$order['order_number'],
                        'app_id' => $arrRequest['app_id']
                    ]
                )->value('maid_money');
                $enter_order = $taobaoEnterOrder->getOneOrders($order['order_number']);
                if (empty($enter_order)) {
                    $arr_orders[$k]['taobao_id'] = $order['order_number'];
                    $arr_orders[$k]['status'] = $arrRequest['status'] == 0 ? 3 : $arrRequest['status'];
                    $arr_orders[$k]['title'] = '商品名字同步中...';
                    $arr_orders[$k]['money'] = '同步中..';
                    $arr_orders[$k]['img'] = '';
                    $arr_orders[$k]['created_at'] = '同步中..';
                    $arr_orders[$k]['tkmoney_general'] = empty($maid_old) ? 0 : $maid_old;
                    $arr_orders[$k]['tkmoney_vip'] = empty($maid_old) ? 0 : $maid_old;
                    $arr_orders[$k]['button'] = '淘宝升级中,本平台订单24小时内同步完成,请放心哦!';
                } else {
                    $arr_orders[$k]['taobao_id'] = $order['order_number'];
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
                    $arr_orders[$k]['money'] = round($enter_order->alipay_total_price, 2);
                    $arr_orders[$k]['created_at'] = $enter_order->created_at->toDateTimeString();

                    $arr_orders[$k]['tkmoney_general'] = empty($maid_old) ? round(($enter_order->pub_share_pre_fee) * $this->common_percent, 2) : $maid_old;
                    $arr_orders[$k]['tkmoney_vip'] = empty($maid_old) ? round(($enter_order->pub_share_pre_fee) * $this->vip_percent, 2) : $maid_old;
                }
            }
            return $this->getResponse([
                'data' => $arr_orders,
                'lastPage' => $orders['last_page'],
                'currentPage' => $orders['current_page'],
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request, TaobaoEnterOrder $taobaoEnterOrder, TaobaoMaidOld $taobaoMaidOld, AppUserInfo $appUserInfo)
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

            $orders = $taobaoMaidOld->getTaobaoMaidAll($arrRequest['app_id'], $status);

            $orders = $orders->toArray();
            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $taobaoEnterOrder->getOneOrders($order['trade_id']);
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
                    $arr_orders[$k]['button'] = '淘宝升级中,本平台订单24小时内同步完成,请放心哦!';

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
                    $arr_orders[$k]['money'] = round($enter_order->alipay_total_price, 2);
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
