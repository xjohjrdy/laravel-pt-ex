<?php

namespace App\Http\Controllers\Voip;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Ad\VoipAccount;
use App\Entitys\Ad\VoipMoneyOrder;
use App\Entitys\Ad\VoipType;
use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\Voip\Buy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yansongda\Pay\Pay;
use App\Services\PutaoRealActive\PutaoRealActive;

/**
 * @author 吴航
 * 充值资源
 * Class RechargeController
 * @package App\Http\Controllers\Voip
 */
class RechargeController extends Controller
{
    protected $PID = '2088531728490041';

    /**
     * 拉出订单列表
     * @param Request $request
     * @param VoipMoneyOrder $voipMoneyOrder
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, VoipMoneyOrder $voipMoneyOrder)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }
            $res = $voipMoneyOrder->getAllOrderByUser($arrRequest['app_id']);
            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * {"app_id":"","type_id":"","to_phone":"","buy_type":""}
     *                  上一步id                   1：支付宝，2：我的币
     * @param Request $request
     * @param VoipType $voipType
     * @param VoipMoneyOrder $voipMoneyOrder
     * @param AdUserInfo $adUserInfo
     * @param UserAccount $userAccount
     * @param Buy $buy
     * @return \Illuminate\Http\JsonResponse|\Yansongda\Pay\Gateways\Alipay\AppGateway
     * @throws ApiException
     */
    public function create(Request $request, VoipType $voipType, VoipMoneyOrder $voipMoneyOrder, AdUserInfo $adUserInfo, UserAccount $userAccount, Buy $buy, UserCreditLog $creditLog, UserAboutLog $aboutLog)
    {

        return $this->getInfoResponse('5551', '为提升更好的服务，我的通讯当前正在升级中，请耐心等待哦！');
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id']) || empty($arrRequest['type_id']) || empty($arrRequest['to_phone']) || empty($arrRequest['buy_type'])) {
                throw new ApiException('传入参数错误', '3001');
            }
            if (Cache::has('voip_recharge_create_' . $arrRequest['app_id'])) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试v...');
            }
            Cache::put('voip_recharge_create_' . $arrRequest['app_id'], 1, 0.5);
            if (!preg_match("/^1[3456789]\d{9}$/", $arrRequest['to_phone'])) {
                return $this->getInfoResponse('4004', '不是一个正常的手机号码');
            }
            $voip_type = $voipType->getById($arrRequest['type_id']);
            $real_price = $voip_type->real_price;
            $price = $voip_type->price;
            if ($arrRequest['buy_type'] == 2) {
                $ad_user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
                $ad_account = $userAccount->getUserAccount($ad_user->uid);
                if ($ad_account->extcredits4 < ($real_price * 10)) {
                    return $this->getInfoResponse('4005', '您账户上的我的币不够');
                }
            }
            $common_function = new  CommonFunction();
            $order_id = date('YmdHis') . $common_function->random(5);
            $order_voip = $voipMoneyOrder->generateNewOrder($arrRequest['app_id'], $arrRequest['to_phone'], $real_price, $price, $voip_type->title, $voip_type->remark, $voip_type->image, $arrRequest['buy_type'], $voip_type->time, $order_id);
            if ($arrRequest['buy_type'] == 1) {
                if ($arrRequest['app_id'] <> 1744932) {
                    return $this->getInfoResponse('4004', '支付宝正在升级中，请先用微信或者我的币支付');
                }
                if ($arrRequest['app_id'] == 1744932) {
                    $real_price = 0.01;
                }
                $order = [
                    'out_trade_no' => $order_voip->order_id,
                    'total_amount' => $real_price,
                    'subject' => '我的购物 - ' . $price . '元',
                ];
                $this->config['return_url'] = 'http://api.36qq.com/notify_url_voip_buy';
                $this->config['notify_url'] = 'http://api.36qq.com/notify_url_voip_buy';
                $alipay = Pay::alipay($this->config)->app($order);
                return $alipay;
            }
            if ($arrRequest['buy_type'] == 3) {
                return $this->getInfoResponse('4004', '请使用支付宝或我的币支付');
                if ($arrRequest['app_id'] == 1569840) {
                    $real_price = 0.01;
                }
                $order = [
                    'out_trade_no' => $order_voip->order_id,
                    'total_fee' => ($real_price * 100),
                    'body' => '我的购物 - ' . $price . '元',
                ];
                $this->wechat_config['notify_url'] = 'http://api.36qq.com/api/voip_wechat_pay_now_wuhang';
                $pay = Pay::wechat($this->wechat_config)->app($order);
                return $pay;
            }

            if ($arrRequest['buy_type'] == 2) {
                $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
                $ptb_price = $real_price * 10;
                $res_account = $userAccount->subtractPTBMoney($ptb_price, $user->uid);
                $insert_id = $creditLog->addLog($user->uid, "TXB", ['extcredits4' => -$ptb_price]);
                $extcredits4_change = $ad_account->extcredits4 - $ptb_price;
                $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $ad_account->extcredits4], ["extcredits4" => $extcredits4_change]);

                if ($res_account) {
                    $buy->overOrder($order_voip->id);
                }
                return $this->getResponse('购买成功！');
            }

            return $this->getResponse('您的支付方式选择错误！');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
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
     *
     * 充值卡充值
     * {"phone":"13194089498","card_name":"123456789","card_pass":"123456"}
     * @param Request $request
     * @param $id
     * @param Buy $buy
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     * @throws \Exception
     */
    public function update(Request $request, $id, Buy $buy)
    {
        DB::beginTransaction();
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('phone', $arrRequest) || !array_key_exists('card_name', $arrRequest) || !array_key_exists('card_pass', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $buy->overOrderCard($arrRequest['phone'], $arrRequest['card_name'], $arrRequest['card_pass']);

            DB::commit();
            return $this->getResponse('充值成功！');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * 用于微信支付回调
     */
    public function wechatCallBack(VoipMoneyOrder $voipMoneyOrder, Buy $buy)
    {

        Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export('step-1', true));
        $pay = Pay::wechat($this->wechat_config);

        try {
            $data = $pay->verify();

            Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export($data->out_trade_no, true));
            if ($data->return_code <> "SUCCESS") {

                Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export($data->out_trade_no, true));

                Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export($data->return_msg, true));
                exit();
            }
            $shop_order = $voipMoneyOrder->getByOrderId($data->out_trade_no);
            if (empty($shop_order)) {

                Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export('订单错误！', true));
                exit();
            }
            if ($shop_order->app_id == 1569840) {
                $shop_order->real_price = 0.01;
            }
            if ($data->total_fee <> ($shop_order->real_price * 100)) {

                Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export('金额不对等', true));

                Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export($data->total_fee, true));

                Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export("订单金额：" . $shop_order->real_price, true));
                exit();
            }
            $res_maid = $buy->overOrder($shop_order->id);

            Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export('step-2', true));
        } catch (\Throwable $e) {

            Storage::disk('local')->append('callback_document/wechat_pay_notify_voip.txt', var_export('error!need change is!', true));
        }
        return $pay->success();

    }

    /**
     * 用于阿里支付回调
     */
    public function aliCallBack(Request $request, VoipMoneyOrder $voipMoneyOrder, Buy $buy)
    {

        $alipay = Pay::alipay($this->config);
        try {

            $data = $request->request->count() > 0 ? $request->request->all() : $request->query->all();

            Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export($data, true));

            $data = $alipay->verify();

            Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export($data->trade_status, true));
            if ($data->trade_status == 'TRADE_SUCCESS' || $data->trade_status == 'TRADE_FINISHED') {

                Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export($data->out_trade_no, true));
                $shop_order = $voipMoneyOrder->getByOrderId($data->out_trade_no);
                if ($shop_order) {

                    Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export($data->out_trade_no, true));
                    if ($shop_order->app_id == 1744932) {
                        $shop_order->real_price = 0.01;
                    }
                    if ($shop_order->real_price == $data->total_amount) {

                        Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export($data->seller_id, true));
                        if ($data->seller_id == $this->PID) {

                            Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export($data->app_id, true));
                            if ($data->app_id == $this->config['app_id']) {

                                Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export("other ", true));
                                if ($shop_order->status == 0) {

                                    Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export("run", true));
                                    $res_maid = $buy->overOrder($shop_order->id);
                                }
                            }
                        }
                    }
                }
            }

            Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export('---------------end--------', true));
        } catch (\Exception $e) {
        }

        Storage::disk('local')->append('callback_document/test_alipay_notify_voip.txt', var_export('---------------' . $alipay->success() . '--------', true));
        return $alipay->success();
    }


    /**
     * 校验是否有权限进入电影
     * @param $app_id
     * @param VoipAccount $voipAccount
     * @return \Illuminate\Http\JsonResponse
     */
    public function IsNeedMovie($app_id, VoipAccount $voipAccount)
    {
        $movie_time = $voipAccount->getMovieTime($app_id);
        $is_need = 0;
        if ($movie_time > time()) {
            $is_need = 'http://vd.huitongtel.com';
        }
        return $this->getResponse($is_need);
    }

    /**
     * 问题列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestionList()
    {
        return $this->getResponse([
            [
                'question' => '什么情况下我的通讯不能打？',
                'answer' => '答:无网络或主卡欠费的情况下不能打。'
            ],
            [
                'question' => '我的通讯里有什么的电话不能拨打？',
                'answer' => '答:固话.国际长途.'
            ],
            [
                'question' => '拨打电话后出现的“我的专线”字样是什么意思？',
                'answer' => '答：这是我的通讯跟三大运营商合作的专线，接通即可拨打到对方号码中，对方显示的是您的本机号码，跟您平时拨打电话是一样的。'
            ],
            [
                'question' => '我的通讯资费说明',
                'answer' => '1、我的通讯三网通用，通话期间不扣自身运营商的流量和话费。（注：不能抵扣自身套餐月租等）。

2、我的通讯通话1分钟0.25元，不满1分钟则按一分钟计算。

话费价格：

1、100元话费30元（相当于一分钟：0.075元），

2、300元话费85元（相当于一分钟：0.07），

3、500元话费125元（相当于一分钟：0.0625元）。

'
            ],
        ]);
    }
}
