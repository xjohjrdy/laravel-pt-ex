<?php

namespace App\Http\Controllers\Voip;

use App\Entitys\Ad\VoipMoneyOrder;
use App\Entitys\Ad\VoipMoneyOrderMaid;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RechargeMaidController extends Controller
{
    /**
     * 获取分佣情况
     * @param Request $request
     * @param VoipMoneyOrderMaid $voipMoneyOrderMaid
     * @param VoipMoneyOrder $voipMoneyOrder
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, VoipMoneyOrderMaid $voipMoneyOrderMaid, VoipMoneyOrder $voipMoneyOrder)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }
            $res = $voipMoneyOrderMaid->getAllMaid($arrRequest['app_id']);
            foreach ($res as $k => $v) {
                $order = $voipMoneyOrder->getById($v->order_id);
                $res[$k]->from_id = $order->app_id;
                $res[$k]->to_phone = $order->phone;
                $res[$k]->updated_at = $v->updated_at;
                $res[$k]->string_info = 'ID：' . $order->app_id . '，' . $order->phone . '充值' . $order->price . '元葡萄通讯，奖励您' . ($v->money * 10) . '葡萄币。';
            }
            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
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
    public function show($id)
    {
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
