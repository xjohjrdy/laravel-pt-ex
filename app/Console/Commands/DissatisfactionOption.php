<?php

namespace App\Console\Commands;

use App\Entitys\App\SpecialOption;
use Illuminate\Console\Command;

class DissatisfactionOption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DissatisfactionOption';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用于清除3个月没有活跃值>10的用户期权值（每个月运行一次，运行时间为月中旬）';

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
        $test = new  SpecialOption();
        $special_option = $test->getALlUser();
        $bar = $this->output->createProgressBar($special_option->count());
        foreach ($special_option as $value) {
            $bar->advance();

            $time_one = strtotime(date("Y-m-01 00:00:00", strtotime("-1 month")));
            $time_two = strtotime(date("Y-m-01 00:00:00", strtotime("-2 month")));
            $time_three = strtotime(date("Y-m-01 00:00:00", strtotime("-3 month")));
            $time_four = strtotime(date("Y-m-01 00:00:00", strtotime("-4 month")));
            $time_five = strtotime(date("Y-m-01 00:00:00", strtotime("-5 month")));

            $time_now = strtotime(date("Y-m-01", time()));

            $user_option = $test
                ->where(['app_id' => $value->app_id])
                ->whereIn('compute_time', [
                    $time_one,
                    $time_two,
                    $time_three,
                    $time_four,
                    $time_five
                ])
                ->get();
            $no_clear = 0;
            $no_jump = $test
                ->where([
                    'app_id' => $value->app_id
                ])
                ->count();
            if ($no_jump == 1) {
                $no_clear = 1;
            }
            $no_jump_three = $test
                ->where([
                    'app_id' => $value->app_id,
                    'compute_time' => $time_three
                ])
                ->first();
            if (empty($no_jump_three)) {
                $no_clear = 1;
            }


            foreach ($user_option as $item) {
                if ($item->active_value >= 10) {
                    $no_clear = 1;
                    break;
                }
            }


            if (empty($no_clear)) {
                var_dump($value->app_id);
                $test->clearUserOption($value->app_id);
            }
        }
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
    }
}
