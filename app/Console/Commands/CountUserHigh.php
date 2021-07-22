<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\UserHigh;
use Illuminate\Console\Command;

class CountUserHigh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CountUserHigh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        var_dump(1);
        exit();
        $cStartTime = microtime(true);
        $this->info('start');


        $appUserInfo = new AppUserInfo();
        $userHigh = new UserHigh();
        $user = $appUserInfo->where('active_value', '>=', config('putao.active_all_high'))->get();
        if (!$user) {
            var_dump('没有用户符合条件');
            die();
        }
        $bar = $this->output->createProgressBar($user->count());
        foreach ($user as $k => $item) {
            $bar->advance();
            $user_high = $userHigh->getUserHigh($item['id']);
            if (!$user_high->remark) {
                $userHigh->addLog($item['id'], date('Ym', time()));
            } else {
                $is_need_add = 0;
                $year_month = explode(',', $user_high->remark);
                foreach ($year_month as $k => $v) {
                    if ($v == date('Ym', time())) {
                        $is_need_add++;
                    }
                }
                if (!$is_need_add) {
                    $userHigh->addLog($item['id'], date('Ym', time()), $user_high->remark, 2);
                }
            }
        }
        /****************************/
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
    }
}
