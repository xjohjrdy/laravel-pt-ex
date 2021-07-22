<?php

namespace App\Console\Commands;

use App\Entitys\Other\MtMaidOldOther;
use App\Entitys\Other\MtMaidOther;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeEleMaid;
use App\Entitys\Other\ThreeEleMaidOld;
use App\Entitys\Other\ThreeUser;
use App\Services\Common\Time;
use Illuminate\Console\Command;

class OtherMtCountOrderAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OtherMtCountOrderAmount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每月25日0点，计算[订单返现金额] 和 [可提现金额] 美团';

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
        $obj_ele_maid_old = new MtMaidOldOther();
        //全部订单记录数量
        $last_month_time = [date('Y-m-d H:i:s', $last_month[0]), date('Y-m-d H:i:s', $last_month[1])];
        $num_ele_maid_old = $obj_ele_maid_old->lastAllApplyData($last_month_time);

        //总页数
        $all_page = ceil($num_ele_maid_old / 10000);
        $page = 1;

        $cStartTime = microtime(true);//统计执行开始时间
        $this->info('start');
        //设置进度条开始状态
        $bar = $this->output->createProgressBar($num_ele_maid_old);

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

                //饿了么分佣真表
                $obj_ele_maid = new MtMaidOther();
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

                //根据父id 获取父级当前的可提余额
                $perentAcount = $this->getParentCarryMoney($app_id);

                //给用户添加分佣的钱
                $taobao_user = new ThreeUser();
                $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();

                if (empty($obj_taobao_user)) {
                    $taobao_user->create([
                        'app_id' => $app_id,
                        'money' => $maid_money,
                    ]);
                } else {
                    $obj_taobao_user->money = $obj_taobao_user->money + $maid_money;
                    $obj_taobao_user->save();
                }

                //记录可提余额变化记录值与变化说明
                $obj_three_change_user_log = new ThreeChangeUserLog();
                $later_money = $perentAcount + $maid_money;
                $obj_three_change_user_log->addLog($app_id, $perentAcount, $maid_money, $later_money, 0, 'MTI');
            }
            $page++;
        }

        //统计结束
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
    }

    /*
     * 根据app_id 取该用户可提余额
     */
    public function getParentCarryMoney($ptPid)
    {
        $obj_three_user = new ThreeUser();
        $account = $obj_three_user->where(['app_id' => $ptPid])->first();
        if (!$account) {
            $obj_three_user->create([
                'app_id' => $ptPid,
                'money' => 0,
            ]);
            $account = $obj_three_user->where(['app_id' => $ptPid])->first();
        }
        return $account->money;
    }

}
