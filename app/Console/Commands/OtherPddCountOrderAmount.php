<?php

namespace App\Console\Commands;

use App\Entitys\App\PddMaid;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use App\Entitys\Other\PddMaidOldOther;
use App\Entitys\Other\PddMaidOther;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeUser;
use App\Services\Common\Time;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OtherPddCountOrderAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OtherPddCountOrderAmount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每月25日执行，拼多多分佣计算!';

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
        $obj_pdd_maid_old = new PddMaidOldOther();
        //全部订单记录数量
        $last_month_time = [date('Y-m-d H:i:s', $last_month[0]), date('Y-m-d H:i:s', $last_month[1])];
        $num_pdd_maid_old = $obj_pdd_maid_old->lastAllApplyData($last_month_time);

        //总页数
        $all_page = ceil($num_pdd_maid_old / 10000);
        $page = 1;

        $cStartTime = microtime(true);//统计执行开始时间

        $this->info('start');
        //设置进度条开始状态
        $bar = $this->output->createProgressBar($num_pdd_maid_old);

        while ($page <= $all_page) {

            $pdd_maid_old_datas = $obj_pdd_maid_old
                ->whereBetween('created_at', $last_month_time)
                ->where(['real' => 0])
                ->forPage(1, 10000)
                ->get(); //取 上个月 的一万条数据， real 为 0 的

            if (empty($pdd_maid_old_datas)) {
                break;
            }

            foreach ($pdd_maid_old_datas as $pdd_maid_old_data) {

                if ($pdd_maid_old_data->real == 1) { //后续调试使用
                    continue;
                }

                //推动进度条
                $bar->advance();
                //修改假表real
                $pdd_maid_old_data->update(['real' => 1]);  //单条遍历处理
                //取假表所有字段数据
                $father_id = $pdd_maid_old_data->father_id;
                $trade_id = $pdd_maid_old_data->trade_id;
                $app_id = $pdd_maid_old_data->app_id;
                $group_id = $pdd_maid_old_data->group_id;
                $maid_money = $pdd_maid_old_data->maid_money;
                $type = $pdd_maid_old_data->type;
                $obj_pdd_maid = new PddMaidOther(); //分佣真表
                //判断真表里面是否有数据
                $bor_pdd_maid = $obj_pdd_maid->where(['trade_id' => $trade_id, 'app_id' => $app_id])->exists();

                if ($bor_pdd_maid) {
                    continue;
                }

                try{
                    DB::connection('db001')->beginTransaction();
                    //真表增加记录
                    $obj_pdd_maid->create([
                        'father_id' => $father_id,
                        'trade_id' => $trade_id,
                        'app_id' => $app_id,
                        'group_id' => $group_id,
                        'maid_money' => $maid_money,
                        'type' => $type,
                    ]);

                    $perentAcount = $this->getUserMoney($app_id);
                    //给用户加可提余额

                    $obj_three_user = new ThreeUser();
                    $obj_three_user->where('app_id', $app_id)->update(['money' => DB::raw("money + " . $maid_money)]);
                    //记录可提余额变化记录值与变化说明
                    $obj_three_change_user_log = new ThreeChangeUserLog();
                    $later_money = $perentAcount + $maid_money;
                    $obj_three_change_user_log->addLog($app_id, $perentAcount, $maid_money, $later_money, 9, 'FPDD');
                    DB::connection('db001')->commit();
                }catch (\Throwable $e){
                    DB::connection('db001')->rollBack();
                }
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
    public function getUserMoney($ptPid)
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
