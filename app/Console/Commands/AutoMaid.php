<?php

namespace App\Console\Commands;

use App\Entitys\Other\ManagerMaidAutoList;
use App\Entitys\Other\ManagerPretendMaid;
use App\Entitys\OtherOut\AppUserInfoOut;
use App\Services\Qmshida\OtherUserMoneyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoMaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AutoMaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command 自动分佣处理25日经理佣金';

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
//
//        $res = DB::connection('app38_out')->select(
//            "select * from lc_user WHERE id = 1"
//        );
//
//        var_dump($res);
//        exit();

        //

        /**
         * 1.筛选目标用户，经理级别
         * 2.把目标用户lc_manager_pretend_maid的记录
         * 3.上个月活跃值>=60，记录status=1
         * 3.1加余额
         * 4.上个月活跃值<60，记录status=2（失效状态）
         */

        $t_last = strtotime('-1 month');
        $t_begin = date('Y-m-01 00:00:00', $t_last);
        $t_end = date('Y-m-t 23:59:59', $t_last);

        $app_user = new AppUserInfoOut();
        $manage_pretend_maid = new ManagerPretendMaid();
        $manage_maid_auto_list = new ManagerMaidAutoList();
        $other_user_money_service = new OtherUserMoneyService();

        $all_user = $app_user->where(['level' => 4])->get();

        $pass_user_number = $all_user->count();//得到需要统计的用户总数
        $bar = $this->output->createProgressBar($pass_user_number);//设置进度条开始状态
        foreach ($all_user as $user) {

            //拦截非测试用户
//            if ($user->id <> 9873668) {
//                if ($user->id <> 4446218) {
//                    continue;
//                }
//            }

            $bar->advance();//推动进度条


            $all_pretend_money = $manage_pretend_maid->where([
                'status' => 0,
                'app_id' => $user->id
            ])->whereBetween('created_at', [$t_begin, $t_end])->sum('money');
            if ($user->history_active_value >= 60) {

                var_dump($user->id);
                var_dump($all_pretend_money);
                var_dump(1);
                //加钱
                $other_user_money_service->plusThreeUserMoney($user->id, $all_pretend_money, 41, 'WHE');

                $manage_pretend_maid->where([
                    'status' => 0,
                    'app_id' => $user->id
                ])->whereBetween('created_at', [$t_begin, $t_end])->update([
                    'status' => 1
                ]);

                $manage_maid_auto_list->create([
                    'app_id' => $user->id,
                    'from_info' => date('m'),
                    'money' => $all_pretend_money,
                    'status' => 1,
                ]);
            }

            if ($user->history_active_value < 60) {

                var_dump($user->id);
                var_dump(2);
                $manage_pretend_maid->where([
                    'status' => 0,
                    'app_id' => $user->id
                ])->whereBetween('created_at', [$t_begin, $t_end])->update([
                    'status' => 2
                ]);

                $manage_maid_auto_list->create([
                    'app_id' => $user->id,
                    'from_info' => date('m'),
                    'money' => $all_pretend_money,
                    'status' => 0,
                ]);
            }
        }

        //统计结束
        $bar->finish();
        var_dump(1);
        exit();
    }
}
