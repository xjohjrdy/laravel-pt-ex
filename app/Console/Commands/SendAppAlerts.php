<?php

namespace App\Console\Commands;

use App\Services\Common\AppAlerts;
use Illuminate\Console\Command;

class SendAppAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SendAppAlerts';

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
        $s_app_alerts = new AppAlerts();

        if ($s_app_alerts->isLock()) {
            $this->info('waiting...');
            exit();
        }

        $s_app_alerts->look();

        $count = 0;
        while ($s_app_alerts->isLock()) {


            $alert_msg = $s_app_alerts->getAlert();

            if (empty($alert_msg)) {
                $this->info('本次处理通知：' . $count);
                exit();
            }

            //TODO 改为友盟推送
            $s_app_alerts->mPush($alert_msg);
            $count++;
        }
    }

}
