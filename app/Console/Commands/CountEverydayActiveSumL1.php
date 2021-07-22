<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\SpecialOption;
use App\Entitys\App\UserHigh;
use App\Services\Commands\ActiveSum;
use Illuminate\Console\Command;

class CountEverydayActiveSumL1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CountEverydayActiveSumL1';

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
        $cStartTime = microtime(true);
        $this->info('start');

        $activeSum = $this->activeSum;

        $passUserNumber = $activeSum->countPassUserL1();
        $bar = $this->output->createProgressBar($passUserNumber);

        $limit = 10000;
        $page = ceil($passUserNumber / $limit);
        for ($i = 0; $i < $page; $i++) {

            $arrUserInfo = $activeSum->getPassUserInfoL1($i * $limit, $limit);

            foreach ($arrUserInfo as $singleUserInfo) {
                $bar->advance();
                $ptId = $singleUserInfo->id;
                $arrTotalActive = $activeSum->getSingleActive($ptId);
                $numTotalActive = array_sum($arrTotalActive);

                $signActive = $arrTotalActive[1];
                $activeSum->setActiveLog($ptId, $arrTotalActive);
                $activeSum->setUserActive($ptId, $numTotalActive, $signActive);
            }
        }
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");

    }
}
