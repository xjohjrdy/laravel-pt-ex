<?php

namespace App\Console\Commands;

use App\Entitys\App\ActiveCount;
use App\Entitys\App\AppUserInfo;
use App\Services\Commands\CountEverydayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CountEverydayActiveTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CountEverydayActiveTemp';

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
        $this->info('★★★★★ Start ★★★★★');
        $obj_order = new CountEverydayService();
        /****************= 统计每日活跃度存入lc_active_count表 =******************/
        list($begin_time_stamp, $end_time_stamp) = $obj_order->getLeadTimeStamp();
        list($begin_time_string, $end_time_string) = $obj_order->getLeadTimeString();

        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $c_start_time = microtime(true);


        $pass_user_number = $obj_order->countPassUser();
        $bar = $this->output->createProgressBar($pass_user_number);

        $limit = 10000;
        $page = ceil($pass_user_number / $limit);

        if (!$obj_order->clearActive(3))
            $this->error("Active clearing failed");

        if (!$obj_order->clearActive(4))
            $this->error("Active clearing failed");

        if (!$obj_order->clearActive(5))
            $this->error("Active clearing failed");

        if (!$obj_order->clearActive(6))
            $this->error("Active clearing failed");

        if (!$obj_order->clearActive(0))
            $this->error("Active clearing failed");

        if (!$obj_order->clearActive(2))
            $this->error("Active clearing failed");

        if (!$obj_order->clearActive(7))
            $this->error("Active clearing failed");

        if (!$obj_order->clearActive(99))
            $this->error("Active clearing failed");

        $date = date('Ymd');

        for ($i = 0; $i < $page; $i++) {

            $arr_user_info = $obj_order->getPassUserInfo($i * $limit, $limit);
            Storage::disk('local')->append('callback_document/active_001/temp_' . $date . '.txt', date('H:i:s') . '#### ' . var_export($i . '--' . $pass_user_number, true) . ' ####');
            foreach ($arr_user_info as $single_id) {
                $bar->advance();
                $num_app_id = $single_id->id;
                $num_sign_active_value = $single_id->sign_active_value;
                $num_append_active_value = $single_id->append_active_value;

                Storage::disk('local')->append('callback_document/active_001/temp_foreach_' . $date . '.txt', date('H:i:s') . '#### ' . var_export($i . '--' . $num_app_id, true) . ' ####');


                # 测试1694511用户，除了该用户其他用户先全部跳过


                /**********= 统计团队签到活跃度 type:3 =**********/
                //开始测试
                //测试通过
//                $count_group_number = $obj_order->getGroupNumber($num_app_id);//统计团队人数(自己和直推的人数) 最低 1
//                $count_sign_number = $obj_order->getSignNumber($num_app_id, $yesterday);//得到团队已经签到的用户人数 最低0
//                $headcount = $count_group_number > 50 ? $count_group_number : 50;//团队总人数如果小于50
//                $active_value = round($count_sign_number / $headcount, 2);
//                $active_value += $num_sign_active_value;//用户当前活跃度+用户累加活跃度
//                $obj_order->addUserActive($num_app_id, 3, $active_value);//插入类型为3(团队签到活跃度)的活跃度记录

                /**********= 统计团队签到活跃度-新规则 type:3 =**********/
                $count_sign_number = $obj_order->getSignNumber($num_app_id, $yesterday);//得到团队已经签到的用户人数 最低0
                $active_value = round($count_sign_number * 0.015, 3);
                $active_value = $active_value > 0.75 ? 0.75 : $active_value;
				$active_value += $num_sign_active_value;//用户当前活跃度+用户累加活跃度
                $obj_order->addUserActive($num_app_id, 3, $active_value);//插入类型为3(团队签到活跃度)的活跃度记录

                /**********= 统计团队报销活跃度 type:4 =**********/
                $group_order_account_number = $obj_order->getGroupOrderAccount($num_app_id, $begin_time_stamp, $end_time_stamp);
                $active_value = round($group_order_account_number / 10, 2);
                $group_order_account_number_new = $obj_order->getGroupOrderAccountNew($num_app_id, $begin_time_stamp, $end_time_stamp);
                $active_value_new = round($group_order_account_number_new / 10, 2);
                $pdd_group_order_account_number = $obj_order->getGroupOrderAccountPdd($num_app_id, $begin_time_string, $end_time_string);
                $pdd_active_value = round($pdd_group_order_account_number / 1000, 2);
                $jd_group_order_account_number = $obj_order->getGroupOrderAccountJd($num_app_id, $begin_time_stamp .'000', $end_time_stamp . '000');
                $jd_active_value = round($jd_group_order_account_number / 10, 2);

                $obj_order->addUserActive($num_app_id, 4, $active_value + $active_value_new + $pdd_active_value + $jd_active_value);


                /**********= 统计团队拉人活跃度 type:5 =**********/
                $register_number = $obj_order->getRegisterNumber($num_app_id, $begin_time_stamp, $end_time_stamp);
                $active_value = $register_number;
                $obj_order->addUserActive($num_app_id, 5, $active_value);

                /**********= 统计我的商城活跃度 type:6 =**********/
                $monetary = $obj_order->getMonetary($num_app_id, $begin_time_string, $end_time_string);
                $active_value = round($monetary * 0.05, 2);
                $obj_order->addUserActive($num_app_id, 6, $active_value);

                /**********= 统计开通代理商订单 type:0 (此处是根据广告联盟的上下级关系) =**********/
                $agent_order_number = $obj_order->getOrderActive($num_app_id, $begin_time_stamp, $end_time_stamp);
                $active_value = $agent_order_number * 2;
                $obj_order->addUserActive($num_app_id, 0, $active_value);

                /**********= 统计我的通讯活跃度 type:2 =**********/
                $active_value = 0;
                $obj_order->addUserActive($num_app_id, 2, $active_value);

                /**********= 统计圈子购买活跃度 type:7 (不包括竞价) =**********/
                $circle_number = $obj_order->getCircleNumber($num_app_id, $begin_time_string, $end_time_string);
                $active_value = $circle_number * 2;
                $obj_order->addUserActive($num_app_id, 7, $active_value);

                /**********= 统计用户附加活跃值 type:99 =**********/
                $active_value = $num_append_active_value;
                $obj_order->addUserActive($num_app_id, 99, $active_value);

            }

        }
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");

        $this->info('★★★★★ end ★★★★★');


        /******************************/

        try {
            $s_time_count = strtotime(date('Y-m-d', strtotime('yesterday')));
            $e_time_count = strtotime(date('Y-m-d'));
            $user_count_list = ActiveCount::whereBetween('update_time', [$s_time_count, $e_time_count])->where('type', 0)->pluck('pt_id');
            foreach ($user_count_list as $item) {

                if ($obj_order->again($item)) {
                    $log_user = $item . '遗漏用户，待排查。';
                } else {
                    $log_user = $item . ':注销冻结或者不存在';
                }

                Storage::disk('local')->append('callback_document/active_001/temp_err_' . $date . '.txt', date('H:i:s') . '#### ' . var_export($log_user, true) . ' ####');;
            }

        } catch (\Exception $e) {
            Storage::disk('local')->append('callback_document/active_001/temp_err_' . $date . '.txt', date('H:i:s') . '#### ' . var_export($e->getMessage() . '---' . $e->getFile(), true) . ' ####');
        }

    }
}
