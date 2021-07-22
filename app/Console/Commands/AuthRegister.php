<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Services\Wechat\Wechat;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class AuthRegister extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AuthRegister';

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
        $this->info("start");
        $obj_app_user_info = new AppUserInfo();
        $phone_prefix = time();
        for ($i = 1; $i <= 20; $i++) {

            $phone = $phone_prefix . sprintf("%02d", $i);
            $this->info('phone:' . $phone);

            try {
                $obj_app_user_info->insert([
                    "user_name" => $phone,
                    "real_name" => "",
                    "avatar" => "",
                    "phone" => $phone,
                    "password" => bcrypt('pt' . $phone),
                    "alipay" => "",
                    "level" => 1,
                    "parent_id" => 0,
                    "up_three_floor" => "",
                    "up_four_floor" => "",
                    "status" => 1,
                    "create_time" => time(),
                    "active_value" => 0,
                    "append_active_value" => 0,
                    "sign_active_value" => 0,
                    "order_num_active_value" => 0,
                    "history_active_value" => 0,
                    "bonus_amount" => 0,
                    "order_amount" => 0,
                    "apply_cash_amount" => 0,
                    "next_month_cash_amount" => 0,
                    "current_month_passed_order" => 0,
                    "order_can_apply_amount" => 0,
                    "sign_number" => 0,
                    "level_modify_time" => time(),
                    "apply_status" => 2,
                    "device" => 'pt',
                ]);
            } catch (\Exception $e) {
                $this->error('error:' . $phone);
                continue;
            }


        }
        $this->info("end");
    }
}
