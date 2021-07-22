<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Services\Common\UserMoney;
use Illuminate\Console\Command;

class AutoPayOff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AutoPayOff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动发工资功能';


    protected $aboutLog;
    protected $creditLog;
    protected $userAccount;
    protected $adUserInfo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserAboutLog $aboutLog, UserCreditLog $creditLog, UserAccount $userAccount, AdUserInfo $adUserInfo)
    {
        parent::__construct();
        $this->aboutLog = $aboutLog;
        $this->creditLog = $creditLog;
        $this->userAccount = $userAccount;
        $this->adUserInfo = $adUserInfo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //off
        var_dump(1);
        exit();

        //防止以后还有类似于发工资的功能，这边写一个发工资脚本
        $add_user_info = [
            [
                'app_id' => '3048',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '577584',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '6425',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1442883',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1350629',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1440988',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '256648',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2050931',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '254282',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1354616',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '253113',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2076465',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '3082479',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2033820',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2076964',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '77812',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2029357',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2074889',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1462846',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2246797',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2120360',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1746108',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2102469',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1427222',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '4371523',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1354455',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '5879368',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1129558',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2076530',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '2077619',
                'add_money' => '500',
                'type' => '405'
            ],
            [
                'app_id' => '1459216',
                'add_money' => '500',
                'type' => '405'
            ],
        ];

        $money = new UserMoney();
        foreach ($add_user_info as $value) {
            $user = $this->adUserInfo->appToAdUserId($value['app_id']);
            if (empty($user)) {
                var_dump(1);
                continue;
            }

            $add_price_ptb = $value['add_money'];

            if ($add_price_ptb > 0) {
                $money->plusCnyAndLog($value['app_id'], $add_price_ptb, $value['type']);
            }
        }
        var_dump(5);
        exit();
    }
}
