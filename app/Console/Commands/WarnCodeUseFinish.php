<?php

namespace App\Console\Commands;

use App\Entitys\App\VoipCards;
use App\Services\Common\CommonFunction;
use App\Services\Common\Sms;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class WarnCodeUseFinish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:WarnCodeUseFinish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检测是否消耗殆尽';

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
        $client = new Client();
        $res_api = $client->request('get','http://phone.voiper.cn:8585/api/user',[
            'headers' => [
                'Authorization'=>'bearer 14ac73b8-e0d4-40dc-930b-e3de7c9b81cf',
            ]
        ]);

        $jsonRes = (string)$res_api->getBody();
        $arrRes = json_decode($jsonRes, true);
        if (!empty($arrRes['banlance'])){
            $sms = new Sms();
            $function = new  CommonFunction();
            $test = $function->sendWarnSms(13194089498, $sms,'通讯余额提示' ,$arrRes['banlance']);
            if ($arrRes['banlance'] < 80) {
                $test = $function->sendWarnSms(13194089498, $sms,'余额通知（紧急！）当前余额：' ,$arrRes['banlance']);
                $test = $function->sendWarnSms(18805029611, $sms,'余额通知（紧急！）当前余额：' ,$arrRes['banlance']);
                $test = $function->sendWarnSms(15980277249, $sms,'余额通知（紧急！）当前余额：' ,$arrRes['banlance']);
            }
        }
    }
}
