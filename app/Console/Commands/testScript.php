<?php

namespace App\Console\Commands;

use App\Services\Commands\CountActiveness;
use Illuminate\Console\Command;

class testScript extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testScript';

    protected $countActiveness;
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
        $this->info("hello");

        $numUserId = 1519092;
        $objOrder = $this->countActiveness;

        list($beginTime, $endTime) = $objOrder->getLeadTime();

        $strBeginTime = date('Y-m-d H:i:s', $beginTime);
        $strEndTime = date('Y-m-d H:i:s', $endTime);

        $monetary = $objOrder->getMonetary($numUserId, $strBeginTime, $strEndTime);

        if (!empty($monetary)) {
            $activeValue = round($monetary * 0.03, 2);
            $objOrder->addUserActive($numUserId, 6, $activeValue);
            $this->info($activeValue);
        }else{
            $this->info("no");
        }



        $this->info("end");
    }
}
