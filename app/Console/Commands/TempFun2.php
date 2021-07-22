<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Ad\VoipMoneyOrderMaid;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\JdGetOneShow;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TempFun2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:tempFun2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command TempFun';

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
        /*
        $this->info("start");
        $c_start_time = microtime(true);
        $count_number = 0;
        $bar = $this->output->createProgressBar($count_number);
        $bar->advance();
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
        */

        $this->ctHandle();


    }

    /*
     * 处理订单
     */
    public function ctHandle()
    {
        $client = new Client();
        $t = 0;
        $round = 1999;
        $this->info("start");
        $c_start_time = microtime(true);
        $count_number = $round;
        $bar = $this->output->createProgressBar($count_number);

        $new_model = new JdGetOneShow();
        for ($i = 1000; $i <= $round; $i++) {
            $bar->advance();
            $url = "https://api.91fyt.com/index.php/api/v1/hd/hdorderlistapi?pageindex=" . $i . "&pagesize=10&yn=1&memberid=1004023&hdid=7&starttime=0&endtime=1567267199&type=2";
            $res = $client->request('POST', $url, ['verify' => false]);
            $json_res = (string)$res->getBody();
            $arr_res = json_decode($json_res, true);
            foreach ($arr_res['data']['data'] as $item) {
                $one = $new_model->getOne($item['orderid']);
                if (empty($one)) {
                    $new_model->addOne($item);
                }
            }
        }
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");

        var_dump($t);
        exit();
    }

    /*
     * 处理京东相关业务
     */
    public function jdHandle()
    {
        $this->info("start");
        $c_start_time = microtime(true);


        $new_model = new JdGetOneShow();
        $all = $new_model->where(['is_ptb' => 0])->get();
        $count_number = $all->count();
        $bar = $this->output->createProgressBar($count_number);

        $app_user_info = new AppUserInfo();
        $ad_user_info = new AdUserInfo();
        $user_account = new UserAccount();
        $credit_log = new UserCreditLog();
        $about_log = new  UserAboutLog();

        foreach ($all as $one) {
            $bar->advance();

            if (!empty($one->is_ptb)) {
                continue;
            }

            $user_info_tall = $app_user_info->getUserById($one->subunionid);
            if (empty($user_info_tall)) {
                var_dump("未匹配到对应id的数据");
                var_dump($one->subunionid);
                continue;
            }
            $ad_user_info_tall = $ad_user_info->appToAdUserId($one->subunionid);
            if (empty($ad_user_info_tall)) {
                var_dump("未匹配到对应uid的数据");
                var_dump($one->subunionid);
                continue;
            }
            $ad_user_info_uid_tall = $ad_user_info_tall->uid;
            $account_tall = $user_account->getUserAccount($ad_user_info_uid_tall);
            $res_account_tall = $user_account->addUserPTBMoney(99, $ad_user_info_uid_tall);
            $insert_id = $credit_log->addLog($ad_user_info_uid_tall, "JDX", ['extcredits4' => 99]);
            $extcredits4_change_tall = $account_tall->extcredits4 + 99;
            $about_log->addLog($insert_id, $ad_user_info_uid_tall, $ad_user_info_tall->username, $ad_user_info_tall->pt_id, ["extcredits4" => $account_tall->extcredits4], ["extcredits4" => $extcredits4_change_tall]);

            $new_model->updateOne($one->orderid);
        }
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");

    }

    public function orderHandle()
    {
        $this->info("start");
        $c_start_time = microtime(true);

        $m_user = new AppUserInfo();

        $count_number = $m_user
            ->where('bonus_amount', '>', 0)
            ->orWhere('order_amount', '>', 0)
            ->count();
        $bar = $this->output->createProgressBar($count_number);

        $page_size = 10000;


        $taobao_change_user_log = new TaobaoChangeUserLog();

        while (true) {
            $list_user_info = $m_user
                ->where('bonus_amount', '>', 0)
                ->orWhere('order_amount', '>', 0)
                ->limit($page_size)
                ->get(['id', 'bonus_amount', 'order_amount', 'apply_cash_amount', 'order_can_apply_amount']);

            if ($list_user_info->isEmpty()) {
                $this->info("空数据");
                break;
            }

            foreach ($list_user_info as $item_user) {
                $bar->advance();

                $app_id = $item_user->id;
                $bonus_amount = (float)$item_user->bonus_amount;
                $order_amount = (float)$item_user->order_amount;

                $maid_money = $bonus_amount + $order_amount;
                $taobao_user = new TaobaoUser();
                $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();

                $now_money = 0;

                if (empty($obj_taobao_user)) {
                    $obj_taobao_user = $taobao_user->create([
                        'app_id' => $app_id,
                        'money' => $maid_money,
                        'next_money' => 0,
                        'last_money' => 0,
                    ]);

                } else {
                    $now_money = $obj_taobao_user->money;
                    $obj_taobao_user->money = $obj_taobao_user->money + $maid_money;
                    $obj_taobao_user->save();
                }

                if (!empty($bonus_amount)) {
                    $taobao_change_user_log->create([
                        'app_id' => $app_id,
                        'before_money' => $now_money,
                        'before_next_money' => $bonus_amount,
                        'before_last_money' => 0,
                        'after_money' => $now_money + $bonus_amount,
                        'after_next_money' => 0,
                        'after_last_money' => 0,
                        'from_type' => 4,
                    ]);

                    $now_money += $bonus_amount;
                }

                if (!empty($order_amount)) {
                    $taobao_change_user_log->create([
                        'app_id' => $app_id,
                        'before_money' => $now_money,
                        'before_next_money' => $order_amount,
                        'before_last_money' => 0,
                        'after_money' => $now_money + $order_amount,
                        'after_next_money' => 0,
                        'after_last_money' => 0,
                        'from_type' => 3,
                    ]);
                }

                $item_user->order_amount = 0;
                $item_user->bonus_amount = 0;
                $item_user->apply_cash_amount = $item_user->apply_cash_amount - $order_amount - $bonus_amount;
                $item_user->order_can_apply_amount = $item_user->order_can_apply_amount - $order_amount;
                $item_user->save();

            }

        }
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
    }


}
