<?php

namespace App\Console\Commands;

use App\Entitys\App\PddMaid;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use App\Services\Common\Time;
use App\Services\WuHang\Add;
use Illuminate\Console\Command;

class PddCountOrderAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PddCountOrderAmount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每月25日执行，拼多多分佣计算!';

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
        $obj_pdd_maid_old = new PddMaidOld();
        $last_month_time = [date('Y-m-d H:i:s', $last_month[0]), date('Y-m-d H:i:s', $last_month[1])];
        $num_pdd_maid_old = $obj_pdd_maid_old->lastAllApplyData($last_month_time);
        $all_page = ceil($num_pdd_maid_old / 10000);
        $page = 1;
        $taobao_change_user_log = new TaobaoChangeUserLog();

        $cStartTime = microtime(true);

        $this->info('start');
        $bar = $this->output->createProgressBar($num_pdd_maid_old);
        $w_arr = [];
        /**
         * [
         *  'app_id'=>'xxx',
         * ]
         */
        while ($page <= $all_page) {

            $pdd_maid_old_datas = $obj_pdd_maid_old
                ->whereBetween('created_at', $last_month_time)
                ->where(['real' => 0])
                ->forPage(1, 10000)
                ->get();

            if (empty($pdd_maid_old_datas)) {
                break;
            }

            foreach ($pdd_maid_old_datas as $pdd_maid_old_data) {

                if ($pdd_maid_old_data->real == 1) {
                    continue;
                }
                $bar->advance();
                $pdd_maid_old_data->update(['real' => 1]);
                $father_id = $pdd_maid_old_data->father_id;
                $trade_id = $pdd_maid_old_data->trade_id;
                $app_id = $pdd_maid_old_data->app_id;
                $group_id = $pdd_maid_old_data->group_id;
                $maid_money = $pdd_maid_old_data->maid_money;
                $type = $pdd_maid_old_data->type;
                $obj_pdd_maid = new PddMaid();
                $bor_pdd_maid = $obj_pdd_maid->where(['trade_id' => $trade_id, 'app_id' => $app_id])->exists();

                if ($bor_pdd_maid) {
                    continue;
                }

                $obj_pdd_maid->create([
                    'father_id' => $father_id,
                    'trade_id' => $trade_id,
                    'app_id' => $app_id,
                    'group_id' => $group_id,
                    'maid_money' => $maid_money,
                    'type' => $type,
                ]);
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
//                    'from_type' => 9,
//                ]);

                if (empty($w_arr[$app_id])) {
                    $w_arr[$app_id] = $maid_money;
                } else {
                    $w_arr[$app_id] = $w_arr[$app_id] + $maid_money;
                }
            }
            $page++;
        }

        $add_w = new Add();

        foreach ($w_arr as $k => $w) {
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
                'from_type' => 9,
            ]);

            $add_w->kill($k, $w);
        }


        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");

    }
}
