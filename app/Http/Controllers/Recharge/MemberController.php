<?php

namespace App\Http\Controllers\Recharge;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\RechargeSetting;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\ArticleOrders;
use App\Entitys\App\TaobaoUser;
use App\Entitys\Article\Agent;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;
use App\Services\Recharge\PurchaseUserGroup;
use App\Services\Recharge\RechargeUserLevel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mockery\Exception;
use function Psy\debug;
use Yansongda\Pay\Pay;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    /**
     *
     * 获取用户余额等级信息
     * 传入uid、check_code(明文)
     * @param Request $request
     * @param AdUserInfo $adUserInfo
     * @param UserAccount $userAccount
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, AdUserInfo $adUserInfo, UserAccount $userAccount, RechargeOrder $rechargeOrder, RechargeSetting $rechargeSetting, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('uid', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            $user = $adUserInfo->getUserById($arrRequest['uid']);
            if (!$user) {
                throw new ApiException('用户出现错误！', '3003');
            }
            $user_account = $userAccount->getUserAccount($arrRequest['uid']);
            if ($user->check_code <> $request->header('code')) {
                throw new ApiException('用户异常！', '3002');
            }
            $app_user = $appUserInfo->getUserById($user->pt_id);
            if (!$app_user) {
                throw new ApiException('用户数据异常！请联系客服', '3004');
            }
            $user_type = $rechargeOrder->getUserType($arrRequest['uid']);
            $setting = $rechargeSetting->getRechargeSetting($user_type);

            return $this->getResponse([
                "username" => $app_user->phone,
                "group_id" => $user->groupid,
                "account" => $user_account->extcredits3,
                "buy_setting" => $setting
            ]);
        } catch (Exception $e) {
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
     *
     * 购买成功更新用户等级
     * @param Request $request
     * @param RechargeUserLevel $rechargeUserLevel
     * @return \Illuminate\Http\JsonResponse|\Yansongda\Pay\Gateways\Alipay\AppGateway
     * @throws ApiException
     */
    public function store(Request $request, RechargeUserLevel $rechargeUserLevel)
    {
        try {
            /**************************= 参数验证 =***************************/
            $jsonParams = $request->data;
            if (empty($jsonParams)) {
                throw new ApiException('参数异常', '3001');
            }
            $arrParams = json_decode($jsonParams, true);
            $rules = [
                'act' => 'required',
                'gid' => 'required',
                'uid' => 'required',
                'type' => 'required'
            ];
            $validator = Validator::make($arrParams, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }
            if (Cache::has('recharge_member_' . $arrParams['uid'])) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试！...');
            }
            Cache::put('recharge_member_' . $arrParams['uid'], 1, 0.5);

            if ($arrParams['act'] != 'buy') {
                throw new ApiException('请求错误', 5001);
            }
            if ($arrParams['gid'] == '74') {
                throw new ApiException('我的商城购买礼包赠送超级用户价格不变！建议使用我的商城', 5555);
            }
            if ($arrParams['type'] == 3) {
                return $this->getInfoResponse('1002', '暂不支持微信支付！');
            }
            if ($arrParams['gid'] == '73') {
                return $this->getInfoResponse('2006', '暂时不支持购买!');
            }
            /**************************= 初始化一些参数 =***************************/
            $rechargeUserLevel->initOrder($arrParams);
            /**************************= 逻辑开始 =***************************/
            if ($arrParams['type'] == 1)
                $rechargeUserLevel->isAccount();
            list($order_id, $price, $desc) = $rechargeUserLevel->installOrder($arrParams['type']);
            if ($arrParams['type'] == 2) {

                return $this->getInfoResponse('1002', '支付宝模块更新中!请使用我的币或者微信支付！');
                $order = [
                    'out_trade_no' => $order_id,
                    'total_amount' => $price,
                    'subject' => $desc . ' - ' . $price . '元',
                ];
                $alipay = Pay::alipay($this->config)->app($order);
                return $alipay;
            }
            $rechargeUserLevel->updateExt();
            $rechargeUserLevel->updateUserAccountRMB();
            if ($arrParams['gid'] == '74') {
                $rechargeUserLevel->returnCommission();
            } else {
                $rechargeUserLevel->returnCommissionV12();
            }
            $rechargeUserLevel->handleArticle();
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('购买异常', 5003);
        }
        return $this->getResponse("开通成功");
    }

    /**
     *
     * 用户订单列表
     * @param $id
     * @param Request $request
     * @param RechargeOrder $rechargeOrder
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, RechargeOrder $rechargeOrder)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('uid', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($id <> $arrRequest['uid']) {
                throw new ApiException('信息被篡改！', '3002');
            }

            $orders = $rechargeOrder->getUserOrders($arrRequest['uid']);

            return $this->getResponse($orders);

        } catch (Exception $e) {
            throw new ApiException('服务器异常', '500');
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

    /*
     * 购买广告包
     */
    public function buyAdvertisingPackage(Request $request, UserAccount $userAccount, ArticleOrders $articleOrders, PurchaseUserGroup $purchaseUserGroup)
    {

        //return $this->getInfoResponse('2005', '系统升级期间暂停付款，预计1月1号升级完成，敬请期待！');
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
                'type' => Rule::in([1, 2]),
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type'];
            if ($app_id != 3675700) {
                return $this->getInfoResponse('2005', '系统升级期间暂停付款，请耐心等待！！');
            }
            /***********************************/
            if (Cache::has('recharge_member_' . $app_id)) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试！...');
            }
            Cache::put('recharge_member_' . $app_id, 1, 0.5);

            if ($type == 1) {
                $orderid_tag = date('YmdHis') . $purchaseUserGroup->random(18);
                $obj_data = $articleOrders->createOrder($app_id, $type, $orderid_tag);
                if (empty($obj_data)) {
                    throw new ApiException('网络异常，不存在的订单!', 5002);
                }
                $order = [
                    'out_trade_no' => $obj_data->order_id,          #当前交易订单号
                    'total_fee' => ($obj_data->pay_price * 100),    #微信单位为分
                    'body' => '商城购物 - ' . $obj_data->pay_price . '元',
                ];
                $we_config = array_replace(
                    config('unified_pay.we_config'),
                    [
                        'notify_url' => 'http://api.36qq.com/api/buy_advertising_package_XxX_we_notify'
                    ]);

                $we_secret = Pay::wechat($we_config)->app($order);
                return $this->getResponse($we_secret->getContent());
            } elseif ($type == 2) {
                $orderid_tag = date('YmdHis') . $purchaseUserGroup->random(18);
                $obj_data = $articleOrders->createOrder($app_id, $type, $orderid_tag);
                $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);
                if ($user_account->extcredits4 < $obj_data->pay_price * 10) {
                    return $this->getInfoResponse('3001', '我的币余额不足！');
                }

                $params = [
                    'pay_status' => 1,
                ];
                $articleOrders->upOrder($orderid_tag, $params);
                $this->takePtb($app_id, $obj_data->pay_price * 10);
                $this->handleArticle($app_id);
            }
            return $this->getResponse("开通成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('购买异常：' . $e->getLine(), '5003');
        }
    }

    /*
     * 购买广告包
     */
    public function buyAdvertisingPackageV1(Request $request, UserAccount $userAccount, ArticleOrders $articleOrders, PurchaseUserGroup $purchaseUserGroup)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
                'type' => Rule::in([1, 5]),
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type'];
            if ($app_id != 3675700) {
                return $this->getInfoResponse('2005', '系统升级期间暂停付款，请耐心等待！！');
            }
            /***********************************/
            if (Cache::has('recharge_member_' . $app_id)) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试！...');
            }
            Cache::put('recharge_member_' . $app_id, 1, 0.5);

            if ($type == 1) {
                $orderid_tag = date('YmdHis') . $purchaseUserGroup->random(18);
                $obj_data = $articleOrders->createOrder($app_id, $type, $orderid_tag);
                if (empty($obj_data)) {
                    throw new ApiException('网络异常，不存在的订单!', 5002);
                }
                $order = [
                    'out_trade_no' => $obj_data->order_id,          #当前交易订单号
                    'total_fee' => ($obj_data->pay_price * 100),    #微信单位为分
                    'body' => '商城购物 - ' . $obj_data->pay_price . '元',
                ];
                $we_config = array_replace(
                    config('unified_pay.we_config'),
                    [
                        'notify_url' => 'http://api.36qq.com/api/buy_advertising_package_XxX_we_notify'
                    ]);

                $we_secret = Pay::wechat($we_config)->app($order);
                return $this->getResponse($we_secret->getContent());
            } elseif ($type == 5) {
                $orderid_tag = date('YmdHis') . $purchaseUserGroup->random(18);
                $obj_data = $articleOrders->createOrder($app_id, $type, $orderid_tag);

//                $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();
//                $user_account = $userAccount->getUserAccount($ad_user_info->uid);

                $taobao_user = new TaobaoUser();//用户真实分佣表
                $int_taobao_user = $taobao_user->where('app_id', $app_id)->value('money');
                $int_taobao_user = empty($int_taobao_user) ? 0 : $int_taobao_user;
                if ($int_taobao_user < $obj_data->pay_price) {
                    return $this->getInfoResponse('3001', '余额不足！');
                }

                $params = [
                    'pay_status' => 1,
                ];
                $articleOrders->upOrder($orderid_tag, $params);
                //扣除余额元
                $this->takeMoney($app_id, $obj_data->pay_price);
                $this->handleArticle($app_id);
            }
            return $this->getResponse("开通成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('购买异常：' . $e->getLine(), '5003');
        }
    }

    /*
     * 购买广告包微信回调
     */
    public function weNotify()
    {
        $this->weLog('---------------start----------');
        $we_config = array_replace(
            config('unified_pay.we_config'),
            [
                'notify_url' => 'http://api.36qq.com/api/buy_advertising_package_XxX_we_notify'
            ]);
        $pay = Pay::wechat($we_config);
        try {
            $obj_data = $pay->verify();
            if ($obj_data->return_code != "SUCCESS") {
                $this->weLog('错误信息：' . $obj_data->return_msg);
                $this->weLog('---------------end----------');
                return 'error';
            }

            $order_id = $obj_data->out_trade_no;
            $actual = $obj_data->total_fee;

            $this->weLog('开始查询订单：' . $order_id);
            $md_article_order = new ArticleOrders();
            $order_info = $md_article_order->getUnpaidByOrderId($order_id);
            if (empty($order_info)) {
                $this->weLog('不存或已处理该订单：' . $order_id);
                $this->weLog('---------------end_error----------');
                return 'error';
            }

            $this->weLog('用户支付金额：' . ($actual / 100));
            if ($order_info->pay_price != ($actual / 100)) {
                $this->weLog('该用户实际支付金额有误：实付' . ($actual / 100) . '元');
                $this->weLog('---------------end_error----------');
                return 'error';
            }
            $this->weLog('开始判断订单类型');

            switch ($order_info->pay_type) {
                case 1:
                    $this->weLog('开始更新订单状态');
                    $params = [
                        'pay_status' => 1,
                    ];
                    $md_article_order->upOrder($order_id, $params);
                    $this->handleArticle($order_info->app_id);
                    $this->weLog('更新完成');
                    break;

                default:
                    $this->weLog('订单类型异常：' . $order_info->pay_type);
                    $this->weLog('---------------end_error----------');
                    return 'error';
            }

            $this->weLog('---------------end----------');

        } catch (\Throwable $e) {
            $this->weLog('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage());
            $this->weLog('---------------end_error----------');
            return 'error';
        }

        return $pay->success();

    }

    /*
     * 记录日志
     */
    private function weLog($msg)
    {
        Storage::disk('local')->append('callback_document/buy_advertising_package_notify.txt', var_export($msg, true));
    }

    /**
     * 通过用户 app_id 扣除相应我的币，并记录日志
     * $value 为我的币值
     * （独立方法，可直接调用）
     * @param $app_id
     * @param $value
     * @return bool
     * @throws ApiException
     */
    public function takePtb($app_id, $value)
    {
        try {
            $obj_user = new AdUserInfo();
            $obj_info = $obj_user->appToAdUserId($app_id);
            $user_uid = $obj_info->uid;
            $username = $obj_info->username;
            $obj_account = new UserAccount();
            $user_ptb = $obj_account->getUserAccount($user_uid)->extcredits4;
            $obj_account->subtractPTBMoney($value, $user_uid);
            $obj_credit_log = new UserCreditLog();
            $obj_about_log = new UserAboutLog();
            $insert_id = $obj_credit_log->addLog($user_uid, "ADP", ['extcredits4' => -$value]);
            $obj_about_log->addLog($insert_id, $user_uid, $username, $app_id, ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb - $value]);
        } catch (\Exception $e) {
            throw new ApiException('网络异常，扣费失败，请联系客服！', 5004);
        }

        return true;
    }

    /*
     * 购买广告包扣除余额
     */
    public function takeMoney($app_id, $value)
    {
        try {
            //扣除余额
            $obj_user_money = new UserMoney();
            $obj_user_money->minusCnyAndLog($app_id, $value, '20010', "ADP");
        } catch (\Exception $e) {
            throw new ApiException('网络异常，扣费失败，请联系客服！', 5004);
        }
        return true;
    }

    /*
     * 增加文章
     */
    public function handleArticle($app_id)
    {
        $obj_agent = new Agent();
        $obj_ad_info = new AdUserInfo();
        $res = $obj_agent->where('pt_id', $app_id)->first();
        $user_data = $obj_ad_info->where('pt_id', $app_id)->first();
        if ($res) {
            $res->number += 10;
            $res->update_time = time();
            $res->save();
        } else {
            $obj_agent->username = $user_data->pt_username;
            $obj_agent->pt_id = $user_data->pt_id;
            $obj_agent->uid = $user_data->uid;
            $obj_agent->update_time = time();
            $obj_agent->number = 10;
            $obj_agent->forever = 0;
            $obj_agent->save();
        }

    }
}
