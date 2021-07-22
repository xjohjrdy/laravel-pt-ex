<?php

namespace App\Console\Commands;

use App\Entitys\App\EleMaid;
use App\Entitys\App\EleMaidOld;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use App\Services\Common\Time;
use App\Services\Other\ShopCommissionService;
use App\Services\WuHang\Add;
use Illuminate\Console\Command;

class EleCountOrderAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EleCountOrderAmount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每月24日0点，计算[订单返现金额] 和 [可提现金额]';

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
        //得到上月时间戳范围
        $obj_timestamp = new Time();
        $last_month = $obj_timestamp->getLastMonthTimestamp();

        //基础查询条件,上月时间范围内,过审订单
        //得到上月全部假表报销记录
        $obj_ele_maid_old = new EleMaidOld();
        //全部订单记录数量
        $last_month_time = [date('Y-m-d H:i:s', $last_month[0]), date('Y-m-d H:i:s', $last_month[1])];
        $num_ele_maid_old = $obj_ele_maid_old->lastAllApplyData($last_month_time);

        //总页数
        $all_page = ceil($num_ele_maid_old / 10000);
        $page = 1;
        $taobao_change_user_log = new TaobaoChangeUserLog();//记录日志表

        $cStartTime = microtime(true);//统计执行开始时间

        $this->info('start');
        //设置进度条开始状态
        $bar = $this->output->createProgressBar($num_ele_maid_old);

        $w_arr = [];
        /**
         * [
         *  'app_id'=>'xxx',
         * ]
         */
        while ($page <= $all_page) {

            $ele_maid_old_datas = $obj_ele_maid_old
                ->whereBetween('created_at', $last_month_time)
                ->where(['real' => 0])
                ->forPage(1, 10000)
                ->get(); //取 上个月 的一万条数据， real 为 0 的

            if (empty($ele_maid_old_datas)) {
                break;
            }

            foreach ($ele_maid_old_datas as $ele_maid_old_data) {

                if ($ele_maid_old_data->real == 1) { //后续调试使用
                    continue;
                }

                //推动进度条
                $bar->advance();
                //修改假表real
                $ele_maid_old_data->update(['real' => 1]);  //单条遍历处理
                //取假表所有字段数据
                $father_id = $ele_maid_old_data->father_id;
                $order_enter_id = $ele_maid_old_data->order_enter_id;
                $trade_id = $ele_maid_old_data->trade_id;
                $app_id = $ele_maid_old_data->app_id;
                $group_id = $ele_maid_old_data->group_id;
                $maid_money = $ele_maid_old_data->maid_money;
                $type = $ele_maid_old_data->type;
                $obj_ele_maid = new EleMaid(); //分佣真表
                //判断真表里面是否有数据
                $bor_ele_maid = $obj_ele_maid->where(['trade_id' => $trade_id, 'app_id' => $app_id])->exists();

                if ($bor_ele_maid) {
                    continue;
                }

                $obj_ele_maid->create([
                    'father_id' => $father_id,
                    'order_enter_id' => $order_enter_id,
                    'trade_id' => $trade_id,
                    'app_id' => $app_id,
                    'group_id' => $group_id,
                    'maid_money' => $maid_money,
                    'type' => $type,
                ]);

                //给用户添加分佣的钱
                $taobao_user = new TaobaoUser(); //用户真实分佣表
                $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();

                if (empty($obj_taobao_user)) {
                    $obj_taobao_user = $taobao_user->create([
                        'app_id' => $app_id,
                        'money' => $maid_money,
                        'next_money' => 0,
                        'last_money' => 0,
                    ]);
                } else {
                    $obj_taobao_user->money = $obj_taobao_user->money + $maid_money;
                    $obj_taobao_user->save();
                }

//                //添加日志记录
//                $taobao_change_user_log->create([
//                    'app_id' => $app_id,
//                    'before_money' => $obj_taobao_user->money - $maid_money, //变化前
//                    'before_next_money' => $maid_money,  //变化的值
//                    'before_last_money' => 0,
//                    'after_money' => $obj_taobao_user->money,   //变化后
//                    'after_next_money' => 0,
//                    'after_last_money' => 0,
//                    'from_type' => '70',
//                    'from_info' => 'ELE',
//                ]);

                if (empty($w_arr[$app_id])) {
                    $w_arr[$app_id] = $maid_money;
                } else {
                    $w_arr[$app_id] = $w_arr[$app_id] + $maid_money;
                }

            }
            $page++;
        }


        $add_w = new Add();

        foreach ($w_arr as $k => $w) {
            $taobao_user = new TaobaoUser();
            $obj_taobao_user = $taobao_user->where('app_id', $k)->first();
            $taobao_change_user_log = new TaobaoChangeUserLog();
            $taobao_change_user_log->create([
                'app_id' => $k,
                'before_money' => $obj_taobao_user->money,
                'before_next_money' => $w,
                'before_last_money' => 0,
                'after_money' => $obj_taobao_user->money + $w,
                'after_next_money' => 0,
                'after_last_money' => 0,
                'from_type' => '70',
                'from_info' => 'ELE',
            ]);


            $add_w->kill($k, $w);
        }


        //处理管理费
//        $this->info('报销处理完,管理费处理中!!!');
//        $obj_Shop_commission_service = new ShopCommissionService();
//        $obj_Shop_commission_service->eleMoreCommissionAddMoney($last_month_time);

        //统计结束
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");

    }

}
