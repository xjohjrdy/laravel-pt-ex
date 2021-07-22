<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\CircleUserAdd;
use App\Entitys\App\TaobaoUser;
use App\Exceptions\ApiException;
use App\Services\Circle\Add;
use App\Services\Common\CommonFunction;
use App\Services\Common\UserMoney;
use App\Services\HeMengTong\HeMeToServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yansongda\Pay\Pay;

class RingAddController extends Controller
{
    /**
     * 展示用户列表
     * @param Request $request
     * @param CircleRingAdd $circleRingAdd
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleRingAdd $circleRingAdd)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_ring = $circleRingAdd->getAllByCircle($arrRequest['circle_id']);
            return $this->getResponse($circle_ring);
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
     * 圈子加入新用户
     * @param Request $request
     * @param CircleRing $circleRing
     * @param AdUserInfo $adUserInfo
     * @param UserAccount $userAccount
     * @param UserCreditLog $creditLog
     * @param UserAboutLog $aboutLog
     * @param CircleUserAdd $circleUserAdd
     * @param Add $add
     * @return \Illuminate\Http\JsonResponse|\Yansongda\Pay\Gateways\Alipay\AppGateway
     * @throws ApiException
     */
    public function store(Request $request, CircleRing $circleRing, CircleRingAdd $circleRingAdd, AdUserInfo $adUserInfo, UserAccount $userAccount, UserCreditLog $creditLog, UserAboutLog $aboutLog, CircleUserAdd $circleUserAdd, Add $add)
    {

        return $this->getInfoResponse('3001', '圈子模块升级中，开放时间请留意公告通知~');
        try {
            DB::beginTransaction();
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
                'buy_type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_ring = $circleRing->getById($arrRequest['circle_id'], 1);
            $add_price_ptb = $circle_ring->add_price * 10;
            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            $user_account = $userAccount->getUserAccount($user->uid);
            $res_count = $circleRingAdd->getAllUserCount($arrRequest['app_id']);
            if ($res_count > 50 && $user->groupid < 23) {
                return $this->getInfoResponse('3001', '普通用户加入圈子上限50个，若有需要加入更多圈子，请退出已有圈子重试或升级为超级用户！');
            }
            $common_function = new  CommonFunction();
            $order_id = date('YmdHis') . $common_function->random(5);
            $arrRequest['money'] = $circle_ring->add_price;
            $arrRequest['to_app_id'] = $circle_ring->app_id;
            $arrRequest['use_time'] = $circle_ring->use_time;
            $circleUserAdd->createOrUpdateAdd($order_id, $arrRequest);
            if ($arrRequest['buy_type'] == 1) {
                if ($add_price_ptb <> 0) {
                    if ($user_account->extcredits4 < $add_price_ptb) {
                        return $this->getInfoResponse('4000', '葡萄币不足！');
                    }
                }
                if ($add_price_ptb > 0) {
                    $res_account = $userAccount->subtractPTBMoney($add_price_ptb, $user->uid);
                    $insert_id = $creditLog->addLog($user->uid, "CAP", ['extcredits4' => -$add_price_ptb]);
                    $extcredits4_change = $user_account->extcredits4 - $add_price_ptb;
                    $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $user_account->extcredits4], ["extcredits4" => $extcredits4_change]);
                }
                $add->overOrder($order_id);
                DB::commit();
                return $this->getResponse('葡萄币支付成功！');
            }
            if ($arrRequest['buy_type'] == 2) {
                $order = [
                    'out_trade_no' => $order_id,
                    'total_amount' => $circle_ring->add_price,
                    'subject' => '商城购物 - ' . $circle_ring->add_price . '元',
                ];
                $this->config['return_url'] = 'http://api_new.36qq.com/api/notify_url_circle_wuhang_add';
                $this->config['notify_url'] = 'http://api_new.36qq.com/api/notify_url_circle_wuhang_add';
                $alipay = Pay::alipay($this->config)->app($order);
                DB::commit();
                return $alipay;
            }

            DB::commit();
            return $this->getInfoResponse('5000', '圈子正在升级中，暂时无法加入哦！');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取成员的单个信息
     * @param Request $request
     * @param $id
     * @param CircleRingAdd $circleRingAdd
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circleRingAdd = new CircleRingAdd();
            $circle_ring = $circleRingAdd->getByAppCircle($arrRequest['circle_id'], $arrRequest['app_id']);
            return $this->getResponse($circle_ring);
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
     * 更新圈子里面的用户信息
     * @param Request $request
     * @param $id
     * @param CircleRing $circleRing
     * @param CircleRingAdd $circleRingAdd
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, CircleRing $circleRing, CircleRingAdd $ringAdd)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id_now' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_ring = $circleRing->getById($arrRequest['circle_id'], 1);
            if ($arrRequest['app_id_now'] <> $circle_ring->app_id) {
                return $this->getInfoResponse('4000', '您没有权限修改这个圈子！');
            }

            if ($arrRequest['app_id'] == $arrRequest['app_id_now']) {
                return $this->getInfoResponse('4000', '您不能自己踢出或者修改自己！');
            }
            unset($arrRequest['app_id_now']);
            if (!empty($arrRequest['deleted_at'])) {
                $arrRequest['deleted_at'] = date('y-m-d h:i:s', time());
            } else {
                unset($arrRequest['deleted_at']);
            }
            $res = $ringAdd->updateOneUser($arrRequest['circle_id'], $arrRequest['app_id'], $arrRequest);

            return $this->getResponse('修改成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
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
     * post支付宝回调
     */
    public function callBackByWuHang(Request $request, Add $add, CircleUserAdd $circleUserAdd)
    {
        $obj_ali_pay = Pay::alipay(config('ring.ali_add_config'));
        $obj_data = $obj_ali_pay->verify();

        $add->log('---------------start----------');
        $add->log($obj_data->toArray());
        if ($obj_data->trade_status != 'TRADE_SUCCESS' && $obj_data->trade_status != 'TRADE_FINISHED') {
            $add->log('---------------end----------');
            return 'error';
        }
        if ($obj_data->seller_id != config('ring.ali_pid')) {
            $add->log('---------------end_error----------');
            return 'error';
        }

        $order_id = $obj_data->out_trade_no;
        $actual = $obj_data->total_amount;
        $order_info = $circleUserAdd->getOrder($order_id);
        if (empty($order_info)) {
            $add->log('存在该订单：' . $order_id);
            $add->log('---------------end_error----------');
            return 'error';
        }
        if ($order_info->money != $actual) {
            $add->log('该用户实际支付金额有误：实付' . $actual . '元');
            $add->log('---------------end_error----------');
            return 'error';
        }
        if ($order_info->status == 1) {
            $add->log('该笔订单已经支付过：' . $order_id);
            $add->log('---------------end_error----------');
            return 'error';
        }
        $add->overOrder($order_id);
        $add->log('---------------end----------');
        return $obj_ali_pay->success();
    }

    /*
     * 加入圈子扣除余额
     */
    public function addCircleV1(Request $request, CircleRing $circleRing, CircleRingAdd $circleRingAdd, AdUserInfo $adUserInfo, UserAccount $userAccount, UserCreditLog $creditLog, UserAboutLog $aboutLog, CircleUserAdd $circleUserAdd, Add $add)
    {

//        return $this->getInfoResponse('4442', '圈子模块升级中，敬请期待！');
        try {
            DB::beginTransaction();
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
                'buy_type' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_ring = $circleRing->getById($arrRequest['circle_id'], 1);
            $add_price_ptb = $circle_ring->add_price * 10;
            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            $user_account = $userAccount->getUserAccount($user->uid);
            $res_count = $circleRingAdd->getAllUserCount($arrRequest['app_id']);
            if ($res_count > 50 && $user->groupid < 23) {
                return $this->getInfoResponse('3001', '普通用户加入圈子上限50个，若有需要加入更多圈子，请退出已有圈子重试或升级为超级用户！');
            }
            $common_function = new  CommonFunction();
            $order_id = date('YmdHis') . $common_function->random(5);
            $arrRequest['money'] = $circle_ring->add_price;
            $arrRequest['to_app_id'] = $circle_ring->app_id;
            $arrRequest['use_time'] = $circle_ring->use_time;
            $circleUserAdd->createOrUpdateAdd($order_id, $arrRequest);

            $taobao_user = new TaobaoUser();//用户真实分佣表
            $int_taobao_user = $taobao_user->where('app_id', $arrRequest['app_id'])->value('money');
            $int_taobao_user = empty($int_taobao_user) ? 0 : $int_taobao_user;
            if ($arrRequest['buy_type'] == 5) {
                if ($add_price_ptb <> 0) {
                    if ($int_taobao_user * 10 < $add_price_ptb) {
                        return $this->getInfoResponse('4000', '余额不足！');
                    }
                }
                if ($add_price_ptb > 0) {
                    //扣除余额
                    $obj_user_money = new UserMoney();
                    $obj_user_money->minusCnyAndLog($arrRequest['app_id'], $add_price_ptb / 10, '20011', "CAP");
                }
                $add->overOrder($order_id);
                DB::commit();
                return $this->getResponse('余额支付成功！');
            }
            if ($arrRequest['buy_type'] == 2) {
                //改为禾盟通支付
//                $order = [
//                    'out_trade_no' => $order_id,
//                    'total_amount' => $circle_ring->add_price,
//                    'subject' => '商城购物 - ' . $circle_ring->add_price . '元',
//                ];
//                $this->config['return_url'] = 'http://api_new.36qq.com/api/notify_url_circle_wuhang_add';
//                $this->config['notify_url'] = 'http://api_new.36qq.com/api/notify_url_circle_wuhang_add';
//                $alipay = Pay::alipay($this->config)->app($order);
//                $ali_secret = Pay::alipay($this->config)->app($order);

                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appPayCircleJoin($circle_ring->add_price, $circle_ring->add_price, $order_id);
                $res = json_decode($data, true);
                DB::commit();
//                return $alipay;
//                return $this->getResponse($ali_secret->getContent());
                if (@$res['fcode'] != 10000) {
                    return $this->getResponse('购买失败！请联系客服');
                }
                return $this->getResponse(@$res['fcode_url']);
            }
            //新增加入圈子微信支付
            if ($arrRequest['buy_type'] == 3) {
                //改为禾盟通支付
                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appWxPayCircleJoin($circle_ring->add_price, $order_id, $arrRequest['app_id']);
                DB::commit();
                return $this->getResponse($data);
            }

            DB::commit();
            return $this->getInfoResponse('5000', '圈子正在升级中，暂时无法加入哦！');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
