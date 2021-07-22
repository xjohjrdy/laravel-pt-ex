<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\SpecialOption;
use App\Entitys\App\UserHigh;
use App\Services\Commands\ActiveSum;
use Illuminate\Console\Command;

class CountEverydayActiveSum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CountEverydayActiveSum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将用户所有的积分累加起来，并统计优质转正 （19-04-25版）';

    private $activeSum;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ActiveSum $activeSum)
    {
        parent::__construct();
        $this->activeSum = $activeSum;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cStartTime = microtime(true);//统计执行开始时间
        $this->info('start');

        $activeSum = $this->activeSum;

        $passUserNumber = $activeSum->countPassUser();
        //设置进度条开始状态
        $bar = $this->output->createProgressBar($passUserNumber);

        $limit = 1000;
        $page = ceil($passUserNumber / $limit);

        $appUserInfo = new AppUserInfo();
        //分片得到所有的用户
        for ($i = 0; $i < $page; $i++) {

            $arrUserInfo = $activeSum->getPassUserInfo($i * $limit, $limit);

            foreach ($arrUserInfo as $singleUserInfo) {
                //推动进度条
                $bar->advance();
                $ptId = $singleUserInfo->id;
                $arrTotalActive = $activeSum->getSingleActive($ptId);
                $numTotalActive = array_sum($arrTotalActive);//计算总活跃度

                $signActive = $arrTotalActive[1];//得到最新 签到活跃度

                //记录每日活跃度日志
                $activeSum->setActiveLog($ptId, $arrTotalActive);
                //修改用户当前活跃值，以及修改用户最新签到活跃度
                $activeSum->setUserActive($ptId, $numTotalActive, $signActive);

//                $is_level_need = $appUserInfo->where(['id' => $ptId])->first(['level']);
//                if ($is_level_need->level > 2) {
//                    //增加期权---只有实习以上等级才能有
//                    $test = new  SpecialOption();
//                    $test->addNewOption($ptId, $numTotalActive);
//                }
            }
        }

        //--------------------------------------------------------------------------------------结合----统计优质转正脚本
        $userHigh = new UserHigh();
        $user = $appUserInfo->where('active_value', '>=', config('putao.active_all_high'))->get();
        if (!$user) {
            var_dump('没有用户符合条件');
            //统计结束
            $bar->finish();
            $cEndTime = microtime(true);
            $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
            $this->info("\r\n End Time-consuming {$consuming} s\r\n");
            die();
        }
        //-------------------------------------------------正常逻辑------------------------------------
        //1号要做特殊处理
        if (date('j') == 1) {
            //上月一号0点以及月末59秒时间戳
            $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
            $today_month = date('Ym', $begin);
        } else {
            $today_month = date('Ym', time());
        }
        foreach ($user as $k => $item) {
            $user_high = $userHigh->getUserHigh($item['id']);
            if (!$user_high->remark) {
                //添加记录
                $userHigh->addLog($item['id'], $today_month);
            } else {
                $is_need_add = 0;
                $year_month = explode(',', $user_high->remark);
                foreach ($year_month as $k => $v) {
                    if ($v == $today_month) {
                        $is_need_add++;
                    }
                }
                if (!$is_need_add) {
                    //添加记录
                    $userHigh->addLog($item['id'], $today_month, $user_high->remark, 2);
                }
            }
        }

        //统计结束
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");

    }
}
