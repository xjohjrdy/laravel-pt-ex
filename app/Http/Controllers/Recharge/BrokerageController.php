<?php

namespace App\Http\Controllers\Recharge;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\Ad\RechargeOrder;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BrokerageController extends Controller
{
    /**
     *
     * 分佣记录
     * {"uid":"1","start":"1514450925","end":"","limit":""}
     * @param Request $request
     * @param RechargeCreditLog $rechargeCreditLog
     * @param RechargeOrder $rechargeOrder
     * @param AdUserInfo $adUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, RechargeCreditLog $rechargeCreditLog, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('uid', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if (!array_key_exists('start', $arrRequest)) {
                $start = 0;
            } else {
                $start = $arrRequest['start'];
            }
            if (!array_key_exists('end', $arrRequest)) {
                $end = time();
            } else {
                $end = $arrRequest['end'];
            }
            if (!array_key_exists('limit', $arrRequest)) {
                $limit = 20;
            } else {
                $limit = $arrRequest['limit'];
            }

            $credits = $rechargeCreditLog->getCreditLogById($arrRequest['uid'], $start, $end, $limit);
            $sum = $rechargeCreditLog->getCreditLogByIdSum($arrRequest['uid']);
            $today_sum = $rechargeCreditLog->getCreditLogBySum($arrRequest['uid'], $start, $end);

//            foreach ($credits as $key => $credit) {
//                $order = $rechargeOrder->getOrdersById($credit->orderid);
//                if (empty($order)) {
//                    unset($credits[$key]);
//                    continue;
//                }
//                $user = $adUserInfo->getUserById($order->uid);
//                $credits[$key]->consume = $order->price;
//                $credits[$key]->three = $adUserInfo->checkUserThreeFloor($arrRequest['uid'], $order->uid);
//                $three_info = $adUserInfo->checkUserThreeFloorInfo($arrRequest['uid'], $order->uid);
//                if ($three_info == 0) {
//                    $credits[$key]->three_info = "团队会员";
//                }
//                if ($three_info == 1) {
//                    $credits[$key]->three_info = "直属会员";
//                }
//                if ($three_info == 2) {
//                    $credits[$key]->three_info = "团队会员";
//                }
//                if ($three_info == 3) {
//                    $credits[$key]->three_info = "团队会员";
//                }
//                $credits[$key]->pt_id = $user->pt_id;
//                $credits[$key]->username = $adUserInfo->getUserById($order->uid)->username;
//                if ($order->price == 10) {
//                    $credits[$key]->reason = '购买广告包';
//                } elseif ($order->price == 800) {
//                    $credits[$key]->reason = '购买超级用户';
//                } elseif ($order->price == 3000) {
//                    $credits[$key]->reason = '购买优质转正';
//                } elseif ($order->price == 2200) {
//                    $credits[$key]->reason = '补差价购买优质转正';
//                } elseif ($order->price == 2700) {
//                    $credits[$key]->reason = '补差价购买优质转正';
//                } elseif ($order->price == 300) {
//                    $credits[$key]->reason = '购买超级用户（早期）';
//                } else {
//                    $credits[$key]->reason = '消费金额';
//                }
//            }

            $arr_credits = [];
            foreach ($credits as $key => $credit) {
                $order = $rechargeOrder->getOrdersById($credit->orderid);
                if (empty($order)) {
                    continue;
                }
                $user = $adUserInfo->getUserById($order->uid);
                $three = $adUserInfo->checkUserThreeFloor($arrRequest['uid'], $order->uid);
                $three_info = $adUserInfo->checkUserThreeFloorInfo($arrRequest['uid'], $order->uid);
                if ($three_info == 0) {
                    $credits_three_info = "团队会员";
                }
                if ($three_info == 1) {
                    $credits_three_info = "直属会员";
                }
                if ($three_info == 2) {
                    $credits_three_info = "团队会员";
                }
                if ($three_info == 3) {
                    $credits_three_info = "团队会员";
                }
                $credits_username = $adUserInfo->getUserById($order->uid)->username;
                if ($order->price == 10) {
                    $credits_reason = '购买广告包';
                } elseif ($order->price == 800) {
                    $credits_reason = '购买超级用户';
                } elseif ($order->price == 3000) {
                    $credits_reason = '购买优质转正';
                } elseif ($order->price == 2200) {
                    $credits_reason = '补差价购买优质转正';
                } elseif ($order->price == 2700) {
                    $credits_reason = '补差价购买优质转正';
                } elseif ($order->price == 300) {
                    $credits_reason = '购买超级用户（早期）';
                } else {
                    $credits_reason = '消费金额';
                }

                $arr_credits[] =
                    [
                        'consume' => $order->price,
                        'dateline' => $credits[$key]->dateline,
                        'logid' => $credits[$key]->logid,
                        'money' => $credits[$key]->money,
                        'orderid' => $credits[$key]->orderid,
                        'pt_id' => $user->pt_id,
                        'reason' => $credits_reason,
                        'three' => $three,
                        'three_info' => $credits_three_info,
                        'uid' => $credits[$key]->uid,
                        'username' => $credits_username,
                    ];
            }


            return $this->getResponse(["sum" => $sum, "credits" => $arr_credits, 'today_sum' => $today_sum]);

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
