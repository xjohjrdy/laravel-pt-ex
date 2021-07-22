<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\Other\UserThreeUpMaid;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\ShopOrdersOut;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MemberTeamCnyController extends Controller
{
    /**
     * 展示单个用户商城的分佣信息
     * get {"app_id":"1"}
     * @param Request $request
     * @param ShopOrdersMaid $shopOrdersMaid
     * @param ShopOrders $shopOrders
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, UserThreeUpMaid $userThreeUpMaid, ShopOrdersOut $shopOrdersOut, AdUserInfoOut $adUserInfoOut)
    {
        //
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $credit_log = $userThreeUpMaid->getAllCreditLog($arrRequest['app_id'], 1);

            $user_info = $adUserInfoOut->getUidById($arrRequest['app_id']);

            foreach ($credit_log as $k => $v) {
                $credit_log[$k]->test_money = $credit_log[$k]->money;  // 转换成余额
                $credit_log[$k]->test_wuhang = 1;  // 转换成余额
                $credit_log[$k]->money = (((int)$credit_log[$k]->money) / 10);  // 转换成余额
                if (empty($v->order_id)) {
                    continue;
                }
                $order = $shopOrdersOut->getByOrderId($v->order_id);
                if (!$order) {
                    continue;
                }
                $credit_log[$k]->order_id = $order->order_id;
                $new_app_id = $order->app_id;
                if ($order->app_id >= 10000000) {
                    $new_app_id = base_convert($order->app_id, 10, 33); // 10 转 33
                    $new_app_id = 'x' . $new_app_id;
                }
                $credit_log[$k]->from = $new_app_id;
                $credit_log[$k]->three = $adUserInfoOut->checkUserThreeFloor($user_info, $order->app_id);
            }

            return $this->getResponse(['account_title' => '商城预估佣金', 'left' => '已到账商城佣金', 'log' => $credit_log]);

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}