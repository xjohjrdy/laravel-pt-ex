<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\UserHigh;
use Illuminate\Console\Command;

class PushExchangeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pushExchangeUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导导导';

    protected $appUserInfo;
    protected $bonusLog;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AppUserInfo $appUserInfo, BonusLog $bonusLog)
    {
        parent::__construct();
        $this->appUserInfo = $appUserInfo;
        $this->bonusLog = $bonusLog;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now_time = date('Y-m', time());
        $unix_now_time = strtotime($now_time);
        var_dump($now_time);
        $fp = fopen('/data/wwwroot/excel/forward_exchange_' . $now_time . '.csv', 'w');
        fputcsv($fp, array('角色等级', '用户id', '手机号', '上级ID', '直属下级总数', '团队总数', '用户名', '真实姓名', '支付宝', '次月可提现金额', '上月活跃值', '当前活跃值', '可提现金额', '本月过审订单数', '签到次数', '订单可报销额度', '转正等级变化时间'));

        $test_user = $this->appUserInfo->where('level', '=', '3')->where('level_modify_time', '<=', $unix_now_time)->get();

        $bar = $this->output->createProgressBar($test_user->count());
        foreach ($test_user as $u) {
            $bar->advance();

            $very = 0;
            $next_count = $this->appUserInfo->getNextOneFloorCount($u->id, 0);
            if ($next_count < 10) {
                continue;
            }
            $next_count_three = $this->appUserInfo->getNextFloorCount($u->id);
            $userHigh = new UserHigh();
            $obj_user_high = $userHigh->getUserHighInfo($u->id);
            if ($obj_user_high && $obj_user_high->number >= 1) {
                $very = 1;
            } elseif ($u->active_value >= 97.5) {
                $very = 1;
            }

            $adUserInfo = new AdUserInfo();
            $ad_user = $adUserInfo->appToAdUserId($u->id);
            if (!empty($ad_user)) {
                if ($ad_user->groupid == 24) {
                    $very = 1;
                }
            }

            if ($very == 1) {
                continue;
            }

            fputcsv($fp, array('普通转正', $u->id, $u->phone, $u->parent_id, $next_count, $next_count_three, $u->user_name, $u->real_name, $u->alipay, $u->next_month_cash_amount, $u->history_active_value, $u->active_value, $u->apply_cash_amount, $u->current_month_passed_order, $u->sign_number, $u->order_can_apply_amount, date('Y - m - d H:i:s', $u->level_modify_time)));
        }

        $bar->finish();
        fclose($fp);

        $fp = fopen('/data/wwwroot/excel/forward_very_exchange_' . $now_time . '.csv', 'w');
        fputcsv($fp, array('角色等级', '用户id', '手机号', '上级ID', '直属下级总数', '团队总数', '用户名', '真实姓名', '支付宝', '次月可提现金额', '上月活跃值', '当前活跃值', '可提现金额', '本月过审订单数', '签到次数', '订单可报销额度', '转正等级变化时间'));

        $test_user = $this->appUserInfo->where('level', '=', '3')->where('level_modify_time', '<=', $unix_now_time)->get();

        $bar = $this->output->createProgressBar($test_user->count());
        foreach ($test_user as $u) {
            $bar->advance();
            $very = 0;
            $next_count = $this->appUserInfo->getNextOneFloorCount($u->id, 0);
            if ($next_count < 10) {
                continue;
            }
            $next_count_three = $this->appUserInfo->getNextFloorCount($u->id);


            $userHigh = new UserHigh();
            $obj_user_high = $userHigh->getUserHighInfo($u->id);
            if ($obj_user_high && $obj_user_high->number >= 1) {
                $very = 1;
            } elseif ($u->active_value >= 97.5) {
                $very = 1;
            }

            $adUserInfo = new AdUserInfo();
            $ad_user = $adUserInfo->appToAdUserId($u->id);

            if (!empty($ad_user)) {
                if ($ad_user->groupid == 24) {
                    $very = 1;
                }
            }

            if ($very == 0) {
                continue;
            }

            fputcsv($fp, array('优质转正', $u->id, $u->phone, $u->parent_id, $next_count, $next_count_three, $u->user_name, $u->real_name, $u->alipay, $u->next_month_cash_amount, $u->history_active_value, $u->active_value, $u->apply_cash_amount, $u->current_month_passed_order, $u->sign_number, $u->order_can_apply_amount, date('Y - m - d H:i:s', $u->level_modify_time)));
        }

        $bar->finish();
        fclose($fp);

        $fp = fopen('/data/wwwroot/excel/forward_manage_' . $now_time . '.csv', 'w');
        fputcsv($fp, array('角色等级', '用户id', '手机号', '上级ID', '直属下级总数', '团队总数', '用户名', '真实姓名', '支付宝', '次月可提现金额', '上月活跃值', '当前活跃值', '可提现金额', '本月过审订单数', '签到次数', '订单可报销额度', '转正等级变化时间'));

        $test_user = $this->appUserInfo->where('level', '=', '4')->where('level_modify_time', '<=', $unix_now_time)->get();

        $bar = $this->output->createProgressBar($test_user->count());
        foreach ($test_user as $u) {
            $bar->advance();
            $next_count = $this->appUserInfo->getNextOneFloorCount($u->id, 0);
            if ($next_count < 10) {
                continue;
            }
            $next_count_three = $this->appUserInfo->getNextFloorCount($u->id);
            fputcsv($fp, array('经理', $u->id, $u->phone, $u->parent_id, $next_count, $next_count_three, $u->user_name, $u->real_name, $u->alipay, $u->next_month_cash_amount, $u->history_active_value, $u->active_value, $u->apply_cash_amount, $u->current_month_passed_order, $u->sign_number, $u->order_can_apply_amount, date('Y - m - d H:i:s', $u->level_modify_time)));
        }

        $bar->finish();
        fclose($fp);


        $fp = fopen('/data/wwwroot/excel/forward_special_' . $now_time . '.csv', 'w');
        fputcsv($fp, array('角色等级', '用户id', '手机号', '上级ID', '直属下级总数', '团队总数', '用户名', '真实姓名', '支付宝', '次月可提现金额', '上月活跃值', '当前活跃值', '可提现金额', '本月过审订单数', '签到次数', '订单可报销额度', '转正等级变化时间'));
        $test_user = $this->appUserInfo->where('level', '>', '2')->where('level_modify_time', '>', $unix_now_time)->get();

        $bar = $this->output->createProgressBar($test_user->count());
        foreach ($test_user as $u) {
            $bar->advance();
            $very = 0;
            $bonus_log = $this->bonusLog->where(['user_id' => $u->id])->first();
            if (empty($bonus_log)) {
                continue;
            }
            $next_count = $this->appUserInfo->getNextOneFloorCount($u->id, 0);
            if ($next_count < 10) {
                continue;
            }
            $next_count_three = $this->appUserInfo->getNextFloorCount($u->id);


            $userHigh = new UserHigh();
            $obj_user_high = $userHigh->getUserHighInfo($u->id);
            if ($obj_user_high && $obj_user_high->number >= 1) {
                $very = 1;
            } elseif ($u->active_value >= 97.5) {
                $very = 1;
            }

            $adUserInfo = new AdUserInfo();
            $ad_user = $adUserInfo->appToAdUserId($u->id);

            if (!empty($ad_user)) {
                if ($ad_user->groupid == 24) {
                    $very = 1;
                }
            }


            fputcsv($fp, array($very . '特殊情况', $u->id, $u->phone, $u->parent_id, $next_count, $next_count_three, $u->user_name, $u->real_name, $u->alipay, $u->next_month_cash_amount, $u->history_active_value, $u->active_value, $u->apply_cash_amount, $u->current_month_passed_order, $u->sign_number, $u->order_can_apply_amount, date('Y - m - d H:i:s', $u->level_modify_time)));
        }
        $bar->finish();
        fclose($fp);
        exit();

    }
}
