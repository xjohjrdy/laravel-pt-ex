<?php

namespace App\Console\Commands;

use App\Services\Commands\CountActiveness;
use Illuminate\Console\Command;

class CountUserActiveTempTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CountUserActiveTempTest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $countActiveness;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CountActiveness $countActiveness)
    {
        parent::__construct();
        $this->countActiveness = $countActiveness;

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $objOrder = $this->countActiveness;
        $this->info('start');
        /****************= 统计团队签到率、报销、推广人数活跃度 =******************/
        list($beginTime,$endTime) = $objOrder->getLeadTime();
        $yesterday = date('Y-m-d',strtotime('yesterday'));;
        $cStartTime = microtime(true);
        $passUserNumber = $objOrder->countPassUser();
        $bar = $this->output->createProgressBar($passUserNumber);

        $limit = 1000;
        $page =  ceil($passUserNumber/$limit);
        if (!$objOrder->clearActive(3))
            $this->error("Active clearing failed");
        if (!$objOrder->clearActive(4))
            $this->error("Active clearing failed");
        if (!$objOrder->clearActive(5))
            $this->error("Active clearing failed");
        if (!$objOrder->clearActive(6))
            $this->error("Active clearing failed");
        for ($i=0;$i<$page;$i++){

            $arrUserInfo = $objOrder->getPassUserInfo($i*$limit,$limit);
            foreach ($arrUserInfo as $singleUserInfo){
                $bar->advance();

                $numUserId = $singleUserInfo->id;
                $countGroupNumber = $objOrder->getGroupNumber($numUserId);
								

                /*********************= 统计签到率活跃度 =***********************/

                $countSignNumber = $objOrder->getSignNumber($numUserId,$yesterday);

                if (!empty($countSignNumber)){
                    $headcount = $countGroupNumber>50?$countGroupNumber:50;
                    $activeValue = round($countSignNumber/$headcount,2);
                    $objOrder->addUserActive($numUserId,3,$activeValue);
                }

                /*********************= 统计团队报销活跃度 =***********************/

                $groupOrderAccountNumber = $objOrder->getGroupOrderAccount($numUserId,$beginTime,$endTime);
                if (!empty($groupOrderAccountNumber)){
                    $activeValue = round($groupOrderAccountNumber/10,1);
                    $objOrder->addUserActive($numUserId,4,$activeValue);
                }

                /*********************= 统计团队拉人活跃度 =***********************/

                $registerNumber = $objOrder->getRegisterNumber($numUserId,$beginTime,$endTime);

                if (!empty($registerNumber)){
                    $activeValue = $registerNumber;
                    $objOrder->addUserActive($numUserId,5,$activeValue);
                }
				
				/*********************= 统计葡萄商城活跃度 =***********************/
                $strBeginTime = date('Y-m-d H:i:s', $beginTime);
                $strEndTime = date('Y-m-d H:i:s', $endTime);

                $monetary = $objOrder->getMonetary($numUserId, $strBeginTime, $strEndTime);

                if (!empty($monetary)) {
                    $activeValue = round($monetary * 0.03, 2);
                    $objOrder->addUserActive($numUserId, 6, $activeValue);
                }

            }

        }
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S',round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
    }
}
