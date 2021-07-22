<?php

namespace App\Console\Commands;

use App\Entitys\App\AdvertisementClickOnly;
use App\Entitys\App\SignLog;
use Illuminate\Console\Command;

class ClearSign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ClearSign';

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
     * @throws \Exception
     */
    public function handle()
    {
        //获取90天前时间
        $date = date("Y-m-d", strtotime("-90 day"));
        $num = SignLog::where('date', '<', $date)->limit(10000)->delete();
        $this->info($num); // 4547953

        //删除签到相关设备信息记录
        $num = AdvertisementClickOnly::where('created_at', '<', $date)->limit(10000)->forceDelete();
        $this->info($num);
    }
}
