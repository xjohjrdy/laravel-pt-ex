<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\CommissionLog;
use App\Entitys\Ad\ExchangeGrapeOrder;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Ad\VoipMoneyOrderMaid;
use App\Entitys\App\ApplyCash;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\CircleMaid;
use App\Entitys\App\JdGetOneShow;
use App\Entitys\App\JdMaidOld;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\PretendShopOrdersMaid;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\App\TodayMoneyChange;
use App\Entitys\App\UserHigh;
use App\Entitys\App\UserOrderTao;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Services\Recharge\RechargeUserLevel;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Services\Shop\Order;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WuHangTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:WuHangTest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试专用';


    protected $todayMoneyChange;
    protected $userAccount;
    protected $bonusLog;
    protected $userOrderTao;
    protected $voipMoneyOrderMaid;
    protected $pretendShopOrdersMaid;
    protected $rechargeCreditLog;
    protected $circleMaid;
    protected $applyCash;
    protected $exchangeGrapeOrder;
    protected $appUserInfo;
    protected $rechargeUserLevel;
    protected $rechargeOrder;
    protected $order_model;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order_model, UserAboutLog $aboutLog, AdUserInfo $adUserInfo, Client $client, UserCreditLog $creditLog, AppUserInfo $appUserInfo, RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, TodayMoneyChange $todayMoneyChange, ExchangeGrapeOrder $exchangeGrapeOrder, ApplyCash $applyCash, UserAccount $userAccount, BonusLog $bonusLog, UserOrderTao $userOrderTao, VoipMoneyOrderMaid $voipMoneyOrderMaid, PretendShopOrdersMaid $pretendShopOrdersMaid, RechargeCreditLog $rechargeCreditLog, CircleMaid $circleMaid)
    {
        parent::__construct();
        $this->todayMoneyChange = $todayMoneyChange;
        $this->userAccount = $userAccount;
        $this->bonusLog = $bonusLog;
        $this->userOrderTao = $userOrderTao;
        $this->voipMoneyOrderMaid = $voipMoneyOrderMaid;
        $this->pretendShopOrdersMaid = $pretendShopOrdersMaid;
        $this->rechargeCreditLog = $rechargeCreditLog;
        $this->circleMaid = $circleMaid;
        $this->applyCash = $applyCash;
        $this->exchangeGrapeOrder = $exchangeGrapeOrder;
        $this->appUserInfo = $appUserInfo;
        $this->rechargeUserLevel = $rechargeUserLevel;
        $this->rechargeOrder = $rechargeOrder;
        $this->order_model = $order_model;
        $this->client = $client;
        $this->creditLog = $creditLog;
        $this->aboutLog = $aboutLog;
        $this->adUserInfo = $adUserInfo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $arrRequest['app_id'] = 3048;
        $arrRequest['need_time'] = 1582992000;

        $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2592000);

        $begin_1 = mktime(0, 0, 0, date('m') - $date_time, 1, date('Y'));
        $end_1 = mktime(23, 59, 59, date('m') - $date_time, date('t', $begin_1), date('Y'));

        $begin = date('Y-m-d H:i:s', $begin_1);
        $end = date('Y-m-d H:i:s', $end_1);

        var_dump($begin);
        var_dump($end);
        var_dump($begin_1);
        var_dump($end_1);


        $taobaoMaidOld = new TaobaoMaidOld();
        $jdMaidOld = new JdMaidOld();
        $pddMaidOld = new PddMaidOld();

        $prediction_now_2 = $taobaoMaidOld->getTime($arrRequest['app_id'], 2, $date_time);
        $prediction_now_1 = $taobaoMaidOld->getTime($arrRequest['app_id'], 1, $date_time);


        $jd_prediction_now_2 = $jdMaidOld->getTime($arrRequest['app_id'], 2, $date_time);
        $jd_prediction_now_1 = $jdMaidOld->getTime($arrRequest['app_id'], 1, $date_time);


        $pdd_prediction_now_2 = $pddMaidOld->getTime($arrRequest['app_id'], 2, $date_time);
        $pdd_prediction_now_1 = $pddMaidOld->getTime($arrRequest['app_id'], 1, $date_time);

        $my_special = $taobaoMaidOld->getTimeMySpecial($arrRequest['app_id'], 1, $date_time);


        var_dump($prediction_now_2);
        var_dump($prediction_now_1);
        var_dump($jd_prediction_now_2);
        var_dump($jd_prediction_now_1);
        var_dump($pdd_prediction_now_2);
        var_dump($pdd_prediction_now_1);
        var_dump($my_special);

        var_dump([
            'one_all' => round(($prediction_now_1 + $prediction_now_2 + $jd_prediction_now_2 + $jd_prediction_now_1 + $pdd_prediction_now_2 + $pdd_prediction_now_1), 2),
            'two_person' => round($prediction_now_2, 2),
            'three_prediction' => empty($my_special) ? 0 : round($my_special, 2),
            'four_no_push' => round(($prediction_now_1 - $my_special), 2),
        ]);

        exit();

        /**
         * 优质转正补逻辑
         */

//        //--------------------------------------------------------------------------------------结合----统计优质转正脚本
//        $arr_have = [9398066, 9126322, 8357040, 8665546, 8653067, 2472478, 2717898, 2809465, 3609773, 3655537, 8318914, 8332373, 8343580, 8375174, 8380992, 8428037, 8442422, 8446858, 8453019, 8479098, 8480588, 8595577, 8602685, 8661832, 8664899, 8666289, 8680308, 8697951, 8699612, 8702884, 8737511, 8760332, 8776477, 8806042, 8806336, 8825462, 8825837, 8829973, 8830094, 8830327, 8855113, 8873235, 8880588, 8933499, 8935279, 9000155, 9002301, 9009305, 9080272, 9081053, 9396723, 8513765, 9954663, 253113, 8734398, 2610934, 8323192, 2088574, 3481332, 3504314, 4187293, 4207917, 8760261, 8802723, 1868122, 9145998, 1354616, 8357877, 3473076, 8858189, 8949756, 254282, 8627519, 8362008, 9203531, 4182155, 8368030, 2033820, 2012875, 1916233, 8438012, 3048, 6441516, 8590147, 1354455, 8362355, 1350629, 8343339, 2830819, 2832447, 1440988, 2416655];
//        $userHigh = new UserHigh();
//        $appUserInfo = new AppUserInfo();
//        $user = $appUserInfo->where('history_active_value', '>=', config('putao.active_all_high'))->get();
//        if (!$user) {
//            var_dump('没有用户符合条件');
//            //统计结束
//            exit();
//        }
//        //-------------------------------------------------正常逻辑------------------------------------
//        //1号要做特殊处理
//        if (date('j') == 1) {
//            //上月一号0点以及月末59秒时间戳
//            $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
//            $today_month = date('Ym', $begin);
//        } else {
//            $today_month = date('Ym', time());
//        }
//
//        //设置进度条开始状态
//        $bar = $this->output->createProgressBar($user->count());
//        foreach ($user as $k => $item) {
//
//            //推动进度条
//            $bar->advance();
//            if (in_array($item['id'], $arr_have)) {
//                continue;
//            }
//
//            $user_high = $userHigh->getUserHigh($item['id']);
//            if (!$user_high->remark) {
//                //添加记录
//                $userHigh->addLog($item['id'], $today_month);
//            } else {
//                $is_need_add = 0;
//                $year_month = explode(',', $user_high->remark);
//                foreach ($year_month as $k => $v) {
//                    if ($v == $today_month) {
//                        $is_need_add++;
//                    }
//                }
//                if (!$is_need_add) {
//                    //添加记录
//                    $userHigh->addLog($item['id'], $today_month, $user_high->remark, 2);
//                }
//            }
//        }
//        //统计结束
//        $bar->finish();
//        exit();


        /**
         * 京东一分购10月31日活动修补订单
         */
//        $t = 0;
//        $round = 6760;
//        $new_model = new JdGetOneShow();
//        for ($i = 1; $i <= $round; $i++) {
//            $url = "https://api.91fyt.com/index.php/api/v1/hd/hdorderlistapi?pageindex=" . $i . "&pagesize=10&yn=2&memberid=1004023&hdid=15&starttime=0&endtime=1575129600&type=1";
//            //发送post请求
//            $res = $this->client->request('POST', $url, ['verify' => false]);
//            $json_res = (string)$res->getBody();
//            $arr_res = json_decode($json_res, true);
//            foreach ($arr_res['data']['data'] as $item) {
//                $one = $new_model->getOne($item['orderid']);
//                if (empty($one)) {
//                    $new_model->addOne($item);
//                }
//            }
//        }
//        var_dump($t);
//        exit();
        /**
         * 京东一分购，把订单分润
         */

//        $new_model = new JdGetOneShow();
//        $all = $new_model->where(['is_ptb' => 0])->get();
//        $bar = $this->output->createProgressBar($all->count());
//
//        foreach ($all as $one) {
//            $bar->advance();
//
//            if (!empty($one->is_ptb)) {
//                continue;
//            }
//
//            $user_info_tall = $this->appUserInfo->getUserById($one->subunionid);
//            if (empty($user_info_tall)) {
//                var_dump("未匹配到对应id的数据");
//                var_dump($one->subunionid);
//                continue;
//            }
//            $ad_user_info_tall = $this->adUserInfo->appToAdUserId($one->subunionid);
//            if (empty($ad_user_info_tall)) {
//                var_dump("未匹配到对应uid的数据");
//                var_dump($one->subunionid);
//                continue;
//            }
//            $ad_user_info_uid_tall = $ad_user_info_tall->uid;
//            $account_tall = $this->userAccount->getUserAccount($ad_user_info_uid_tall);
//            $res_account_tall = $this->userAccount->addUserPTBMoney(99, $ad_user_info_uid_tall);
//            //记录日志
//            $insert_id = $this->creditLog->addLog($ad_user_info_uid_tall, "JDX", ['extcredits4' => 99]);
//            $extcredits4_change_tall = $account_tall->extcredits4 + 99;
//            $this->aboutLog->addLog($insert_id, $ad_user_info_uid_tall, $ad_user_info_tall->username, $ad_user_info_tall->pt_id, ["extcredits4" => $account_tall->extcredits4], ["extcredits4" => $extcredits4_change_tall]);
//
//            $new_model->updateOne($one->orderid);
//        }
//
//        $bar->finish();
//
//        exit();


        /**
         * 处理未被正常分佣的代理商订单
         */

//        // 3、其它业务逻辑情况
//        $order_id = '20191211114904Uc2bM';
//        $rechargeOrder = new RechargeOrder();
//        $rechargeUserLevel = $this->rechargeUserLevel;
//        $shopOrders = new ShopOrders();
//        $order_model = $this->order_model;
//        $RechargeOrder = new RechargeOrder();
//        $RechargeCreditLog = new CommissionLog();
//        $one = $RechargeOrder->where(['orderid' => $order_id])->first();
//        if ($RechargeCreditLog->where(['orderid' => $one->orderid])->exists()) {
//            var_dump(0);
//        }
//        //拿到订单
//        //  // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
//        $order = $rechargeOrder->getOrdersById($order_id);
//        // 第二种订单情况，如果存在则进入商品回调
//        $shop_order = $shopOrders->getByOrderId($order_id);
//        if (!empty($shop_order)) {
//            if ($shop_order->app_id == 1569840) {
//                $shop_order->real_price = 0.01;
//            }
//
//            //对比金额
////                file_put_contents('wechat_pay_notify_shop.txt', $data->total_fee . PHP_EOL, FILE_APPEND);
//
////                file_put_contents('wechat_pay_notify_shop.txt', $shop_order->real_price . PHP_EOL, FILE_APPEND);
//
//            $computer_price = $shop_order->real_price * 100;
////                if ($data->total_fee == $computer_price) {
////                file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);
//
////                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);
//
//            $res_maid = $order_model->processOrder($shop_order->order_id);
////                }
//        }
//
//        if (!empty($order)) {
//            if ($order->uid == 1499531) {
//                $order->price = 0.01;
//            }
////                if (($order->price * 100) <> $data->total_fee) {
////                    file_put_contents('wechat_pay_notify_shop.txt', '金额不对等' . PHP_EOL, FILE_APPEND);
////                    file_put_contents('wechat_pay_notify_shop.txt', $data->total_fee . PHP_EOL, FILE_APPEND);
////                    file_put_contents('wechat_pay_notify_shop.txt', "订单金额：" . $order->price . PHP_EOL, FILE_APPEND);
////                    exit();
////                }
////                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);
//
//            // 5、其它业务逻辑情况
//            $arr = [
//                'uid' => $order->uid,
//                'money' => $order->price,
//                'orderid' => $order_id,
//            ];
//            if ($shop_order) {
//                $arr = [
//                    'uid' => $order->uid,
//                    'money' => 800,
//                    'orderid' => $order_id,
//                ];
//            }
////                $AdUserInfo = new AdUserInfo();
////                $x = $AdUserInfo->getUserById($order->uid);
////                if ($x->groupid <= 22) {
//            $rechargeUserLevel->initOrder($arr);
//            $rechargeUserLevel->updateExt(); //升级
//            $rechargeUserLevel->returnCommission(); //返佣
//            $rechargeUserLevel->handleArticle(); //更新文章
//            $rechargeOrder->updateOrderStatus($order_id);//更新订单
////                }
//        }
//        var_dump(1);
//        exit();
//            file_put_contents('wechat_pay_notify_shop.txt', 'step-2' . PHP_EOL, FILE_APPEND);
        /**
         * 每日报表重新补计算
         */
//        $time_wuhang = 1575320705;
//        $time_end = strtotime(date("Y-m-d", $time_wuhang));
//        $time_start = $time_end - 86400;
//        $time_start_time = date('Y-m-d H:i:s', $time_start);
//        $time_end_time = date('Y-m-d H:i:s', $time_end);
//        $month_time_start = strtotime(date("Y-m-01", $time_wuhang));
//        $month_time_end = strtotime(date('Y-m-t 23:59:59', $time_wuhang));
//        $all_ptb_number = 0;
//        $submit_new = 0;
//        $share_new = 0;
//        $commission_all = 0;
//        $commission_phone = 0;
//        $commission_shop = 0;
//        $commission_vip = 0;
//        $commission_ad = 0;
//        $commission_circle = 0;
//        $commission_red = 0;
//        $now_today_app_cash = 0;
//        $now_today_app_cash_success = 0;
//        $now_today_app_cash_fail = 0;
//        $now_today_ad_cash = 0;
//        $now_today_ad_cash_success = 0;
//        $now_today_ad_cash_fail = 0;
//        $now_today_app_cash_submit = 0;
//        $now_today_app_cash_dividend = 0;
//        $to_month_submit_new = 0;
//        $to_month_ok_submit_new = 0;
//
//        $all_ptb_number += $this->userAccount->sum('extcredits4');
//        $sql = "
//			SELECT sum(`cashback_amount`) as ct FROM lc_user_order WHERE create_time > {$month_time_start} AND create_time < {$month_time_end} AND status > 2
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $to_month_submit_new += $res[0]->ct;
//        $sql = "
//			SELECT sum(`cashback_amount`) as ct FROM lc_user_order WHERE create_time > {$month_time_start} AND create_time < {$month_time_end} AND status = 4
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $to_month_ok_submit_new += $res[0]->ct;
//        $sql = "
//			SELECT sum(`cashback_amount`) as ct FROM lc_user_order WHERE create_time > {$time_start} AND create_time < {$time_end} AND status = 3
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $submit_new += $res[0]->ct;
//        $sql = "
//            SELECT sum(`bonus_amount`) as ct FROM lc_bonus_log WHERE create_time > {$time_start}  AND create_time < {$time_end}
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $share_new += $res[0]->ct;
//        $sql = "
//            SELECT sum(`money`) as ct FROM pre_voip_money_orders_maid WHERE created_at > '" . $time_start_time . "'  AND created_at < '" . $time_end_time . "'
//        ";
//        $res = DB::connection("a1191125678")->select($sql);
//        $commission_phone += $res[0]->ct;
//        $sql = "
//            SELECT sum(`money`)/10 as ct FROM lc_shop_orders_pretend_maid WHERE created_at > '" . $time_start_time . "'  AND created_at < '" . $time_end_time . "'
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $commission_shop += $res[0]->ct;
//        $sql = "
//            SELECT sum(`money`)/10 as ct FROM pre_aljbgp_credit_log WHERE money > 20 AND dateline > {$time_start}  AND dateline < {$time_end}
//        ";
//        $res = DB::connection("a1191125678")->select($sql);
//        $commission_vip += $res[0]->ct;
//        $sql = "
//            SELECT sum(`money`)/10 as ct FROM pre_aljbgp_credit_log WHERE money <= 20 AND dateline > {$time_start}  AND dateline < {$time_end}
//        ";
//        $res = DB::connection("a1191125678")->select($sql);
//        $commission_ad += $res[0]->ct;
//        $sql = "
//            SELECT sum(`money`)/10 as ct FROM lc_circle_ring_add_order_maid WHERE created_at > '" . $time_start_time . "'  AND created_at < '" . $time_end_time . "' AND `type` <> 3
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $commission_circle += $res[0]->ct;
//        $sql = "
//            SELECT sum(`money`)/10 as ct FROM lc_circle_ring_add_order_maid WHERE created_at > '" . $time_start_time . "'  AND created_at < '" . $time_end_time . "' AND `type` = 3
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $commission_red += $res[0]->ct;
//
//        $commission_all = $commission_phone + $commission_shop + $commission_vip + $commission_ad + $commission_circle + $commission_red;
//        $sql = "
//            SELECT sum(`cash_amount`) as ct FROM lc_apply_cash WHERE create_time > {$time_start}  AND create_time < {$time_end} AND `status` = 0
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $now_today_app_cash += $res[0]->ct;
//
//        $sql = "
//           SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE type = 0 AND created_at > '{$time_start_time}' AND  created_at < '{$time_end_time}'
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $now_today_app_cash += $res[0]->ct;
//
//
//        $sql = "
//            SELECT sum(`amount`) as ct FROM pre_xigua_t_tixian WHERE crts > {$time_start}  AND crts < {$time_end}
//        ";
//        $res = DB::connection("a1191125678")->select($sql);
//        $now_today_ad_cash += $res[0]->ct;
//        $sql = "
//            SELECT sum(`cash_amount`) as ct FROM lc_apply_cash WHERE handle_time > {$time_start}  AND handle_time < {$time_end} AND `status` = 1
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $now_today_app_cash_success += $res[0]->ct;
//
//        $sql = "
//           SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE type = 1 AND updated_at > '{$time_start_time}' AND  updated_at < '{$time_end_time}'
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $now_today_app_cash_success += $res[0]->ct;
//
//        $sql = "
//            SELECT sum(`cash_amount`) as ct FROM lc_apply_cash WHERE handle_time > {$time_start}  AND handle_time < {$time_end} AND `status` = 2
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $now_today_app_cash_fail += $res[0]->ct;
//        $sql = "
//           SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE type = 2 AND updated_at > '{$time_start_time}' AND  updated_at < '{$time_end_time}'
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $now_today_app_cash_fail += $res[0]->ct;
//
//
//        $sql = "
//            SELECT sum(`amount`) as ct FROM pre_xigua_t_tixian WHERE upts > {$time_start}  AND upts < {$time_end} AND status = 1
//        ";
//        $res = DB::connection("a1191125678")->select($sql);
//        $now_today_ad_cash_success += $res[0]->ct;
//        $sql = "
//            SELECT sum(`amount`) as ct FROM pre_xigua_t_tixian WHERE upts > {$time_start}  AND upts < {$time_end} AND status = 2
//        ";
//        $res = DB::connection("a1191125678")->select($sql);
//        $now_today_ad_cash_fail += $res[0]->ct;
//
//        $now_today_app_cash_submit += 0;
//        $now_today_app_cash_dividend += 0;
//        $now_today_app_cash_all = $now_today_app_cash_dividend + $now_today_app_cash_submit;
//
//        $this->todayMoneyChange->updateOrCreate([
//            'change_time' => $time_start
//        ], [
//            'change_time' => $time_start,
//            'to_month_submit_new' => $to_month_submit_new,
//            'to_month_ok_submit_new' => $to_month_ok_submit_new,
//            'all_ptb_number' => $all_ptb_number,
//            'submit_new' => $submit_new,
//            'share_new' => $share_new,
//            'commission_all' => $commission_all,
//            'commission_phone' => $commission_phone,
//            'commission_shop' => $commission_shop,
//            'commission_vip' => $commission_vip,
//            'commission_ad' => $commission_ad,
//            'commission_circle' => $commission_circle,
//            'commission_red' => $commission_red,
//            'now_today_app_cash' => $now_today_app_cash,
//            'now_today_app_cash_success' => $now_today_app_cash_success,
//            'now_today_app_cash_fail' => $now_today_app_cash_fail,
//            'now_today_ad_cash' => $now_today_ad_cash,
//            'now_today_ad_cash_success' => $now_today_ad_cash_success,
//            'now_today_ad_cash_fail' => $now_today_ad_cash_fail,
//            'now_today_app_cash_submit' => $now_today_app_cash_submit,
//            'now_today_app_cash_dividend' => $now_today_app_cash_dividend,
//            'now_today_app_cash_all' => $now_today_app_cash_all,
//        ]);

    }
}
