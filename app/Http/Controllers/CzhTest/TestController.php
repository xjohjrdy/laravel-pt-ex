<?php

namespace App\Http\Controllers\CzhTest;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\App\CardEnterOrders;
use App\Entitys\App\CardMaid;
use App\Entitys\App\CoinTaskConfig;
use App\Entitys\App\CoinTaskFinishLog;
use App\Entitys\App\PddEnterOrders;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\PutNewGetMoney;
use App\Entitys\App\ShopOrders;
use App\Exceptions\ApiException;
use App\Extend\Random;
use App\Services\Ali\AliOrderService;
use App\Services\Alimama\BigWashUser;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\CoinPlate\CoinConst;
use App\Services\CoinPlate\MainService;
use App\Services\Common\UserMoney;
use App\Services\Dplus\Dplus;
use App\Services\Evisa\EVisaServices;
use App\Services\HarryPay\Harry;
use App\Services\JdCommodity\JdCommandServices;
use App\Services\KaDuoFen\KaDuoFenServices;
use App\Services\Other\OtherCountService;
use App\Services\Pay\PayPaiService;
use App\Services\Pay\SwiftPassPay;
use App\Services\PddCommodity\PddCommodityServices;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\Shop\Order;
use App\Services\TencentCloud\CaptchaService;
use App\Services\TencentCloud\Common\Credential;
use App\Services\UCNews\UCNewsService;
use App\Services\Pay\CloudPay;
use App\Services\UPush\UPushService;
use com\unionpay\acp\sdk\AcpService;
use com\unionpay\acp\sdk\SDKConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class TestController extends Controller
{


    public function getHarryResult(Request $request){
        try {
//
            $params = $request->input();
            $harryService = new Harry();
            $res = $harryService->getPushResult($params['id']);
            return $this->getResponse($res);
        }catch (\Throwable $e){
            return $this->getResponse($e->getMessage());
        }
    }

    public function getRangeMoney(Request $request){
        try {
//
            $putMoneyModel = new PutNewGetMoney();
            $res = $putMoneyModel->get();
            return $this->getResponse($res);
        }catch (\Throwable $e){
            return $this->getResponse($e->getMessage());
        }
    }
    public function getInviteGift(Request $request){
        try {
            $sql = 'select l1.app_id,l1.show_info,l1.success_add,l1.change,l2.order_id,l2.reward_id,l2.title,l2.address,l2.real_name,l2.phone,l2.money from lc_put_new_rank_list l1 LEFT JOIN lc_put_new_reward_real l2 on l1.app_id = l2.app_id
ORDER BY l1.success_add DESC
LIMIT 50';
            $res = DB::connection('app38')->table(DB::raw("($sql) cc"))->get();
            return $this->getResponse($res);
        }catch (\Throwable $e){
            return $this->getResponse($e->getMessage());
        }
    }

    public function getHarryResult2(Request $request){
        try {

//
            $params = $request->input();
            $harryService = new \App\Services\HarryPayOut\Harry();
            $res = $harryService->getPushResult($params['id']);
            return $this->getResponse($res);
        }catch (\Throwable $e){
            return $this->getResponse($e->getMessage());
        }
    }
    //
//{
//"code": 200,
//"msg": "请求成功",
//"data": {
//"rt1_bizType": "AppPayApplet",
//"rt2_retCode": "0000",
//"rt3_retMsg": "成功",
//"rt4_customerNumber": "20200117144332XXD429A00296814B62",
//"rt5_orderId": "202007090151308882EB4981CFD8B1A1",
//"rt6_serialNumber": "4651408441",
//"rt7_payType": "APPLET",
//"rt8_appid": "wx7d5b2c444c3748c9",
//"rt9_tokenId": "",
//"rt10_payInfo": {
//"appId": "wx7d5b2c444c3748c9",
//"timeStamp": "1581921710",
//"nonceStr": "b9818668012a4209bf19aefac14e2987",
//"package": "prepay_id=wx17144150413368a3f2d4e1e01002932900",
//"signType": "RSA",
//"paySign": "fkJVEcHSHxnOu2HjGCYI8veokyaHkeBRde/2/zwQajkc2S/C2WbC9SQd7fLKRrIte2An3F8ctynbDSx6cbgAlr8898KV44sjRdfwDtjY8RBVgFkYA69fHwC7OwOrDpLCe9E5ep0VOhv4nyQZE3NHeTkMI7cgmWKrbbwKyA08eWhdpLKJZs+nKX+kiC4XZRQ40zE6jaXydhWNNMTd6EAPc3uSs+4vELDV3n9OhRLZRYRXRCNoE2hIKHEM9ekboOvlRoVrqqU/62tw0btCBjhEk9GJEvYzNb7MXfzjnhMDYApzR3zF62DXGH//VyNkLTt6c339+32a1hMNxd8JLFFbAw=="
//},
//"rt11_orderAmount": "0.02",
//        "rt12_currency": "CNY",
//        "rt13_channelRetCode": "0000",
//        "rt14_appPayType": "WXPAY",
//        "sign": "473B10F85FDF0821DC2E1E5C8359ED6C"
//    }
//}
    public function dodo(Request $request, RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, ShopOrders $shopOrders, Order $order_model)
    {
//        halt($request->fullUrlIs());
        try {
//            $aliService = new AliOrderService();
//            $aliService->addOrderCommissionV2('281658AD4873546304', '4353124', 100);
//            $aliService->reduceOrderCommissionV1('281658AD4873546304');
//            $JdSerivce = new JdCommandServices();
//            $JdSerivce->delDatum('102944541884', '46537074735');
            $time = time();
            $coinService = new MainService(7134356, time()); //
            $coinCommonService = new CoinCommonService('7134356');
            $coinCommonService->plusCoin(123, CoinConst::COIN_MINUS_ARTICLE_READ, '加金币备注');
            $coinCommonService->minusCoin(-123, CoinConst::COIN_MINUS_ANSWER, '扣金币备注');
            $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $taskModel = new CoinTaskConfig();
            $taskLogModel = new CoinTaskFinishLog();
            $coinService->coinSign();
//            $coinCommonService->successTask(3, $time, true);
            return $this->getResponse('');
            $payPayService = new PayPaiService();
            $res = $payPayService->checkBusinessMoney(1);
//            $res = $payPayService->queryOrderInfo(1, 'WXAPPLET20200305124859R6kvz', ''); // 4764691625
            $res = json_decode($res, true);
            return $this->getResponse($res);
//        $jdService = new JdCommandServices();
//        $c_params = [
//            'app_id' => '4353124',    //该笔订单的主人app_id
//            'trade_id' => '102944541884',    //该笔订单大id
//            'sku_id' => '46537074735',    //该笔订单里面的子订单商品id
//            'maid_money' => 100,    //该笔订单完整佣金
//        ];
//        $jdService->commissionV2($c_params);
//        $this->anewCommissionAllot2('4353124', 10000, '191130-207070495690233');
//        $evService = new EVisaServices();
//        $res = $evService->getContractStatus();
//        $res = $payPayService->h5Pay('33448', '202007090151308882EB4981CFD8B1312', '0.02', '测试商品', '');
//        $res = $payPayService->pay(
//            Random::alpha(32),
//            'wx34989a331407111a',
//            '',
//            'oju5s1G_Uj0d24EMe5qc_dXgG6ag',
//            '0.02',
//            '127.0.0.1',
//            '测试商品',
//            '测试商品'
//        );
//
//        $res = $payPayService->refund(
//            "202007090151308882EB4981CFD8B1A3",
//            "202007090151308882EB4981CFD8B1A2",
//            "0.02",
//            "11"
//        );
//        $dplus = new Dplus(1);
//        $res = $dplus->sendIOSCustomizedcast('测试通知', '7134356', 1, 1, 'www.baidu.com', 333);
//        $res = $dplus->sendAndroidCustomizedcast('1111', '111', '123', '1777977', 1, 1, 'www.baidu.com', 333);
//            $busines_nos = [
//                [
//                    'no' => '20200210122504XX6428FCCC5131420D',
//                    'key' => '20200210122504XX04CE7876749D4285' // 福州合泰隆贸易有限公司
//                ],
//                [
//                    'no' => '20200117144332XXD429A00296814B62',
//                    'key' => '20200117144332XXC62FA1FCA5B14345' // 福州梓妍嘉业贸易有限公司
//                ],
//                [
//                    'no' => '20200117144333XX0342E5482BB34F53',
//                    'key' => '20200117144333XX5EDCF411CBC74DB6' // 福州梓妍嘉业贸易有限公司
//                ]
//            ];

//        $params = array (
//            'rt1_customerNumber' => '20200210122504XX6428FCCC5131420D',
//            'rt2_orderId' => 'WXAPPLET202002231008583utg5',
//            'rt3_systemSerial' => '4683732055',
//            'rt4_status' => 'SUCCESS',
//            'rt5_orderAmount' => '0.02',
//            'rt6_currency' => 'CNY',
//            'rt7_timestamp' => '1582423755947',
//            'rt8_desc' => NULL,
//            'rt10_openId' => 'oGex90kNyj0mvBdfmYWjsSNxauDM',
//            'rt11_channelOrderNum' => '4200000519202002239046473465',
//            'rt12_orderCompleteDate' => '2020-02-23 10:09:18',
//            'rt13_onlineCardType' => 'UNKNOWN',
//            'rt14_cashFee' => '0.02',
//            'rt15_couponFee' => '0.00',
//            'rt16_fundBillList' => NULL,
//            'rt17_outTransactionOrderId' => '4200000519202002239046473465',
//            'rt18_bankType' => 'OTHERS',
//            'rt19_subOpenId' => 'o8CPF5Mz4NegSCZLDrYfeED-Qjq0',
//            'rt20_orderAttribute' => 'UNDIRECT_DEFAULT',
//            'rt21_marketingRule' => NULL,
//            'rt22_promotionDetail' => NULL,
//            'rt23_paymentAmount' => '0.02',
//            'rt24_creditAmount' => '0.02',
//            'rt25_appId' => 'wx5c32420b46e89fd0',
//            'rt26_appPayType' => 'WXPAY',
//            'rt27_payType' => 'PUBLIC',
//            'rt28_userId' => '6633743',
//            'ruleJson' => NULL,
//            'sign' => '33691A7F0597CECF471CCA3EB1C527E0',
//        );
//        $payService = new PayPaiService();
//        $payService->h5CallBack($params, $rechargeOrder, $rechargeUserLevel, $shopOrders, $order_model);
//        return $this->getResponse(json_decode($res, true));
        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }
    }

    public function cardRecover(Request $request, RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, ShopOrders $shopOrders, Order $order_model)
    {
//        halt($request->fullUrlIs());
        try {
            $list = DB::connection('app38')->select('select l1.*, l2.orgBonus, l2.bonus, l2.bonusRate from lc_card_maid l1, lc_card_enter_orders l2
where l1.type = 1 and l1.record_id = l2.record_id and l1.maid_ptb = l2.bonus * 5');
            $userMoneeService = new UserMoney();
            $userCardMaidModel = new CardMaid();
            foreach ($list as $item){
                try {
                    DB::connection('app38')->beginTransaction();
                    $cny = $item->bonus - $item->maid_ptb / 10;
                    $userMoneeService->plusCnyAndLogNoTrans($item->app_id, $cny, '58', 'ord:' . $item->record_id);
                    $userCardMaidModel->where(['app_id' => $item->app_id, 'record_id' => $item->record_id, 'type' => 1])->update([
                        'maid_ptb' => $item->bonus * 10
                    ]);
                    DB::connection('app38')->commit();
                } catch (\Throwable $e) {
                    DB::connection('app38')->rollBack();
                    continue;
                }
            }
            return $this->getResponse($list);
        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }
    }



    /**
     * 根据信息获取电签H5地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dodo1(Request $request)
    {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }//仅用于测试兼容旧版-----------------线上可删除
        $arrRequest = json_decode($request->data, true);
        $name = "陈政航";
        $mobile = "15980271371";
        $certificateType = "1";
        $idNumber = "350322199210085213";
        $bankNum = "6212261402043016440";
        $workNumber = "5891507";
        $evService = new EVisaServices();
        $res = $evService->getEncryptionUrl($workNumber, $name, $mobile, $certificateType, $idNumber, $bankNum);
        return $this->getResponse($res);
    }

    public function anewCommissionAllot2($app_id, $promotion_amount, $order_sn)
    {
        $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

        if (empty($ad_user_info)) {
            return false;
        }
        $commission = $promotion_amount / 100;#分变元
        $group_id = $ad_user_info->groupid;
        if (in_array($group_id, [23, 24])) {
            $f_commission = round($commission * 0.675, 2);
        } else {
            $f_commission = round($commission * 0.45, 2);
        }
        $order_commission = $f_commission;
        if (PddMaidOld::where(['trade_id' => (string)$order_sn, 'app_id' => (string)$app_id, 'type' => 2])->exists()) {
            return $order_commission;
        }
        PddMaidOld::create([
            'father_id' => 0,
            'trade_id' => (string)$order_sn,
            'app_id' => $app_id,
            'group_id' => $group_id,
            'maid_money' => $f_commission,
            'type' => 2,
            'real' => 0,
        ]);

        $due_rmb = 0;
        $tmp_next_id = $ad_user_info->pt_pid;
        $parent_info = AdUserInfo::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);
        $p_groupid = $parent_info['groupid'];
        $p_pt_id = $parent_info['pt_id'];
        if ($p_groupid == 23) {
            $due_rmb = round($commission * 0.1, 2);
        } elseif ($p_groupid == 24) {
            $due_rmb = round($commission * 0.1, 2);
        } else {
            $due_rmb = round($commission * 0.05, 2);
        }
        PddMaidOld::create([
            'father_id' => $app_id,
            'trade_id' => (string)$order_sn,
            'app_id' => $p_pt_id,
            'group_id' => $p_groupid,
            'maid_money' => $due_rmb,
            'type' => 1,
            'real' => 0,
        ]);
    }

    /**
     * 游戏渠道接入接口
     */
    public function index(Request $request)
    {
        $key = '';
        $channel = '';
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'openid' => 'required', //用户唯一id
                'channel' => 'required',//在游戏渠道id（区分用户来源渠道）
                'nick' => 'required', //玩家昵称（URLENcoder（所有涉及这个的都是UTF-8）转码过的）
//                'avatar' => 'required', // 玩家头像链接地址（URLENcoder转码过的）
                'sex' => 'required', // 玩家性别1男，2女,其他未知
//                'Phone' => 'required', // 玩家手机号（可选）
                'time' => 'required',// 当前时间，服务器时间戳（秒做为单位）
                'gid' => 'required' // 游戏渠道授权单个游戏的要进入单个游戏带游戏id
            ];
//            $validator = Validator::make($arrRequest, $rules);
//
//            if ($validator->fails()) {
//                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
//            }
            $openid = @$arrRequest['openid'];
            $channel = @$arrRequest['channel'];
            $nick = urldecode(@$arrRequest['nick']);
            $avatar = urldecode(@$arrRequest['avatar']);
            $sex = @$arrRequest['sex'];
            $phone = @$arrRequest['Phone'];
            $time = @$arrRequest['time'];
            $gid = @$arrRequest['gid'];
            $sign_str =
                (empty($channel) ? '' : '&channel=' . $channel) .
                (empty($openid) ? '' : '&openid=' . $openid) .
                (empty($nick) ? '' : '&nick=' . $nick) .
                (empty($avatar) ? '' : '&avatar=' . $avatar) .
                (empty($sex) ? '' : '&sex=' . $sex) .
                (empty($time) ? '' : '&time=' . $time) .
                (empty($phone) ? '' : '&phone=' . $phone) .
                (empty($gid) ? '' : '&gid=' . $gid) . $key;
            $sign_str = substr($sign_str, 1, strlen($sign_str) - 1);
//            dd($sign_str);
//            dd(md5($sign_str));
            return $this->getResponse($sign_str . '&sign=' . md5($sign_str));//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    public function cloudPay(Request $request, CloudPay $cloudPay)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $orderNo = @$arrRequest['orderNo'];
            $money = @$arrRequest['money'];
            $subject = @$arrRequest['subject'];
            $time = date('YmdHis');
            $result = $cloudPay->pay($orderNo, $money, $time, $subject);
            return $this->getResponse($result['fn']);//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

//    public function query(Request $request, CloudPay $cloudPay)
//    {
//        try {//仅用于测试兼容旧版-start
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }//仅用于测试兼容旧版-----------------线上可删除
//            $arrRequest = json_decode($request->data, true);
//            $orderNo = @$arrRequest['orderNo'];
//            $time = @$arrRequest['time'];
//            $result = $cloudPay->singleQuery($orderNo, $time);
//            return $this->getResponse($result);//正常返回数据
//        } catch (\Throwable $e) {
//            //判断是否正常抛出异常
//            if (!empty($e->getMessage())) {
//                throw new ApiException($e->getMessage(), $e->getCode());
//            }
//            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
//        }
//    }

    public function notify(Request $request, CloudPay $cloudPay)
    {
        $flag = false;
        $post = $request->post();
        if (isset($post['signature'])) {
            //验签
            $res = AcpService::validate($post);
            $orderId = $post['orderId'];
            $respCode = $post['respCode'];
            //涉及到资金交易，需查询订单情况，这一点和支付宝、微信不同
            $flag = $cloudPay->singleQuery($orderId);
        }
        if ($flag) {

        }

    }


    public function dbkSearch(Request $request, BigWashUser $bigWashUser)
    {
        $bigWashUser->dtkSearch([
            'pageId' => 1,
            'keyWords' => "家具",
            'commissionRateLowerLimit' => 90
        ]);
//        $res = $bigWashUser->superSearch([
//            'pageId' => 1,
//            'type' => 1,
//            'keyWords' => "女装",
//        ]);

    }

    // swift pass pay
    public function notify2(Request $request, SwiftPassPay $swiftPassPay)
    {
        return $swiftPassPay->callback();
    }


    public function UCCategory(Request $request, UCNewsService $newsService)
    {
//        halt($request->fullUrlIs());
//        $newsService->log([1,2,3]);
        dd(1);
        try {
            $param = [
                'app' => 'grapebrowser-iflow', #注册时的app名称 必填
                'dn' => '1499531', #第三方用户的唯一标示，可以是第三方客户端设备的唯一标示 必填
                'fr' => 'android', #第三方客户端平台：iphone, android 必须全部小写 必填
                've' => '4.9.5', #第三方客户端版本，建议x.x.x.x格式 必填
                'imei' => '869435037204292', #手机的国际移动设备标识，是由15位数字组成的“电子串号”。IMEI对用户画像非常重要，客户端必须提供真实的IMEI信息由于ios获取不到imei，用idfa替代 必填
                'oaid' => 'A000009B456911', #匿名设备标识符，oaid对用户画像非常重要，可以获取到的设备建议提供。 否
                'nt' => '99', #网络类型，1:运营商,2:wifi,其它默认99 必填
                'client_ip' => '192.168.0.180', #当前请求是服务器为客户端透传时,必传,代表客户端请求来源ip。客户端直连时不需要传 必填
//            'utdid' => '' #阿里设备标识，只有在接入阿里的utdid sdk后才能得到utdid，才需要添加此参数 否
            ];
            $res = json_decode($newsService->getChannels($param));
            $response = [];
            if (@$res['status'] == 0) {
                $data = [];
                $response = [
                    'code' => 200,
                    'msg' => '请求成功',
                    'data' => $data
                ];
            } else {
                $response = [
                    'code' => @$res['status'],
                    'msg' => @$res['message'],
                    'data' => @$res['data']
                ];
            }
            return response()->json($response);
        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }
    }

    public function UCCategory2(Request $request, UCNewsService $newsService)
    {
//        halt($request->fullUrlIs());
        try {
            $param = [
                'app' => 'grapebrowser-iflow', #注册时的app名称 必填
                'dn' => '1499531', #第三方用户的唯一标示，可以是第三方客户端设备的唯一标示 必填
                'fr' => 'android', #第三方客户端平台：iphone, android 必须全部小写 必填
                've' => '4.9.5', #第三方客户端版本，建议x.x.x.x格式 必填
                'imei' => '869435037204292', #手机的国际移动设备标识，是由15位数字组成的“电子串号”。IMEI对用户画像非常重要，客户端必须提供真实的IMEI信息由于ios获取不到imei，用idfa替代 必填
                'oaid' => 'A000009B456911', #匿名设备标识符，oaid对用户画像非常重要，可以获取到的设备建议提供。 否
                'nt' => '99', #网络类型，1:运营商,2:wifi,其它默认99 必填
                'client_ip' => '192.168.0.180', #当前请求是服务器为客户端透传时,必传,代表客户端请求来源ip。客户端直连时不需要传 必填
                'cid' => '472933935',
//            'utdid' => '' #阿里设备标识，只有在接入阿里的utdid sdk后才能得到utdid，才需要添加此参数 否
            ];
            $res = json_decode($newsService->getChannelDetails($param), true);
            $response = [];
            if (@$res['status'] == 0) {
                $articles = [];
                foreach (@$res['data']['items'] as $key => $item) {
                    $articles[$key] = @$res['data'][$item['map']][$item['id']];
                }
                $response = [
                    'code' => 200,
                    'msg' => '请求成功',
                    'data' => [
                        'articles' => $articles,
                        'banners' => @$res['data']['banners'],
                        'specials' => @$res['data']['specials']
                    ]
                ];
            } else {
                $response = [
                    'code' => @$res['status'],
                    'msg' => @$res['message'],
                    'data' => @$res['data']
                ];
            }
            return response()->json($response);
        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }

    }

    public function UCTokenUpdate(Request $request, UCNewsService $newsService)
    {
        $params = Input::all();
        if (!empty($params['access_token'])) {
            Cache::put($this->accessKey, $params['access_token'], 60 * 24 * 6); // 设置token的缓存时间6天，官方文档说毛token有效期为7天
            return 'success';
        } else {
            return 'false';
        }
    }

    public function UPushTest(Request $request)
    {
//        * @param $title String 提示内容
//        * @param $state String 1.公告  2.工作提醒  3.共享提醒
//        * @param string $inform_sign 标记 0忽略 1原生 2网页
//        * @param string $inform_url 1=公告 2工单 3新增粉丝 http=网页
//        * @param string $inform_data {key，value}
        $pushService = new UPushService(0);
        $pushServiceIos = new UPushService(1);
        $date = date('Y-m-d H:m:s');
        $title = '测试 ' . $date;
        $state = 1;
        $sign = 2;
        $url = 'http://www.baidu.com'; // 'http://www.baidu.com'
        $data = ''; // {"work_order_id":"100788"}
        $app_id = '7134356'; // 7134356  13959199791  12345678  ANDROID 6080694   ios 3675700 3690596
//        $pushService->sendAndroidBroadcast($title, $state, $sign, $url, $data);
//        $pushServiceIos->sendIOSBroadcast($title, $state, $sign, $url, $data);
        $pushService->sendAndroidCustomizedcast('' . $title, $app_id, $state, $sign, $url, $data);
//        $pushServiceIos->sendIOSCustomizedcast($title, $app_id, $state, $sign, $url, $data);
    }

    public function test3(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'Ticket' => 'required', //验证码返回给用户的票据
                'UserIp' => 'required',//用户操作来源的外网 IP
                'Randstr' => 'required', //验证票据需要的随机字符串
                'app_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            $service = new CaptchaService();
            $res = $service->validateCaptcha($arrRequest);
            if ($res->CaptchaCode == 1) {
                return $this->getResponse('验证成功！');
            } else {
                return $this->getInfoResponse($res->CaptchaCode, $res->CaptchaMsg);
            }

        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }

    }
}
