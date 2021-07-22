<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoMaid;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\App\TaobaoUser;
use App\Entitys\App\UserOrderNew;
use App\Entitys\App\WCountOrderAmount;
use App\Services\Common\Time;
use App\Services\WuHang\Add;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CountOrderAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CountOrderAmount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每月24日0点，计算[订单返现金额] 和 [可提现金额]';

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
        $obj_timestamp = new Time();
        $last_month = $obj_timestamp->getLastMonthTimestamp();
        $obj_taobao_maid_old = new TaobaoMaidOld();
        $last_month_time = [date('Y-m-d H:i:s', $last_month[0]), date('Y-m-d H:i:s', $last_month[1])];
        $num_taobao_maid_old = $obj_taobao_maid_old->lastAllApplyData($last_month_time);
        $all_page = ceil($num_taobao_maid_old / 10000);
        $page = 1;
        $taobao_change_user_log = new TaobaoChangeUserLog();
        $count_order_amount = new WCountOrderAmount();

        $cStartTime = microtime(true);

        $this->info('start');
        $bar = $this->output->createProgressBar($num_taobao_maid_old);

        while ($page <= $all_page) {

            $taobao_maid_old_datas = $obj_taobao_maid_old
                ->whereBetween('created_at', $last_month_time)
                ->where(['real' => 0])
                ->forPage(1, 10000)
                ->get();

            if (empty($taobao_maid_old_datas)) {
                break;
            }

            foreach ($taobao_maid_old_datas as $taobao_maid_old_data) {

                if ($taobao_maid_old_data->real == 1) {
                    continue;
                }
                $bar->advance();
                $taobao_maid_old_data->update(['real' => 1]);
                $father_id = $taobao_maid_old_data->father_id;
                $order_enter_id = $taobao_maid_old_data->order_enter_id;
                $trade_id = $taobao_maid_old_data->trade_id;
                $app_id = $taobao_maid_old_data->app_id;
                $group_id = $taobao_maid_old_data->group_id;
                $maid_money = $taobao_maid_old_data->maid_money;
                $type = $taobao_maid_old_data->type;
                $obj_taobao_maid = new TaobaoMaid();
                $bor_taobao_maid = $obj_taobao_maid->where(['trade_id' => $trade_id, 'app_id' => $app_id])->exists();

                if ($bor_taobao_maid) {
                    continue;
                }

                $obj_taobao_maid->create([
                    'father_id' => $father_id,
                    'order_enter_id' => $order_enter_id,
                    'trade_id' => $trade_id,
                    'app_id' => $app_id,
                    'group_id' => $group_id,
                    'maid_money' => $maid_money,
                    'type' => $type,
                ]);
                if ($type = 2) {
                    $user_order_new = new UserOrderNew();
                    $user_order_new
                        ->whereIn('status', [3, 4])
                        ->where('user_id', $app_id)
                        ->where('order_number', $trade_id)
                        ->update(['status' => 9, 'pay_time' => time(), 'update_time' => time()]);
                }
                $taobao_user = new TaobaoUser();
                $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();

                if (empty($obj_taobao_user)) {
                    $obj_taobao_user = $taobao_user->create([
                        'app_id' => $app_id,
                        'money' => $maid_money,
                        'next_money' => 0,
                        'last_money' => 0,
                    ]);
                } else {
                    $obj_taobao_user->money = $obj_taobao_user->money + $maid_money;
                    $obj_taobao_user->save();
                }

//                $taobao_change_user_log->create([
//                    'app_id' => $app_id,
//                    'before_money' => $obj_taobao_user->money - $maid_money,
//                    'before_next_money' => $maid_money,
//                    'before_last_money' => 0,
//                    'after_money' => $obj_taobao_user->money,
//                    'after_next_money' => 0,
//                    'after_last_money' => 0,
//                ]);

                $count_order_amount->isInfo($app_id, $maid_money);
            }
            $page++;
        }

        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");


        $add_w = new Add();
        $w_num_taobao_maid_old = $count_order_amount->lastAllApplyData();
        $w_all_page = ceil($w_num_taobao_maid_old / 10000);
        $w_page = 1;


        $cStartTime = microtime(true);

        $this->info('w_start');
        $bar = $this->output->createProgressBar($w_num_taobao_maid_old);

        while ($w_page <= $w_all_page) {
            $w_taobao_maid_old_datas = $count_order_amount
                ->forPage(1, 10000)
                ->get();
            if (empty($w_taobao_maid_old_datas)) {
                break;
            }
            foreach ($w_taobao_maid_old_datas as $w_data) {
                $bar->advance();
                $k = $w_data->app_id;
                $w = $w_data->maid_money;
                $taobao_user = new TaobaoUser();
                $obj_taobao_user = $taobao_user->where('app_id', $k)->first();
                $taobao_change_user_log = new TaobaoChangeUserLog();
                $taobao_change_user_log->create([
                    'app_id' => $k,
                    'before_money' => $obj_taobao_user->money,
                    'before_next_money' => $w,
                    'before_last_money' => 0,
                    'after_money' => $obj_taobao_user->money + $w,
                    'after_next_money' => 0,
                    'after_last_money' => 0,
                ]);

                $add_w->kill($k, $w);
                $count_order_amount->realDelete($k);
//                var_dump(3);
            }
//            var_dump(4);
            $w_page++;
        }
//        var_dump(2);
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n w_End Time-consuming {$consuming} s\r\n");

//        var_dump(1);
//        exit();
    }

}
