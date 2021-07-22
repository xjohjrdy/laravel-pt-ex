<?php

namespace App\Http\Controllers\Voip;

use App\Entitys\Ad\VoipAccount;
use App\Entitys\Ad\VoipMoneyOrder;
use App\Entitys\App\UserCheckAllFunction;
use App\Entitys\App\VoipGpsInfo;
use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use App\Services\Voip\Call;
use App\Services\Voip\Special;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{

    /**
     * 发送拨打电话请求
     * @param Request $request
     * @param Call $call
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws ApiException
     */
    public function index11(Request $request, Call $call)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $validator = Validator::make($arrRequest, [
                'user_id' => 'required',
                'my_phone' => 'required',
                'to_phone' => 'required',
            ]);

            if ($validator->fails()) {
                return response('传入参数错误', 500);
            }

            return $this->getInfoResponse('5551', '为提升更好的服务，通讯当前正在升级中，请耐心等待下哦！');

            $user_id = $arrRequest['user_id'];
            $my_phone = $arrRequest['my_phone'];
            $to_phone = $arrRequest['to_phone'];
            if (strlen($to_phone) <> 11) {
                return $this->getInfoResponse('5005', '请输入正常的手机号拨打！');
            }
            $call->user_id = $user_id;
            if (!$call->verifyMoney()) {
                return $this->getInfoResponse('2001', '话费余额不足');
            }
            $call->callUpNew($my_phone, $to_phone);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

        return $this->getResponse('呼叫成功');

    }

    /*
     * 新VOIP通讯
     */
    public function xinVoip(Request $request, Call $call)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $validator = Validator::make($arrRequest, [
                'user_id' => 'required',
                'my_phone' => 'required',
                'to_phone' => 'required',
            ]);

            if ($validator->fails()) {
                return response('传入参数错误', 500);
            }

            $user_id = $arrRequest['user_id'];
            $my_phone = $arrRequest['my_phone'];
            $to_phone = $arrRequest['to_phone'];
            if (strlen($to_phone) <> 11) {
                return $this->getInfoResponse('5005', '请输入正常的手机号拨打！');
            }
            $call->user_id = $user_id;
            if (!$call->verifyMoney()) {
                return $this->getInfoResponse('2001', '话费余额不足');
            }
            $call->callUpNew($my_phone, $to_phone);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

        return $this->getResponse('呼叫成功');

    }

    /*
     * 久话通讯
     */
    public function index(Request $request, Call $call, UserCheckAllFunction $userCheckAllFunction, VoipMoneyOrder $voipMoneyOrder, VoipGpsInfo $voipGpsInfo)
    {
//        return $this->getInfoResponse('4414', '葡萄通讯优化升级中，预计国庆节后恢复使用！');

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $validator = Validator::make($arrRequest, [
                'user_id' => 'required',
                'my_phone' => 'required',
                'to_phone' => 'required',
//                'gps' => 'required',
            ]);

            if ($validator->fails()) {
                return response('传入参数错误', 500);
            }

            if (empty($arrRequest['version'])) {
                return $this->getInfoResponse('4441', '当前不是最新版，请升级为最新版本才能继续使用！');
            }

            $user_id = $arrRequest['user_id'];
            $my_phone = $arrRequest['my_phone'];
            $to_phone = $arrRequest['to_phone'];
            $gps = empty($arrRequest['gps']) ? '' : $arrRequest['gps'];

            if ($user_id == 6080694) {
                return $this->getInfoResponse('4674', '为了提升更好的服务，葡萄通讯当前正在升级中，预计8月升级完成，请耐心等待哦！');
            }

            $voipGpsInfo->create(['app_id' => $user_id, 'gps' => $gps]);

            $wechatInfo = new WechatInfo();
            $info_is_wechat = $wechatInfo->getAppId($user_id);

            if (empty($info_is_wechat)) {
                return $this->getInfoResponse('4444', '为了提供更好的服务，葡萄通讯升级中，升级后需先到【个人中心】-关联微信才能继续使用葡萄通讯');
            }
            $call->user_id = $user_id;
            $obj_voip_account = new VoipAccount();
            $user_money = $obj_voip_account->where('app_id', $user_id)->value('money');
            if ($user_money <= 0.25) {
                return $this->getInfoResponse('2001', '余额不足以拨打电话');
            }
            if (!$call->verifyMoney()) {
                return $this->getInfoResponse('2001', '话费余额不足');
            }

            $voip_money_orders = $voipMoneyOrder->where(['app_id' => $user_id])->first();

            if (!empty($voip_money_orders)) {
                $is_white = $call->whiteList($user_id);
                if (empty($is_white)) {
                    $user_check_all_function = $userCheckAllFunction->getOne($user_id);
                    if (empty($user_check_all_function) || $user_check_all_function->is_bind != 1) {
                        return $this->getInfoResponse('4674', '为了提升更好的服务，葡萄通讯当前正在升级中，预计8月升级完成，请耐心等待哦！');
                    }
                }
            }
            $res = $call->checkSCheck($user_id, $to_phone);
            if ($res) {
                $s = new Special();
                $res = $s->pushD('主叫号码：' . $my_phone . '被叫号码：' . $to_phone . '，用户拨打次数已达到上限，请留意该号码其他行为！');
                return $this->getInfoResponse('2007', '拨打过于频繁，等待10分钟后才能打！');
            }


            if (strlen($to_phone) <> 11) {
                return $this->getInfoResponse('5005', '请输入正常的手机号拨打！');
            }
            $res = $call->jiuHuaIsBlacklist($user_id, $my_phone);
            if ($res) {
                return $this->getInfoResponse('2005', '您今天的拨打次数已上限，请24小时后再来使用！');
            }
            $res = $call->isCall($to_phone);
            if (empty($res)) {
                return $this->getInfoResponse('2006', '您今天的拨打次数已上限，请24小时后再来使用！！');
            }
            $call->callUpJiuHua($my_phone, $to_phone);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

        return $this->getResponse('呼叫成功');

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

    /**
     * 回调函数
     * @param Request $request
     * @param Call $call
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function callback(Request $request, Call $call)
    {

        $get_param = $request->all();

        $validator = Validator::make($get_param, [
            'orderid' => 'required',
            'state' => 'required',
            'hold_time' => 'required',
            'fee_time' => 'required|regex:/^\d+$/',
        ]);

        if ($validator->fails()) {
            return response('error', 500);
        }
        if (!$call->verifyOrder($get_param['orderid'])) {
            return response('no', 501);
        }
        $call->updateOrder($get_param);

        return response('1', 200);

    }

    /*
     * 新的通讯回调函数
     */
    public function callbackNew(Request $request, Call $call)
    {
        $get_param = $request->all();

        $validator = Validator::make($get_param, [
            'orderid' => 'required',
            'state' => 'required',
            'hold_time' => 'required',
            'fee_time' => 'required|regex:/^\d+$/',
        ]);

        if ($validator->fails()) {
            return response('error', 500);
        }
        if (!$call->verifyOrder($get_param['orderid'])) {
            return response('no', 501);
        }
        $call->updateOrder($get_param);

        return response('1', 200);

    }

    /*
    * 久话的通讯回调函数
    */
    public function callbackJiuHua(Request $request, Call $call)
    {
        $get_param = $request->all();
        $validator = Validator::make($get_param, [
            'Call_id' => 'required',#呼叫唯一标识符
            'Caller' => 'required',#主叫
            'Callee' => 'required',#被叫
            'Call_duration' => 'required|regex:/^\d+$/',   #通话时间(秒) 正整数
        ]);
        if ($validator->fails()) {
            return response('error', 500);
        }
        if (!$call->verifyOrder($get_param['Call_id'])) {
            return response('no', 501);
        }
        $call->updateOrderJiuHua($get_param);

        return response('200', 200);

    }

    /**
     * 用户个人中心
     * @param Request $request
     * @param Call $call
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws ApiException
     */
    public function indexInfo(Request $request, Call $call)
    {

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $validator = Validator::make($arrRequest, [
                'user_id' => 'required',
                'my_phone' => 'required',
            ]);

            if ($validator->fails()) {
                return response('传入参数错误', 500);
            }

            $user_id = $arrRequest['user_id'];
            $user_phone = $arrRequest['my_phone'];
            $call->user_id = $user_id;
            $call->user_phone = $user_phone;
            $call->checkFailure();
            $arr_account_info = $call->getAccountInfo();
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

        return $this->getResponse($arr_account_info);
    }

    /**
     * 得到话单列表
     * @param Request $request
     * @param Call $call
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getOrderList(Request $request, Call $call)
    {

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $validator = Validator::make($arrRequest, [
                'user_id' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
            ]);

            if ($validator->fails()) {
                return response('传入参数错误', 500);
            }

            $call->user_id = $arrRequest['user_id'];
            $start_time = $arrRequest['start_time'];
            $end_time = $arrRequest['end_time'];

            $arr_order_list = $call->getOrderInfo($start_time, $end_time);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

        return $this->getResponse($arr_order_list);

    }

    /**
     * 得到回拨电话列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPhoneList()
    {
        $phone_list = '08326124130,08326124131,08326124132,08326124133,08326124134,08326124135,08326124136,08326124137,08326124138,08326124139,08326124140,08326124141,08326124142,08326124143,08326124144,08326124145,08326124146,08326124147,08326124148,08326124149,08326124150,08326124151,08326124152,08326124153,08326124154,08326124155,08326124156,08326124157,08326124158,08326124159';

        return $this->getResponse($phone_list);
    }

}
