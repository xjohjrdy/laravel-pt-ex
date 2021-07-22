<?php

namespace App\Console\Commands;

use App\Entitys\Ad\ExchangeGrapeOrder;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\VoipMoneyOrderMaid;
use App\Entitys\App\ApplyCash;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\CircleMaid;
use App\Entitys\App\PretendShopOrdersMaid;
use App\Entitys\App\TodayMoneyChange;
use App\Entitys\App\UserOrderTao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCalculateDailyFinancial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AutoCalculateDailyFinancial';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计算每日财务表';


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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AppUserInfo $appUserInfo, TodayMoneyChange $todayMoneyChange, ExchangeGrapeOrder $exchangeGrapeOrder, ApplyCash $applyCash, UserAccount $userAccount, BonusLog $bonusLog, UserOrderTao $userOrderTao, VoipMoneyOrderMaid $voipMoneyOrderMaid, PretendShopOrdersMaid $pretendShopOrdersMaid, RechargeCreditLog $rechargeCreditLog, CircleMaid $circleMaid)
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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time_end = strtotime(date("Y-m-d"), time());
        $time_start = $time_end - 86400;
        $time_start_time = date('Y-m-d H:i:s', $time_start);
        $time_end_time = date('Y-m-d H:i:s', $time_end);
        $month_time_start = strtotime(date("Y-m-01", time()));
        $month_time_end = strtotime(date('Y-m-t 23:59:59', time()));
        $all_ptb_number = 0;
        $submit_new = 0;
        $share_new = 0;
        $commission_all = 0;
        $commission_phone = 0;
        $commission_shop = 0;
        $commission_vip = 0;
        $commission_ad = 0;
        $commission_circle = 0;
        $commission_red = 0;
        $now_today_app_cash = 0;
        $now_today_app_cash_success = 0;
        $now_today_app_cash_fail = 0;
        $now_today_ad_cash = 0;
        $now_today_ad_cash_success = 0;
        $now_today_ad_cash_fail = 0;
        $now_today_app_cash_submit = 0;
        $now_today_app_cash_dividend = 0;
        $to_month_submit_new = 0;
        $to_month_ok_submit_new = 0;

        $all_ptb_number += $this->userAccount->sum('extcredits4');
        $sql = "
			SELECT sum(`cashback_amount`) as ct FROM lc_user_order WHERE create_time > {$month_time_start} AND create_time < {$month_time_end} AND status > 2
        ";
        $res = DB::connection("app38")->select($sql);
        $to_month_submit_new += $res[0]->ct;
        $sql = "
			SELECT sum(`cashback_amount`) as ct FROM lc_user_order WHERE create_time > {$month_time_start} AND create_time < {$month_time_end} AND status = 4
        ";
        $res = DB::connection("app38")->select($sql);
        $to_month_ok_submit_new += $res[0]->ct;
        $sql = "
			SELECT sum(`cashback_amount`) as ct FROM lc_user_order WHERE create_time > {$time_start} AND create_time < {$time_end} AND status = 3
        ";
        $res = DB::connection("app38")->select($sql);
        $submit_new += $res[0]->ct;
        $sql = "
            SELECT sum(`bonus_amount`) as ct FROM lc_bonus_log WHERE create_time > {$time_start}  AND create_time < {$time_end}
        ";
        $res = DB::connection("app38")->select($sql);
        $share_new += $res[0]->ct;
        $sql = "
            SELECT sum(`money`) as ct FROM pre_voip_money_orders_maid WHERE created_at > '" . $time_start_time . "'  AND created_at < '" . $time_end_time . "'
        ";
        $res = DB::connection("a1191125678")->select($sql);
        $commission_phone += $res[0]->ct;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM lc_shop_orders_pretend_maid WHERE created_at > '" . $time_start_time . "'  AND created_at < '" . $time_end_time . "'
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_shop += $res[0]->ct;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM pre_aljbgp_credit_log WHERE money > 20 AND dateline > {$time_start}  AND dateline < {$time_end}
        ";
        $res = DB::connection("a1191125678")->select($sql);
        $commission_vip += $res[0]->ct;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM pre_aljbgp_credit_log WHERE money <= 20 AND dateline > {$time_start}  AND dateline < {$time_end}
        ";
        $res = DB::connection("a1191125678")->select($sql);
        $commission_ad += $res[0]->ct;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM lc_circle_ring_add_order_maid WHERE created_at > '" . $time_start_time . "'  AND created_at < '" . $time_end_time . "' AND `type` <> 3
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_circle += $res[0]->ct;
        $sql = "
            SELECT sum(`money`)/10 as ct FROM lc_circle_ring_add_order_maid WHERE created_at > '" . $time_start_time . "'  AND created_at < '" . $time_end_time . "' AND `type` = 3
        ";
        $res = DB::connection("app38")->select($sql);
        $commission_red += $res[0]->ct;

        $commission_all = $commission_phone + $commission_shop + $commission_vip + $commission_ad + $commission_circle + $commission_red;
        $sql = "
            SELECT sum(`cash_amount`) as ct FROM lc_apply_cash WHERE create_time > {$time_start}  AND create_time < {$time_end} AND `status` = 0
        ";
        $res = DB::connection("app38")->select($sql);
        $now_today_app_cash += $res[0]->ct;

        $sql = "
           SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE type = 0 AND created_at > '{$time_start_time}' AND  created_at < '{$time_end_time}'
        ";
        $res = DB::connection("app38")->select($sql);
        $now_today_app_cash += $res[0]->ct;


        $sql = "
            SELECT sum(`amount`) as ct FROM pre_xigua_t_tixian WHERE crts > {$time_start}  AND crts < {$time_end}
        ";
        $res = DB::connection("a1191125678")->select($sql);
        $now_today_ad_cash += $res[0]->ct;
        $sql = "
            SELECT sum(`cash_amount`) as ct FROM lc_apply_cash WHERE handle_time > {$time_start}  AND handle_time < {$time_end} AND `status` = 1
        ";
        $res = DB::connection("app38")->select($sql);
        $now_today_app_cash_success += $res[0]->ct;

        $sql = "
           SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE type = 1 AND updated_at > '{$time_start_time}' AND  updated_at < '{$time_end_time}'
        ";
        $res = DB::connection("app38")->select($sql);
        $now_today_app_cash_success += $res[0]->ct;

        $sql = "
            SELECT sum(`cash_amount`) as ct FROM lc_apply_cash WHERE handle_time > {$time_start}  AND handle_time < {$time_end} AND `status` = 2
        ";
        $res = DB::connection("app38")->select($sql);
        $now_today_app_cash_fail += $res[0]->ct;
        $sql = "
           SELECT sum(`money`) as ct FROM lc_taobao_user_get WHERE type = 2 AND updated_at > '{$time_start_time}' AND  updated_at < '{$time_end_time}'
        ";
        $res = DB::connection("app38")->select($sql);
        $now_today_app_cash_fail += $res[0]->ct;


        $sql = "
            SELECT sum(`amount`) as ct FROM pre_xigua_t_tixian WHERE upts > {$time_start}  AND upts < {$time_end} AND status = 1
        ";
        $res = DB::connection("a1191125678")->select($sql);
        $now_today_ad_cash_success += $res[0]->ct;
        $sql = "
            SELECT sum(`amount`) as ct FROM pre_xigua_t_tixian WHERE upts > {$time_start}  AND upts < {$time_end} AND status = 2
        ";
        $res = DB::connection("a1191125678")->select($sql);
        $now_today_ad_cash_fail += $res[0]->ct;

        $now_today_app_cash_submit += $this->appUserInfo->sum('order_amount');
        $now_today_app_cash_dividend += $this->appUserInfo->sum('bonus_amount');
        $now_today_app_cash_all = $now_today_app_cash_dividend + $now_today_app_cash_submit;

        $this->todayMoneyChange->updateOrCreate([
            'change_time' => $time_start
        ], [
            'change_time' => $time_start,
            'to_month_submit_new' => $to_month_submit_new,
            'to_month_ok_submit_new' => $to_month_ok_submit_new,
            'all_ptb_number' => $all_ptb_number,
            'submit_new' => $submit_new,
            'share_new' => $share_new,
            'commission_all' => $commission_all,
            'commission_phone' => $commission_phone,
            'commission_shop' => $commission_shop,
            'commission_vip' => $commission_vip,
            'commission_ad' => $commission_ad,
            'commission_circle' => $commission_circle,
            'commission_red' => $commission_red,
            'now_today_app_cash' => $now_today_app_cash,
            'now_today_app_cash_success' => $now_today_app_cash_success,
            'now_today_app_cash_fail' => $now_today_app_cash_fail,
            'now_today_ad_cash' => $now_today_ad_cash,
            'now_today_ad_cash_success' => $now_today_ad_cash_success,
            'now_today_ad_cash_fail' => $now_today_ad_cash_fail,
            'now_today_app_cash_submit' => $now_today_app_cash_submit,
            'now_today_app_cash_dividend' => $now_today_app_cash_dividend,
            'now_today_app_cash_all' => $now_today_app_cash_all,
        ]);

    }
}
