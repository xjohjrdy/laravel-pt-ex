<?php

namespace App\Console\Commands;

use App\Entitys\App\JdMaid;
use App\Entitys\App\JdMaidOld;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use App\Entitys\Other\JdMaidOldOther;
use App\Entitys\Other\JdMaidOther;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeUser;
use App\Services\Common\Time;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OtherJdCountOrderAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OtherJdCountOrderAmount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每月25日执行 京东分佣处理， 处理第三方订单';

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
        //得到上月全部假表报销记录
        $obj_jd_maid_old = new JdMaidOldOther();
        //全部订单记录数量
        $last_month_time = [date('Y-m-d H:i:s', $last_month[0]), date('Y-m-d H:i:s', $last_month[1])];
        $num_jd_maid_old = $obj_jd_maid_old->lastAllApplyData($last_month_time);

        //总页数
        $all_page = ceil($num_jd_maid_old / 10000);
        $page = 1;

        //统计执行开始时间
        $cStartTime = microtime(true);

        $this->info('start');
        //设置进度条开始状态
        $bar = $this->output->createProgressBar($num_jd_maid_old);

        while ($page <= $all_page) {
            //取 上个月 的一万条数据， real 为 0 的
            $jd_maid_old_datas = $obj_jd_maid_old
                ->whereBetween('created_at', $last_month_time)
                ->where(['real' => 0])
                ->forPage(1, 10000)
                ->get();

            if (empty($jd_maid_old_datas)) {
                break;
            }

            foreach ($jd_maid_old_datas as $jd_maid_old_data) {

                if ($jd_maid_old_data->real == 1) { //后续调试使用
                    continue;
                }

                //推动进度条
                $bar->advance();
                //修改假表real
                $jd_maid_old_data->update(['real' => 1]);  //单条遍历处理
                //取假表所有字段数据
                $father_id = $jd_maid_old_data->father_id;
                $trade_id = $jd_maid_old_data->trade_id;
                $sku_id = $jd_maid_old_data->sku_id;
                $app_id = $jd_maid_old_data->app_id;
                $group_id = $jd_maid_old_data->group_id;
                $maid_money = $jd_maid_old_data->maid_money;
                $type = $jd_maid_old_data->type;

                $obj_jd_maid = new JdMaidOther(); //分佣真表
                //判断真表里面是否有数据
                $bor_jd_maid = $obj_jd_maid->where(['trade_id' => $trade_id, 'app_id' => $app_id, 'sku_id' => $sku_id])->exists();

                if ($bor_jd_maid) {
                    continue;
                }
                try{
                    DB::connection('db001')->beginTransaction();
                    //真表增加记录
                    $obj_jd_maid->create([
                        'father_id' => $father_id,
                        'trade_id' => $trade_id,
                        'sku_id' => $sku_id,
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
                    $obj_three_change_user_log->addLog($app_id, $perentAcount, $maid_money, $later_money, 8, 'FJD');
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
