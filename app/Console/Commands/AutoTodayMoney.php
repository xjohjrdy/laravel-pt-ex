<?php

namespace App\Console\Commands;

use App\Entitys\App\TodayMoneyChangeNew;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoTodayMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AutoTodayMoney';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '当日报表统计（新版）';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $today_change_new = new TodayMoneyChangeNew();

        $change_time = strtotime(date('Y-m-d', time())) - 86400;

        $end_time = strtotime(date('Y-m-d', time())); // 当日0点0分0秒时间戳
        $start_time = strtotime(date('Y-m-d', time())) - 86400; //前一天的0点0分0秒时间戳

        $end_time_str = date('Y-m-d H:i:s', $end_time);// 当日0点0分0秒字符串
        $start_time_str = date('Y-m-d H:i:s', $start_time);//前一天的0点0分0秒字符串

        $today_bonus_add = 0;
        $sql = "
            SELECT sum(`bonus_amount`) as ct FROM lc_bonus_log WHERE create_time > {$start_time}  AND create_time < {$end_time}
        ";
        $res = DB::connection("app38")->select($sql);
        $today_bonus_add += $res[0]->ct;

        $today_exchange_all = 0;
        $sql = "
            SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE `type` <> 2 AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_exchange_all += $res[0]->ct;


        //SELECT sum(`money`) as ct FROM lc_taobao_user
        /**
         *
         *   $sql = "
         * SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE `type` = 0 AND created_at < '{$end_time_str}'
         * ";
         *
         *   $sql = "
         * SELECT sum(`money`) as ct FROM lc_taobao_user
         * ";
         */
        $today_no_exchange_all = 0;
        $sql = "
            SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE `type` = 0 AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_no_exchange_all += $res[0]->ct;


        /**
         * -------------------------------------------------------------------------第三方支付共收today_third_pay
         */

        $today_third_pay = 0;

        $sql = "
            SELECT sum(`real_price`) as ct FROM lc_shop_orders WHERE `status` > 0 AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_third_pay += $res[0]->ct;
        //爆款商城模块
        //头条模块---------由于看到线上订单数据几乎没有，故可能不是lc_article_orders表，暂停一下

        $sql = "
            SELECT sum(`money`) as ct FROM lc_circle_ring_add_order WHERE `buy_type`  = 1 AND `status` = 1  AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_third_pay += $res[0]->ct;

        //圈子模块

        $sql = "
            SELECT sum(`price`) as ct FROM lc_circle_ring_red WHERE `order_id`  <> 0 AND `status` = 1  AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_third_pay += $res[0]->ct;

        //圈子红包模块

        /**
         * -------------------------------------------------------------------------第三方支付共收today_third_pay
         */

        /**
         * -------------------------------------------------------------------------用户余额支付today_user_balance_pay
         */

        $today_user_balance_pay = 0;

        //爆款商城模块
        $sql = "
            SELECT sum(`ptb_number`)/10 as ct FROM lc_shop_orders WHERE `status` > 0 AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_user_balance_pay += $res[0]->ct;
        //头条模块---------由于看到线上订单数据几乎没有，故可能不是lc_article_orders表，暂停一下

        //圈子模块
        $sql = "
            SELECT sum(`money`) as ct FROM lc_circle_ring_add_order WHERE `buy_type` = 2 AND `status` = 1 AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_user_balance_pay += $res[0]->ct;

        //圈子红包模块
        $sql = "
            SELECT sum(`price`) as ct FROM lc_circle_ring_red WHERE `order_id` = 0 AND `status` = 1 AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_user_balance_pay += $res[0]->ct;
        /**
         * -------------------------------------------------------------------------用户余额支付today_user_balance_pay
         */

        /**
         * ------------------------------------------------------------------------爆款商城（当日预计佣金一份）
         */

        $commission_shop_today_no = 0;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM lc_shop_orders_pretend_maid WHERE  `status` = 0 AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_shop_today_no += $res[0]->ct;

        /**
         * ------------------------------------------------------------------------爆款商城（当日预计佣金一份）
         */

        /**
         * ------------------------------------------------------------------------爆款商城（当日已结算佣金一份）
         */

        $commission_shop_today_is = 0;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM lc_shop_orders_maid WHERE   created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_shop_today_is += $res[0]->ct;

        /**
         * ------------------------------------------------------------------------爆款商城（当日已结算佣金一份）
         */


        /**
         * ------------------------------------------------------------------------广告包产生（筛选当日）
         */

        $commission_ad = 0;
//        $sql = "
//            SELECT count(*) as ct FROM lc_article_check_info WHERE   created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
//        ";
//        $res = DB::connection("app38")->select($sql);
//        $commission_ad += $res[0]->ct;


        /**
         * ------------------------------------------------------------------------广告包产生（筛选当日）
         */

        /**
         * ------------------------------------------------------------------圈子600购买
         */
        $commission_circle_buy = 0;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM lc_circle_ring_add_order_maid WHERE `type` IN (2,4,6) AND  created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_circle_buy += $res[0]->ct;

        /**
         * ------------------------------------------------------------------圈子600购买
         */

        /**
         * --------------------------------------------------------------圈子竞价
         */
        $commission_circle_bidding = 0;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM lc_circle_ring_add_order_maid WHERE `type` IN (1,5) AND  created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_circle_bidding += $res[0]->ct;

        /**
         * --------------------------------------------------------------圈子竞价
         */

        /**
         * ----------------------------------------------------------圈子红包
         */

        $commission_red = 0;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM lc_circle_ring_add_order_maid WHERE created_at > '" . $start_time_str . "'  AND created_at < '" . $end_time_str . "' AND `type` = 3
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_red += $res[0]->ct;

        /**
         * ----------------------------------------------------------圈子红包
         */

        /**
         * ---------------------------------------------------------我的头条分佣
         */


        $commission_article = 0;
        $sql = "
            SELECT count(*)/10 as ct FROM lc_article_check_info WHERE   created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_article += $res[0]->ct;

        /**
         * ---------------------------------------------------------申请提现成功
         */

        $today_exchange_success = 0;
        $sql = "
            SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE `type` = 1 AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_exchange_success += $res[0]->ct;

        /**
         * ---------------------------------------------------------申请提现成功
         */


        /**
         * ---------------------------------------------------------申请提现金额
         */
        $today_exchange = 0;
        $sql = "
            SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE `type` <> 2 AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_exchange += $res[0]->ct;

        /**
         * ---------------------------------------------------------申请提现金额
         */


        /**
         * ---------------------------------------------------------申请提现失败
         */

        $today_exchange_fail = 0;
        $sql = "
            SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE `type` = 2 AND created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $today_exchange_fail += $res[0]->ct;

        /**
         * ---------------------------------------------------------申请提现失败
         */


        /**
         * ---------------------------------------------------------饿了么分佣
         */

        $commission_ele = 0;
        $sql = "
            SELECT sum(`maid_money`) as ct FROM lc_ele_maid WHERE created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_ele += $res[0]->ct;

        /**
         * ---------------------------------------------------------饿了么分佣
         */

        /**
         * ---------------------------------------------------------美团分佣
         */

        $commission_mt = 0;
        $sql = "
            SELECT sum(`maid_money`) as ct FROM lc_mt_maid WHERE created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_mt += $res[0]->ct;

        /**
         * ---------------------------------------------------------美团分佣
         */

        /**
         * ---------------------------------------------------------信用卡分佣
         */

        $commission_card = 0;
        $sql = "
           SELECT sum(`maid_ptb`) / 10 as ct FROM lc_card_maid WHERE created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_card += $res[0]->ct;

        /**
         * ---------------------------------------------------------信用卡分佣
         */

        /**
         * --------------------------------------------------------淘宝分佣
         */

        $commission_taobao = 0;
        $sql = "
           SELECT sum(`maid_money`) as ct FROM lc_taobao_maid WHERE created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_taobao += $res[0]->ct;

        /**
         * --------------------------------------------------------淘宝分佣
         */


        /**
         * --------------------------------------------------------京东分佣
         */

        $commission_jd = 0;
        $sql = "
           SELECT sum(`maid_money`) as ct FROM lc_jd_maid WHERE created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_jd += $res[0]->ct;

        /**
         * --------------------------------------------------------京东分佣
         */


        /**
         * --------------------------------------------------------拼多多分佣
         */

        $commission_pdd = 0;
        $sql = "
           SELECT sum(`maid_money`) as ct FROM lc_pdd_maid WHERE created_at > '{$start_time_str}'  AND created_at < '{$end_time_str}'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_pdd += $res[0]->ct;

        /**
         * --------------------------------------------------------拼多多分佣
         */


        $all_money = 0;
        $sql = "
         SELECT sum(`money`) as ct FROM lc_taobao_user
         ";
        $res = DB::connection("app38")->select($sql);
        $all_money += $res[0]->ct;

        $today_change_new->updateOrCreate([
            'change_time' => $change_time
        ], [
            'change_time' => $change_time,
            'all_money' => $all_money,
            'today_bonus_add' => $today_bonus_add,
            'today_no_exchange_all' => $today_no_exchange_all,
            'today_exchange_all' => $today_exchange_all,
            'today_third_pay' => $today_third_pay,
            'today_user_balance_pay' => $today_user_balance_pay,
            'commission_shop_today_no' => $commission_shop_today_no,
            'commission_shop_today_is' => $commission_shop_today_is,
            'commission_ad' => $commission_ad,
            'commission_circle_buy' => $commission_circle_buy,
            'commission_circle_bidding' => $commission_circle_bidding,
            'commission_circle_red' => $commission_red,
            'commission_article' => $commission_article,
            'commission_taobao' => $commission_taobao,
            'commission_jd' => $commission_jd,
            'commission_pdd' => $commission_pdd,
            'commission_card' => $commission_card,
            'commission_ele' => $commission_ele,
            'commission_mt' => $commission_mt,
            'today_exchange' => $today_exchange,
            'today_exchange_success' => $today_exchange_success,
            'today_exchange_fail' => $today_exchange_fail,
        ]);

    }
}
