<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\Ad\RechargeOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AllErrorUserCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AllErrorUserCount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '错误用户统计';

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
        $cStartTime = microtime(true);
        $this->info('start');

        $rechargeOrder = new  RechargeOrder();
        $rechargeCreditLog = new RechargeCreditLog();
        $adUserInfo = new AdUserInfo();
        $res = $rechargeOrder->get(['orderid', 'uid', 'price'])->toArray();

        $bar = $this->output->createProgressBar(count($res));
        $money = 0;
        foreach ($res as $k => $v) {
            $v_detail = $adUserInfo->getUserById($v['uid']);

            $bar->advance();

            $sum = 0;
            $res_fenyong = $rechargeCreditLog->where(['orderid' => $v['orderid']])->get(['uid', 'money'])->toArray();
            foreach ($res_fenyong as $r => $i) {
                $i_detail = $adUserInfo->getUserById($i['uid']);
                $is_three = $adUserInfo->checkUserThreeFloor($i_detail->pt_id, $v_detail->pt_id);
                if ($is_three) {
                    if ($i_detail->groupid == 24) {
                        $sum++;
                    }
                } else {
                }
            }
            if ($sum) {
                $money = $money + $v['price'];

                Storage::disk('local')->append('callback_document/test_error.txt', var_export('------------------------------------订单号:' . $v['orderid'] . '--------', true));

                $res_fenyong_2 = $rechargeCreditLog->where(['orderid' => $v['orderid']])->get(['uid', 'money'])->toArray();

                Storage::disk('local')->append('callback_document/test_error.txt', var_export('------------------------------------充值的用户uid:' . $v['uid'] . '--------', true));

                Storage::disk('local')->append('callback_document/test_error.txt', var_export('------------------------------------充值的用户username:' . $v_detail->username . '--------', true));
                foreach ($res_fenyong_2 as $k1 => $v2) {
                    $i_detail_2 = $adUserInfo->getUserById($v2['uid']);
                    $title = "无";
                    $is_three_2 = $adUserInfo->checkUserThreeFloor($i_detail_2->pt_id, $v_detail->pt_id);
                    if ($is_three_2) {
                        $title = "直推";
                        if ($i_detail_2->groupid == 24) {
                            $title = $title . "并且是合伙人";
                        }
                    } else {
                        $title = "非直推";
                    }

                    Storage::disk('local')->append('callback_document/test_error.txt', var_export('---------------(' . $title . ')分佣用户uid：' . $i_detail_2->uid . '--------', true));

                    Storage::disk('local')->append('callback_document/test_error.txt', var_export('---------------分佣用户username：' . $i_detail_2->username . '--------', true));

                    Storage::disk('local')->append('callback_document/test_error.txt', var_export('---------------分佣用户葡萄币的数量：' . $v2['money'] . '--------', true));
                }

                Storage::disk('local')->append('callback_document/test_error.txt', var_export('=======================================================================================================', true));
            }
        }

        Storage::disk('local')->append('callback_document/test_error.txt', var_export('---------------' . $money . '--------', true));
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
    }
}
