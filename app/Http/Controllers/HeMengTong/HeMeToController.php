<?php

namespace App\Http\Controllers\HeMengTong;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleCommonNotify;
use App\Entitys\App\CircleUserAdd;
use App\Entitys\App\CoinShopOrders;
use App\Entitys\App\FuluOrder;
use App\Entitys\App\MedicalHospitalErrorOrders;
use App\Entitys\App\MedicalHospitalTestOrders;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\WxAssistantOrder;
use App\Entitys\App\WxAssistantUser;
use App\Exceptions\ApiException;
use App\Services\Circle\Add;
use App\Services\Circle\BecomeHost;
use App\Services\Circle\LuckyMoney;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\CoinPlate\Orders;
use App\Services\Common\DingAlerts;
use App\Services\FuLu\FuLuServices;
use App\Services\HeMengTong\HeMeToServices;
use App\Services\Itaoke\WechatServices;
use App\Services\Other\CircleCommissionService;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\Shop\Order;
use App\Services\ZhongKang\ZhongKangServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HeMeToController extends Controller
{
//    protected $fkey = 'Z1IUbWoXFcN50aWoZOF7iLYk9MP1n3ke';        #测试 签名秘钥
//    protected $fkey = 'S62pBNLc5TBSog36LUQg8ZVoYL6Nc2E7';        #测试 升级 签名秘钥
    protected $fkey = 'HYxC6qyMqEZDM0h38QopjAdiiOq4gLc9';        #线上 签名秘钥
    protected $wxapp_fkey = 'dIZw7aeYpW82hVSkNeAroV98BaODqgrK';  #小程序 签名秘钥

    /*
     * 禾盟通订单退款
     */
    public function orderRefund(Request $request, HeMeToServices $heMeToServices, ShopOrders $shopOrders)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'order_id' => 'required',
                'amount' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $order_id = $arrRequest['order_id'];
            $amount = $arrRequest['amount'];

            //根据订单号得到唯一 开发者订单号
            $sub_sign = $shopOrders->where('order_id', $order_id)->value('sub_sign');
            $this->log('退款单号' . $sub_sign, 'refund');
            $data = $heMeToServices->orderRefund($sub_sign, $amount);
            $this->log($data, 'refund');
            $arr_data = json_decode($data, true);

            if (@$arr_data["fcode"] != 10000) {
                return $this->getInfoResponse('1001', @$arr_data['fmsg']);//错误返回数据
            }
            return $this->getResponse(@$arr_data['fmsg']);//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            $this->log($e->getMessage(), 'refund');
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 禾盟通支付回调
     */
    function heMeToPayCallBack(Request $request, RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, ShopOrders $shopOrders, Order $order_model)
    {
        //接受回调数据
        $str_post_data = $request->getContent();
        $post_data = json_decode($str_post_data, true);
        $this->log($str_post_data, 'pay');
        try {
            //更新订单对应的uid 商户号 支付时间
            $shopOrders->where('order_id', $post_data['fadd'])->update(['sub_sign' => $post_data['fapino'], 'pay_time' => time(), 'fno' => @$post_data['fno']]);

            if (@$post_data['fcode'] != 10000) {
                $this->log('访问出错', 'pay');
                return 'success';
            }

            //验签
            $data = [
                "fapino" => @$post_data["fapino"],
                "fmoney" => @$post_data["fmoney"],
            ];

            //升级新增
            if (!empty(@$post_data['fno'])) {
                $data['fno'] = $post_data['fno'];
            }

            //判定是app支付宝微信 还是小程序下的单
            $is_app = substr(@$post_data["fapino"], 0, 1);
            if ($is_app == 'a') {//支付宝
                $fkey = $this->fkey;//appkey
                $from = 0;//客户端还是小程序
                $type = 0;//是否走支付宝逻辑
            } elseif ($is_app == 'w') {//微信
                $fkey = $this->fkey;
                $from = 0;
                $type = 1;
            } elseif ($is_app == 'm') {//小程序
                $fkey = $this->wxapp_fkey;
                $from = 1;
                $type = 1;
            } else {
                $this->log('订单未知异常', 'pay');
                return 'success';
            }

            $sign = urldecode(http_build_query($data) . '&fkey=' . $fkey);
            $sign = strtolower(md5($sign));
            if (@$post_data['fsign'] != $sign) {
                $this->log('签名错误', 'pay');
                return 'success';
            }

            //验证通过开始处理
            $this->log('验证成功开始执行！订单号:' . $post_data['fadd'] . ',uniqid:' . $post_data['fapino'], 'pay');

            // 防止重复订单支付成功更新状态
            if (Cache::has('he_me_to_pay_call_back_' . $post_data['fadd'])) {
                return 'success';
            }
            Cache::put('he_me_to_pay_call_back_' . $post_data['fadd'], 1, 10);

            //商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            $out_trade_no = $post_data['fadd'];
            $order = $rechargeOrder->getOrdersById($out_trade_no);

            // 第二种订单情况，如果存在则进入商品回调
            $shop_order = $shopOrders->getByOrderId($out_trade_no);

            if (!empty($shop_order)) {
                if ($shop_order->app_id == 1569840) {
                    $shop_order->real_price = 0.01;
                }
                $computer_price = $shop_order->real_price * 100;
                $this->log('处理订单开始！', 'pay');
                if ($type) {//小程序
                    $res_maid = $order_model->processOrderV1($shop_order->order_id, $from);
                } else {
                    if ($shop_order->status == 0) {
                        $this->log('run', 'pay');
                        $res_maid = $order_model->processOrderV1($shop_order->order_id, $from);
                    }
                }
                $this->log('处理订单结束！', 'pay');

                //爆款支付成功完成任务
                if (!config('test_appid.debug') || in_array($shop_order->app_id, config('test_appid.app_ids'))) {
                    try {
                        $coinCommonService = new CoinCommonService($shop_order->app_id);
                        $task_id = 4;#爆款商城首购
                        $task_time = time();
                        $coinCommonService->successTask($task_id, $task_time);

                        //邀请的新人完成爆款首购完成任务
                        //得到上级
                        $appUserInfo = new AppUserInfo();
                        $user_info = $appUserInfo->where('id', $shop_order->app_id)->first(['parent_id', 'create_time']);
                        $new_user_time = strtotime('2020-07-01');//定义新人注册的时间
                        if ($user_info && $user_info->create_time >= $new_user_time) {//有上级 且为新人 且为首购 上级可完成任务
                            $coinCommonService = new CoinCommonService($user_info->parent_id);
                            $task_id = 9;#邀好友完成爆款首购
                            $task_time = time();
                            $coinCommonService->successTask($task_id, $task_time);
                        }
                    } catch (\Exception $e) {

                    }
                }
            }

            if (!empty($order)) {
                if ($order->uid == 1499531) {
                    $order->price = 0.01;
                }

                $arr = [
                    'uid' => $order->uid,
                    'money' => $order->price,
                    'orderid' => $out_trade_no,
                ];

                if ($shop_order) { //小程序
                    $arr = [
                        'uid' => $order->uid,
                        'money' => 800,
                        'orderid' => $out_trade_no,
                    ];
                }
                if ($type) {
                    // 其它业务逻辑情况
                    $rechargeUserLevel->initOrder($arr);
                    $rechargeUserLevel->updateExt();                 #升级
                    $rechargeUserLevel->returnCommission();          #返佣
                    $rechargeUserLevel->handleArticle();             #更新文章
                    $rechargeOrder->updateOrderStatus($out_trade_no);#更新订单
                } else {
                    if ($order->status == 1) {
                        // 5、其它业务逻辑情况
                        $rechargeUserLevel->initOrder($arr);
                        $rechargeUserLevel->updateExt(); //升级

                        if (@$post_data["fmoney"] != 10) {
                            $rechargeUserLevel->returnCommission(); //返佣
                        } else {
                            $rechargeUserLevel->returnCommissionV12(); //返佣
                        }
                        $rechargeUserLevel->handleArticle(); //更新文章
                        $rechargeOrder->updateOrderStatus($out_trade_no);//更新订单
                    }
                }
            }
            return 'success';
        } catch (\Throwable $e) {
            $this->log($e->getMessage(), 'pay');
            return "success";
        }
    }

    /*
     * 禾盟通圈子发红包支付回调
     */
    function heMeToCircleSendCallBack(Request $request, LuckyMoney $luckyMoney)
    {
        //接受回调数据
        $str_post_data = $request->getContent();
        $post_data = json_decode($str_post_data, true);
        $this->log($str_post_data, 'circle_send');
        try {
            if (@$post_data['fcode'] != 10000) {
                $this->log('访问出错', 'circle_send');
                return 'success';
            }

            //验签
            $data = [
                "fapino" => @$post_data["fapino"],
                "fmoney" => @$post_data["fmoney"],
            ];

            //升级新增
            if (!empty(@$post_data['fno'])) {
                $data['fno'] = $post_data['fno'];
            }

            $sign = urldecode(http_build_query($data) . '&fkey=' . $this->fkey);
            $sign = strtolower(md5($sign));
            if (@$post_data['fsign'] != $sign) {
                $this->log('签名错误', 'circle_send');
                return 'success';
            }

            //验证通过开始处理
            $this->log('验证成功开始执行！订单号:' . $post_data['fadd'] . ',uniqid:' . $post_data['fapino'], 'circle_send');

            /*****************************************/
            $order_id = $post_data['fadd'];
            $actual = $post_data["fmoney"];
            $order_info = $luckyMoney->getInfoByOrderId($order_id);

            if (empty($order_info)) {
                $this->log('不存在该订单：' . $order_id, 'circle_send');
                $this->log('---------------end_error----------', 'circle_send');
                return 'success';
            }
            if ($order_info->price != $actual) {
                $this->log('该用户实际支付金额有误：实付' . $actual . '元', 'circle_send');
                $this->log('---------------end_error----------', 'circle_send');
            }
            if ($order_info->status == 1) {
                $this->log('该笔订单已经支付过：' . $order_id, 'circle_send');
                $this->log('---------------end_error----------', 'circle_send');
                return 'success';
            }
            $this->log('开始处理发红包，将订单处理成支付状态', 'circle_send');
            $luckyMoney->updateRed($order_id);

            $red_id = $order_info->id;
            $circle_id = $order_info->circle_id;
            $app_id = $order_info->app_id;
            $red_price = $order_info->price;
            $remain_price = $order_info->remain_price;
            $circle_info = $luckyMoney->getCircleInfo($circle_id);
            $king_info = $luckyMoney->getKingInfo($circle_info->king_id);

            $count_times = 1;
            $ring_host_ptb = round($red_price * 0.1 * 10);

            if (!empty($circle_info->app_id)) {
                $this->log('开始添加红包记录，并给圈主分葡萄币', 'circle_send');
                $red_user_info = AppUserInfo::find($circle_info->app_id);
                $red_time_value['red_id'] = $red_id;
                $red_time_value['from_app_id'] = $app_id;
                $red_time_value['to_app_id'] = $circle_info->app_id;
                $red_time_value['to_app_username'] = $red_user_info->user_name;
                $red_time_value['type'] = 2;
                $red_time_value['to_app_img'] = $red_user_info->avatar;
                $red_time_value['have'] = $ring_host_ptb;
                $red_time_value['time'] = $count_times;

                $luckyMoney->getRed($red_time_value);

                $this->log('添加圈主分佣记录', 'circle_send');
                $red_time_log = [
                    'app_id' => $circle_info->app_id,
                    'from_user_name' => $red_user_info->real_name,
                    'from_user_phone' => $red_user_info->phone,
                    'from_user_img' => $red_user_info->avatar,
                    'from_circle_name' => $circle_info->ico_title,
                    'from_circle_img' => $circle_info->ico_img,
                    'order_id' => $red_id,
                    'order_money' => $red_price * 10,
                    'money' => $ring_host_ptb,
                    'type' => 3,
                ];
                $luckyMoney->addRedLog($red_time_log);
                $count_times += 1;
            }

            if (!empty($king_info->app_id)) {
                $red_user_info = AppUserInfo::find($king_info->app_id);

                $red_time_value['red_id'] = $red_id;
                $red_time_value['from_app_id'] = $app_id;
                $red_time_value['to_app_id'] = $king_info->app_id;
                $red_time_value['to_app_username'] = $red_user_info->user_name;
                $red_time_value['type'] = 3;
                $red_time_value['to_app_img'] = $red_user_info->avatar;
                $red_time_value['have'] = $ring_host_ptb * 0.1;
                $red_time_value['time'] = $count_times;

                $luckyMoney->getRed($red_time_value);
                $red_time_log = [
                    'app_id' => $king_info->app_id,
                    'from_user_name' => $red_user_info->real_name,
                    'from_user_phone' => $red_user_info->phone,
                    'from_user_img' => $red_user_info->avatar,
                    'from_circle_name' => $circle_info->ico_title,
                    'from_circle_img' => $circle_info->ico_img,
                    'order_id' => $red_id,
                    'order_money' => $red_price * 10,
                    'money' => $ring_host_ptb * 0.1,
                    'type' => 3,
                ];
                $luckyMoney->addRedLog($red_time_log);
            }

            return 'success';
        } catch (\Throwable $e) {
            $this->log($e->getMessage(), 'circle_send');
            return "success";
        }
    }

    /*
     * 禾盟通圈子购买支付回调
     */
    function heMeToCircleBuyCallBack(Request $request, BecomeHost $host)
    {
        //接受回调数据
        $str_post_data = $request->getContent();
        $post_data = json_decode($str_post_data, true);
        $this->log($str_post_data, 'circle_buy');
        try {
            if (@$post_data['fcode'] != 10000) {
                $this->log('访问出错', 'circle_buy');
                return 'success';
            }

            //验签
            $data = [
                "fapino" => @$post_data["fapino"],
                "fmoney" => @$post_data["fmoney"],
            ];

            //升级新增
            if (!empty(@$post_data['fno'])) {
                $data['fno'] = $post_data['fno'];
            }

            $sign = urldecode(http_build_query($data) . '&fkey=' . $this->fkey);
            $sign = strtolower(md5($sign));
            if (@$post_data['fsign'] != $sign) {
                $this->log('签名错误', 'circle_buy');
                return 'success';
            }

            //验证通过开始处理
            $str_fadd = hex2bin($post_data['fadd']);
            $arr_fadd = explode('---', $str_fadd);

            $this->log('验证成功开始执行！订单号:' . $arr_fadd[0] . ',uniqid:' . $post_data['fapino'], 'circle_buy');

            /*****************************************/
            $order_id = $arr_fadd[0];
            $actual = $post_data["fmoney"];
            $area = $arr_fadd[1];

            $order_info = $host->getInfoByOrderId($order_id);
            $this->log('处理订单：' . $order_id, 'circle_buy');
            if (empty($order_info)) {
                $this->log('不存在该订单：' . $order_id, 'circle_buy');
                $this->log('---------------end_error----------', 'circle_buy');
                return 'success';
            }
            if ($order_info->status == 1) {
                $this->log('该笔订单已经支付过：' . $order_id, 'circle_buy');
                $this->log('---------------end_error----------', 'circle_buy');
                return 'success';
            }

            /*
             * 校验当前金额是否低于圈子现价，
             * 如果支付金额低于圈子当前价格，则判定圈子已经被被人捷足先登
             * 记录日志，返还该用户充值金额
             */

            $circle_id = $order_info->circle_id;
            $circle_info = $host->getCircleInfo($circle_id);

            if (!in_array($order_info->app_id, [1694511, 3675700, 9873668, 8343202])) {
                if ($circle_info->price != $actual) {
                    $tmp_msg = "【订单失效】订单id：{$order_id}，用户交易金额：{$actual}，圈子现价：{$circle_info->price}。";

                    Storage::disk('local')->append('callback_document/circle_alipay_notify_overdue.txt', var_export($tmp_msg, true));

                    return 'success';
                }
            }

            $circle_price = config('ring.price');
            $log_app_id = 0;
            if ($actual > $circle_price) {
                $this->log('竞价', 'circle_buy');
                $circle_id = $order_info->circle_id;
                $circle_info = $host->getCircleInfo($circle_id);
                $quondam_app_id = $circle_info->app_id;
                $app_id = $order_info->app_id;
                $log_app_id = $app_id;
                $circle_params['app_id'] = $app_id;
                $circle_params['price'] = $actual * 1.2;
                if ($host->isLock($circle_id)) {
                    $circle_params['close'] = 1;
                }
                if (!empty($area)) {
                    $circle_params['area'] = $area;
                }

                $host->updateCircle($order_id, $circle_id, $circle_params);
                $this->log('创建者加入圈子：' . $order_info->app_id . '，圈子id：' . $circle_id, 'circle_buy');
                $host->addCircle($order_info->app_id, $circle_id);
                $this->log('原圈主降级：' . $quondam_app_id . '，圈子id：' . $circle_id, 'circle_buy');
                $host->demotion($quondam_app_id, $circle_id);
                $return_ptb = round($actual * 10 * config('ring.return_money'));

                $host->addPtb($quondam_app_id, $return_ptb);
                $host->addBoundsLog($quondam_app_id, $app_id, $order_id, $return_ptb);

                $re_obj_user = AppUserInfo::find($app_id);
                $obj_notify = new CircleCommonNotify();
                $n_data = [];
                $n_data['app_id'] = $quondam_app_id;
                $n_data['ico'] = 'http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/logo/logo.png';
                $n_data['username'] = '系统通知';
                if (empty($re_obj_user->user_name)) {
                    $re_obj_user->user_name = 'ID：' . $re_obj_user->id;
                }
                $n_data['notify'] = "{$re_obj_user->user_name} 花费 " . ($actual * 10) . "葡萄币，抢购了您的“{$circle_info->ico_title}”圈子！";
                $n_data['to_id'] = $circle_id;
                $n_data['type'] = 3;
                $obj_notify->addNotify($n_data);

                //剥离多级分
                $obj_circle_commission_service = new CircleCommissionService();
                $host->bidBonus($order_id);//团队会员竞价圈子获得津贴支付宝回调 直属分
                $obj_circle_commission_service->biddingCircleCommission($order_id);//团队会员竞价圈子获得津贴支付宝回调 第三方分


            } else {
                $circle_id = $order_info->circle_id;
                $circle_info = $host->getCircleInfo($circle_id);
                $this->log('低价认购', 'circle_buy');
                $circle_value['app_id'] = $order_info->app_id;
                $log_app_id = $order_info->app_id;
                $circle_value['price'] = $actual * 1.2;
                if (!empty($area)) {
                    $circle_value['area'] = $area;
                }
                $host->updateCircleNotNumber($order_id, $circle_id, $circle_value);
                $this->log('开始分佣订单：' . $order_id, 'circle_buy');

                //剥离多级分
                $obj_circle_commission_service = new CircleCommissionService();
                $host->newBonus($order_id);//团队会员购买圈子津贴支付宝回调 直属分
                $obj_circle_commission_service->buyCircleCommission($order_id);//团队会员购买圈子津贴支付宝回调 第三方分

                $king_id = $circle_info->king_id;
                $host->kingBonus($king_id, $order_id);
                $this->log('创建者加入圈子：' . $order_info->app_id . '，圈子id：' . $circle_id, 'circle_buy');
                $host->addCircle($order_info->app_id, $circle_id, $area);
            }

            $ad_user_info = AdUserInfo::where(['pt_id' => $log_app_id])->first();
            $obj_log = new UserCreditLog();
            $obj_log->addLog($ad_user_info->uid, "APG", ['extcredits1' => $actual]);
            return 'success';
        } catch (\Throwable $e) {
            $this->log($e->getMessage(), 'circle_buy');
            return "success";
        }
    }

    /*
     * 禾盟通圈子加入支付回调
     */
    function heMeToCircleJoinCallBack(Request $request, Add $add, CircleUserAdd $circleUserAdd)
    {
        //接受回调数据
        $str_post_data = $request->getContent();
        $post_data = json_decode($str_post_data, true);
        $this->log($str_post_data, 'circle_join');
        try {
            if (@$post_data['fcode'] != 10000) {
                $this->log('访问出错', 'circle_join');
                return 'success';
            }

            //验签
            $data = [
                "fapino" => @$post_data["fapino"],
                "fmoney" => @$post_data["fmoney"],
            ];

            //升级新增
            if (!empty(@$post_data['fno'])) {
                $data['fno'] = $post_data['fno'];
            }

            $sign = urldecode(http_build_query($data) . '&fkey=' . $this->fkey);
            $sign = strtolower(md5($sign));
            if (@$post_data['fsign'] != $sign) {
                $this->log('签名错误', 'circle_join');
                return 'success';
            }

            //验证通过开始处理
            $this->log('验证成功开始执行！订单号:' . $post_data['fadd'] . ',uniqid:' . $post_data['fapino'], 'circle_join');

            /*****************************************/
            $order_id = $post_data['fadd'];
            $actual = $post_data["fmoney"];

            $order_info = $circleUserAdd->getOrder($order_id);
            if (empty($order_info)) {
                $add->log('不存在该订单：' . $order_id);
                $add->log('---------------end_error----------');
                return 'success';
            }
            if ($order_info->money != $actual) {
                $add->log('该用户实际支付金额有误：实付' . $actual . '元');
                $add->log('---------------end_error----------');
                return 'success';
            }
            if ($order_info->status == 1) {
                $add->log('该笔订单已经支付过：' . $order_id);
                $add->log('---------------end_error----------');
                return 'success';
            }
            $add->overOrder($order_id);
            $add->log('---------------end----------');

            return 'success';
        } catch (\Throwable $e) {
            $this->log($e->getMessage(), 'circle_join');
            return "success";
        }
    }

    /*
     * 禾盟通医疗支付回调
     */
    function heMeToMedicalCallBack(Request $request)
    {
        //接受回调数据
        $str_post_data = $request->getContent();
        $post_data = json_decode($str_post_data, true);
        $this->log($str_post_data, 'medical');
        try {
            if (@$post_data['fcode'] != 10000) {
                $this->log('访问出错', 'medical');
                return 'success';
            }

            //验签
            $data = [
                "fapino" => @$post_data["fapino"],
                "fmoney" => @$post_data["fmoney"],
            ];

            //升级新增
            if (!empty(@$post_data['fno'])) {
                $data['fno'] = $post_data['fno'];
            }

            $sign = urldecode(http_build_query($data) . '&fkey=' . $this->fkey);
            $sign = strtolower(md5($sign));
            if (@$post_data['fsign'] != $sign) {
                $this->log('签名错误', 'medical');
                return 'success';
            }

            //验证通过开始处理
            $this->log('验证成功开始执行！订单号:' . $post_data['fadd'] . ',uniqid:' . $post_data['fapino'], 'medical');

            /*****************************************/
            $order_id = $post_data['fadd'];
            $actual = $post_data["fmoney"];

            $md_medical_order = new MedicalHospitalTestOrders();
            $order_info = $md_medical_order->getUnpaidByOrderId($order_id);

            if (empty($order_info)) {
                $this->log('不存或已处理该订单：' . $order_id, 'medical');
                $this->log('---------------end_error----------', 'medical');
                return "success";
            }
            if ($order_info->use_money != $actual) {
                $this->log('该用户实际支付金额有误：实付' . $actual . '元', 'medical');
                $this->log('---------------end_error----------', 'medical');
                return "success";
            }
            $this->log('开始判断订单类型', 'medical');

            $zhongKangServices = new ZhongKangServices();

            switch ($order_info->type) {
                case 2:
                    $this->log('开始发送远程订单', 'medical');
                    $params = [
                        'zk_unit_id' => $order_info->zk_unit_id,      #中康平台的机构编号
                        'zk_combo_id' => $order_info->zk_combo_id,    #中康平台的套餐编号
                        'zk_combo_name' => $order_info->zk_combo_name,#中康平台的套餐名 可空
                        'out_order_no' => $order_id,  #合作伙伴的唯一订单编号,#中康平台的套餐名 可空
                        'tj_time' => $order_info->in_time,            #用户预约的体检时间，格式如2018-07-01,数据精确到天
                        'tj_name' => $order_info->name,            #用户姓名
                        'mobile' => $order_info->mobile,              #用户手机
                        'quantity' => 1,                  #预订人数，默认1人，一人一单
                        'tj_gender' => $order_info->real_sex,        #用户性别 1-男 2-女
                        'tj_married' => $order_info->is_married,      #用户婚否 1-已婚 2-未婚
                        'tj_age' => $order_info->age,              #用户年龄
                        'tj_ident' => $order_info->id_card,          #用户身份证号
                        'promo_amount' => '',             #优惠金额 可空
                        'promo_amount_desc' => '',        #优惠金额说明 可空
                        'comment' => '',                  #用户备注信息 可空
                    ];

                    $zk_resq = $zhongKangServices->startBook($params);
                    $arr_zk_resq = json_decode($zk_resq, true);
                    if (empty($arr_zk_resq) || @$arr_zk_resq['code'] != 0) {
                        $obj_error_order = new MedicalHospitalErrorOrders();
                        $obj_error_order->addErrorOrder([
                            'app_id' => $order_info->app_id,
                            'pay_type' => $order_info->type,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_zfb' => $order_info->use_money,
                            'pt_order' => $order_id,
                            'tj_time' => $order_info->in_time,
                            'tj_name' => $order_info->name,
                            'tj_ident' => $order_info->id_card,
                            'error_reason' => @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'],
                        ]);
                        $this->log('远程下单订单状态异常：' . @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'], 'medical');
                        $this->log('---------------end_error----------', 'medical');
                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($order_id, $params);
                        return "success";
                    }

                    $params = [
                        'status' => 1,
                        'zk_order_no' => $arr_zk_resq['data']['zk_order_no']
                    ];
                    $this->log('更新订单为成功状态', 'medical');
                    $md_medical_order->upOrder($order_id, $params);
                    break;
                case 4:
                    $this->log('开始发送远程订单', 'medical');
                    $params = [
                        'zk_unit_id' => $order_info->zk_unit_id,      #中康平台的机构编号
                        'zk_combo_id' => $order_info->zk_combo_id,    #中康平台的套餐编号
                        'zk_combo_name' => $order_info->zk_combo_name,#中康平台的套餐名 可空
                        'out_order_no' => $order_id,  #合作伙伴的唯一订单编号,#中康平台的套餐名 可空
                        'tj_time' => $order_info->in_time,            #用户预约的体检时间，格式如2018-07-01,数据精确到天
                        'tj_name' => $order_info->name,            #用户姓名
                        'mobile' => $order_info->mobile,              #用户手机
                        'quantity' => 1,                  #预订人数，默认1人，一人一单
                        'tj_gender' => $order_info->real_sex,        #用户性别 1-男 2-女
                        'tj_married' => $order_info->is_married,      #用户婚否 1-已婚 2-未婚
                        'tj_age' => $order_info->age,              #用户年龄
                        'tj_ident' => $order_info->id_card,          #用户身份证号
                        'promo_amount' => '',             #优惠金额 可空
                        'promo_amount_desc' => '',        #优惠金额说明 可空
                        'comment' => '',                  #用户备注信息 可空
                    ];

                    $zk_resq = $zhongKangServices->startBook($params);
                    $arr_zk_resq = json_decode($zk_resq, true);
                    if (empty($arr_zk_resq) || @$arr_zk_resq['code'] != 0) {
                        $obj_error_order = new MedicalHospitalErrorOrders();
                        $obj_error_order->addErrorOrder([
                            'app_id' => $order_info->app_id,
                            'pay_type' => $order_info->type,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_zfb' => $order_info->use_money,
                            'pt_order' => $order_id,
                            'tj_time' => $order_info->in_time,
                            'tj_name' => $order_info->name,
                            'tj_ident' => $order_info->id_card,
                            'error_reason' => @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'],
                        ]);
                        $this->log('远程下单订单状态异常：' . @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'], 'medical');
                        $this->log('---------------end_error----------', 'medical');
                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($order_id, $params);
                        return "success";
                    }

                    $params = [
                        'status' => 1,
                        'zk_order_no' => $arr_zk_resq['data']['zk_order_no']
                    ];
                    $this->log('更新订单为成功状态', 'medical');
                    $md_medical_order->upOrder($order_id, $params);
                    break;
                default:
                    $this->log('订单类型异常：' . $order_info->type, 'medical');
                    $this->log('---------------end_error----------', 'medical');
                    return "success";
            }
        } catch (\Throwable $e) {
            $this->log('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage(), 'medical');
            $this->log('---------------end_error----------', 'medical');
            return "success";
        }
        $this->log('---------------end----------', 'medical');
        return "success";
    }

    /*
    * 福禄平台商品支付回调
    */
    function fuluPayCallback(Request $request)
    {
        //接受回调数据
        $time = time();
        $str_post_data = $request->getContent();
        $post_data = json_decode($str_post_data, true);
        $dir = 'fulu';
        $this->log($str_post_data, $dir);
        $msg = '';
        try {
            if (@$post_data['fcode'] != 10000) {
                $this->log('访问出错', $dir);
                return 'success';
            }
            //验签
            $data = [
                "fapino" => @$post_data["fapino"],
                "fmoney" => @$post_data["fmoney"],
            ];

            //升级新增
            if (!empty(@$post_data['fno'])) {
                $data['fno'] = $post_data['fno'];
            }

            $sign = urldecode(http_build_query($data) . '&fkey=' . $this->fkey);
            $sign = strtolower(md5($sign));
            if (@$post_data['fsign'] != $sign) {
                $msg = $msg . ' ==> ' . '签名错误';
            } else {
                #todo 验证通过开始处理
                $msg = $msg . ' ==> ' . '成功:' . $post_data['fadd'] . ',uniqid:' . $post_data['fapino'];
                /*****************************************/
                $pay_no = $post_data['fapino'];
                $order_id = $post_data['fadd'];
                $actual = $post_data["fmoney"];
                $fuluOrderModel = new FuluOrder();
                $fuluSerivece = new FuLuServices();
                $hmtSerice = new HeMeToServices();
                $key = 'hmt_fulu_order_pay_success' . $order_id;
                if (Cache::has($key)) {
                    $msg = $msg . ' ==> ' . '重复回调！或重复支付！';
                } else {
                    Cache::put($key, 1, 10);
                    $order = $fuluOrderModel->where(['order_id' => $order_id, 'order_status' => FuluOrder::NO_PAY])->first();
                    if (empty($order)) {
                        $msg = $msg . ' ==> ' . '无效订单';
                    } else {
                        if ($order['real_price'] != $actual) {
                            $msg = $msg . ' ==> ' . '价格验证不通过:' . $actual;
                        } else {
                            // 更新订单支付成功
                            $fuluOrderModel->where(['order_id' => $order_id])->update([
                                'order_status' => FuluOrder::PAY_WAIT,
                                'pay_time' => $time
                            ]);
                            $product_id = $order['product_id'];
                            $buy_num = $order['buy_num'];
                            $order_id = $order['order_id'];
                            $charge_account = $order['charge_account'];
                            $res = null;
                            $msg = $msg . ' ==> ' . '福禄下单:' . $order['product_type'] . $product_id;
                            switch ($order['product_type']) {
                                case '卡密' :
                                    $res = $fuluSerivece->addCardOrder($product_id, $buy_num, $order_id);
                                    break;
                                case '直充' :
                                    $res = $fuluSerivece->addDirectOrder($product_id, $order_id, $charge_account, $buy_num);
                                    break;
                                default;
                            }
                            if (!empty($res)) {
                                $res = json_decode($res, true);
                                if ($res['code'] == 0) {
                                    $msg = ' ==> ' . '下单成功！等待回调结果。 end';
                                } else {
                                    $msg = $msg . ' ==> ' . '下单失败【' . @$res['message'] . '】！开始退款';
                                    if (@$res['message'] == '商户余额不足') {
                                        if (!Cache::has('dding_fulu' . @$res["fcode"])) {
                                            $dingAlerts = new DingAlerts();
                                            $dingAlerts->sendByText('福禄平台下单失败:' . $res['message']);
                                            Cache::put('dding_fulu' . @$res["fcode"], 1, 20);
                                        }
                                    }
                                    $order_status = FuluOrder::PAY_FAIL;
                                    $refund_res = $hmtSerice->orderRefund($order['pay_no'], $order['real_price']);
                                    $refund_res = json_decode($refund_res, true);
//                                    $this->log($refund_res, $dir);
//                                    $this->log($order['pay_no']. '===' . $order['real_price'], $dir);
                                    if (@$refund_res["fcode"] != 10000) {
                                        $order_status = FuluOrder::REFUND_FAIL;
                                        $msg = $msg . ' ==> ' . '退款失败【' . @$refund_res['fmsg'] . '】';
                                    } else {
                                        $order_status = FuluOrder::REFUND_SUCCESS;
                                        $msg = $msg . ' ==> ' . '退款成功';
                                    }
                                    $fuluOrderModel->where(['order_id' => $order_id])->update([
                                        'order_status' => $order_status
                                    ]);
                                    $msg = $msg . ' ==> ' . $order_status . ' end';
                                }
                            }
                        }
                    }
                }

            }
            $this->log($msg, $dir);
            return 'success';
        } catch (\Throwable $e) {
            $this->log($msg, $dir);
            $this->log('异常: ' . $e->getMessage(), $dir);
            return "success";
        }
    }


    /*
     * 禾盟通金币商城支付回调
     */
    function heMeToCoinShopBuyCallBack(Request $request)
    {
        //接受回调数据
        $str_post_data = $request->getContent();
        $post_data = json_decode($str_post_data, true);
        $this->log($str_post_data, 'coin_shop_buy');
        try {
            if (@$post_data['fcode'] != 10000) {
                $this->log('访问出错', 'coin_shop_buy');
                return 'success';
            }

            //验签
            $data = [
                "fapino" => @$post_data["fapino"],
                "fmoney" => @$post_data["fmoney"],
            ];

            //升级新增
            if (!empty(@$post_data['fno'])) {
                $data['fno'] = $post_data['fno'];
            }

            $sign = urldecode(http_build_query($data) . '&fkey=' . $this->fkey);
            $sign = strtolower(md5($sign));
            if (@$post_data['fsign'] != $sign) {
                $this->log('签名错误', 'coin_shop_buy');
                return 'success';
            }

            //验证通过开始处理
            $this->log('验证成功开始执行！订单号:' . $post_data['fadd'] . ',uniqid:' . $post_data['fapino'], 'coin_shop_buy');

            /*****************************************/
            $order_id = $post_data['fadd'];
            $actual = $post_data["fmoney"];

            $coin_shop_orders = new CoinShopOrders();
            $coin_orders = new Orders();
            $order_info = $coin_shop_orders->getOrder($order_id);
            if (empty($order_info)) {
                $this->log('不存在该订单：' . $order_id, 'coin_shop_buy');
                return 'success';
            }
            if (($order_info->real_price + $order_info->fare_price) != $actual) {
                $this->log('该用户实际支付金额有误：实付' . $actual . '元', 'coin_shop_buy');
                return 'success';
            }
            if ($order_info->status == 1) {
                $this->log('该笔订单已经支付过：' . $order_id, 'coin_shop_buy');
                return 'success';
            }
            $coin_orders->handle($order_id);
            $this->log('---------------end----------', 'coin_shop_buy');

            return 'success';
        } catch (\Throwable $e) {
            $this->log($e->getMessage(), 'coin_shop_buy');
            return "success";
        }
    }


    /*
    * 智能助手平台商品支付回调
    */
    function robotPayCallback(Request $request)
    {
        //接受回调数据
        $time = time();
        $str_post_data = $request->getContent();
        $post_data = json_decode($str_post_data, true);
        $dir = 'wxRobot';
        $this->log($str_post_data, $dir);
        $msg = '';
        try {
            if (@$post_data['fcode'] != 10000) {
                $this->log('访问出错', $dir);
                return 'success';
            }
            //验签
            $data = [
                "fapino" => @$post_data["fapino"],
                "fmoney" => @$post_data["fmoney"],
            ];

            //升级新增
            if (!empty(@$post_data['fno'])) {
                $data['fno'] = $post_data['fno'];
            }

            $sign = urldecode(http_build_query($data) . '&fkey=' . $this->fkey);
            $sign = strtolower(md5($sign));
            if (@$post_data['fsign'] != $sign) {
                $msg = $msg . ' ==> ' . '签名错误';
            } else {
                #todo 验证通过开始处理
                $msg = $msg . '验签成功 ==> ' . '成功:' . $post_data['fadd'] . ',uniqid:' . $post_data['fapino'];
                /*****************************************/
                $pay_no = $post_data['fapino'];
                $order_id = $post_data['fadd'];
                $actual = $post_data["fmoney"];
                $fuluOrderModel = new WxAssistantOrder();
                $robotUserModel = new WxAssistantUser();
                $hmtSerice = new HeMeToServices();

                $key = 'hmt_robot_order_pay_success' . $order_id;
                if (Cache::has($key)) {
                    $msg = $msg . ' ==> ' . '重复回调！或重复支付！';
                } else {
                    Cache::put($key, 1, 10);
                    $order = $fuluOrderModel->where(['order_id' => $order_id, 'order_status' => WxAssistantOrder::NO_PAY])->first();
                    if (empty($order)) {
                        $msg = $msg . ' ==> ' . '无效订单';
                    } else {
                        if ($order['real_price'] != $actual) {
                            $msg = $msg . ' ==> ' . '价格验证不通过:' . $actual;
                        } else {
                            // 更新订单支付成功
                            $fuluOrderModel->where(['order_id' => $order_id])->update([
                                'order_status' => WxAssistantOrder::PAY_WAIT,
                                'pay_time' => $time
                            ]);
                            $order_id = $order['order_id'];
                            $res = null;
                            $msg = $msg . ' ==> ' . '开始创建机器人';
                            #todo 开始执行机器人下单相关操作
                            /**
                             ** {
                             * "id": 9221, //机器人id
                             * "uid": 1736,
                             * "wechatrobot": "fc7d8f0c-e9ca-37b1-99aa-2aeeb32ea3d3", //创建生成的微信号，用做标识
                             * "wx_id": "",
                             * "amount_used": 0,
                             * "group_num": 20,  //发单群数量上限
                             * "passwd": "",
                             * "nickname": "",
                             * "c_uid": 1736,
                             * "login_status": 0,
                             * "end_time": 1621582764, //机器人过期时间
                             * "remark": null,
                             * "wc_id": "",
                             * "agent_uid": null,
                             * "is_enabled": 0,
                             * "robot_type": 1, //机器人类型
                             * "ip": "http://106.52.59.134:28081/"
                             * }
                             */
                            $app_id = $order['app_id'];
                            $user = $robotUserModel->where(['app_id' => $app_id])->first();
                            $res = false;
                            $update_body = [];
                            $user_flag = 2;
                            if ($user['user_flag'] >= 2) {
                                if ($user['expiry_time'] < time() && $update_body['user_flag'] == 2) {
                                    $user_flag = 3;
                                }
                                $robotService = new WechatServices($user['robot_id']);
                                $res = $robotService->robotChange($order['month']);
                            } else {
                                $user_flag = 3;
                                $robotService = new WechatServices();
                                $res = $robotService->createRobot($order['month']);
                            }
                            #todo 结束
                            if ($res != false) {
                                $msg = $msg . json_encode($res);
                                $msg = $msg . ' ==> ' . '创建成功！';
                                $robot_id = $res['id'];
                                $wechatrobot = $res['wechatrobot'];
                                $end_time = $res['end_time'];
                                $update_body = [
                                    'user_flag' => $user_flag,
                                    'app_id' => $app_id,
                                    'robot_id' => $robot_id,
                                    'expiry_time' => $end_time,
                                ];
                                try {
                                    DB::connection('app38')->beginTransaction();
                                    $robotUserModel->where(['app_id' => $app_id])->update($update_body);
                                    $msg = ' ==> ' . '用户信息更新成功！';
                                    $fuluOrderModel->where(['order_id' => $order_id])->update([
                                        'order_status' => WxAssistantOrder::PAY_SUCCESS
                                    ]);
                                    DB::connection('app38')->commit();
                                    $msg = ' ==> ' . '订单更新成功！' . WxAssistantOrder::PAY_SUCCESS;
                                } catch (\Exception $exception) {
                                    DB::connection('app38')->rollBack();
                                    throw $exception;
                                }

                            } else {
                                $msg = $msg . ' ==> ' . '创建失败！开始退款';
                                $order_status = WxAssistantOrder::PAY_FAIL;
                                $refund_res = $hmtSerice->orderRefund($order['pay_no'], $order['real_price']);
                                $refund_res = json_decode($refund_res, true);
//                                    $this->log($refund_res, $dir);
//                                    $this->log($order['pay_no']. '===' . $order['real_price'], $dir);
                                if (@$refund_res["fcode"] != 10000) {
                                    $order_status = WxAssistantOrder::REFUND_FAIL;
                                    $msg = $msg . ' ==> ' . '退款失败【' . @$refund_res['fmsg'] . '】';
                                } else {
                                    $order_status = WxAssistantOrder::REFUND_SUCCESS;
                                    $msg = $msg . ' ==> ' . '退款成功';
                                }
                                $fuluOrderModel->where(['order_id' => $order_id])->update([
                                    'order_status' => $order_status
                                ]);
                                $msg = $msg . ' ==> ' . $order_status . ' end';
                            }
                        }
                    }
                }

            }
            $this->log($msg, $dir);
            return 'success';
        } catch (\Throwable $e) {
            $this->log($msg, $dir);
            $this->log('异常: ' . $e->getMessage(), $dir);
            return "success";
        }
    }

    /*
     * 记录日志
     */
    private function log($msg, $name)
    {
        Storage::disk('local')->append('callback_document/hemeto/' . $name . '/' . date('Ymd') . '.txt', date('Y-m-d H:i:s') . '  ' . var_export($msg, true));
    }
}
