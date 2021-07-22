<?php

namespace App\Console\Commands;

use App\Entitys\App\StartPageIndex;
use App\Entitys\Other\CommandConfigOther;
use App\Entitys\Other\JdMaidOldOther;
use App\Entitys\Other\ManagerPretendMaid;
use App\Entitys\Other\PddMaidOldOther;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\JdEnterOrdersOut;
use App\Entitys\OtherOut\JdMaidOldOut;
use App\Entitys\OtherOut\PddEnterOrdersOut;
use App\Entitys\OtherOut\PddMaidOldOut;
use App\Services\WuHang\Maid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OtherJdMaidOld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OtherJdMaidOld';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command 京东第三方管理费假库';

    private $maidOldModel = null;
    private $otherMaidOldModel = null;
    private $commandModel = null;
    private $current = ''; // 单前执行的用户
    private $managerModel = null;
    private $managerMaidService = null;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->commandModel = new CommandConfigOther();
        $this->maidOldModel = new JdMaidOldOut();
        $this->managerMaidService = new Maid();
        $this->otherMaidOldModel = new JdMaidOldOther();
        $this->managerModel = new ManagerPretendMaid();
        $this->commandModel->setCommandName($this->signature);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $end_timestamp = time();
        $use_timestamp = $end_timestamp;
        $command = $this->commandModel->initCommandInfo($end_timestamp);
        // 获取脚本执行的开始日期 相当于上次执行成功的结束日期
        $start_timestamp = $command['start_time']; //
        $page = $command['page_index'];
        $page_size = $command['page_size'];
        $retry_time = $command['end_time'];
        if ($retry_time > 0) { // 如果不为0，则表示该时间断内未全部执行完成
            $end_timestamp = $retry_time; // 结束时间
        }
        $start_time = date('Y-m-d H:i:s', $start_timestamp);
        $end_time = date('Y-m-d H:i:s', $end_timestamp);
//        $this->info($command->toJson());
        $this->info('开始： ' . $start_time . ' - ' . $end_time );
        $this->log($start_timestamp . '---' .  $end_time);
        //TODO 查询delete_at包含的时间段，先处理删除 获取报销用户
        $delete_list = DB::connection('app38_out')->select(
            "select * from lc_jd_maid_old t1
                    WHERE t1.type = 2 AND t1.real = 0 AND t1.deleted_at BETWEEN '{$start_time}' AND '{$end_time}'"
        );
        foreach ($delete_list as $delete_item){
            $maid_info = $this->otherMaidOldModel->where([
                'trade_id' => $delete_item->trade_id,
            ])->first();
            if(!empty($maid_info)){ // 判断是否存在存在则删除
                $this->otherMaidOldModel->where([
                    'trade_id' => $delete_item->trade_id
                ])->delete();
                try{
                    $this->managerModel->where(['order_id' => $delete_item->trade_id])->delete();
                } catch (\Throwable $exception){
                    $this->info('manager delete fail! ' . $delete_item->trade_id);
                }
            }
        }
        //TODO 查询create_at包含的时间段，再处理分佣 分页处理
        while (1) {
            try {
                DB::connection('db001')->beginTransaction();
                $maid_list = $this->maidOldModel->where('created_at', '>=', $start_time)
                    ->where('created_at', '<=', $end_time)
                    ->where('type', '=', 2)
                    ->where('real', '=', 0)->orderBy('created_at')->forPage($page, $page_size)->get();
                $count = count($maid_list);
                $this->output->write($page . '|');
                foreach ($maid_list as $maid_item){
                    $order_id = $maid_item->trade_id;
                    $sku_id =  $maid_item->sku_id;
                    $jdModel = new JdEnterOrdersOut();
                    $order = $jdModel->where(['orderId' => $order_id, 'skuId' => $sku_id])->first();
                    $trade_id = $order->orderId;   //得到订单id
                    $sku_id = $order->skuId;   //得到sku_id 商品id
                    $app_id = $order->app_id;  //取得用户appid
                    $maid_money = $order->actualFee;    //实际佣金，实际获得佣金
                    if(empty($order)){
                        $this->log(date('Y-m-d H:m:s') . '---' . '订单为空'  . $order_id);
                        continue;
                    }
                    $ad_user_info = AdUserInfoOut::where(['pt_id' => $app_id])->first();

                    if (empty($ad_user_info)) {
                        $this->log($app_id . '用户不存在!');
                        continue;
                    }
                    $commission = $maid_money;

                    $due_rmb = 0;
                    $count_partner = 0;
                    $tmp_next_id = $ad_user_info->pt_pid;
                    try{
                        $this->managerMaidService->maid($trade_id, $commission, $tmp_next_id, $app_id, 3);
                    } catch (\Throwable $exception){
                        $this->info('manager maid fail!' . $trade_id . '-commission-' . $commission . '-appid-' . $app_id);
                    }
                    for ($i = 1; $i < 50; $i++) {
                        if (empty($tmp_next_id)) {
                            break;
                        }
                        $parent_info = AdUserInfoOut::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);

                        if (empty($parent_info)) {
                            $this->log($tmp_next_id . '用户不存在!');
                            break;
                        }

                        $p_groupid = $parent_info['groupid'];
                        $p_pt_pid = $parent_info['pt_pid'];
                        $p_pt_id = $parent_info['pt_id'];
                        $tmp_next_id = $p_pt_pid;


                        if ($i == 1) {

                            if ($p_groupid == 23) {
                                $due_rmb = round($commission * 0.1, 2);
                            } elseif ($p_groupid == 24) {
                                $due_rmb = round($commission * 0.1, 2);
                                $count_partner += 1;
                            } else {
                                $due_rmb = round($commission * 0.05, 2);
                            }

                        } else {
                            if ($p_groupid != 24) {
                                continue;
                            }
                            if ($count_partner == 0) {
                                $due_rmb = round($commission * 0.05, 2);
                            } else {
                                $due_rmb = round($commission * 0.025, 2);
                            }
                            $count_partner += 1;
                        }

                        if (empty($due_rmb) || $i == 1) {
                            continue;
                        }
                        $has1 = !JdMaidOldOther::where(['trade_id' => (string)$order_id, 'sku_id' => $sku_id, 'app_id' => (string)$p_pt_id])->exists();
                        $has2 = !JdMaidOldOut::where(['trade_id' => (string)$order_id, 'sku_id' => $sku_id, 'app_id' => (string)$p_pt_id])->exists();
                        if ($has1 && $has2) {
                            JdMaidOldOther::create([
                                'father_id' => $app_id,
                                'trade_id' => (string)$trade_id,
                                'sku_id' => (string)$sku_id,
                                'app_id' => $p_pt_id,
                                'group_id' => $p_groupid,
                                'maid_money' => $due_rmb,
                                'type' => 1,
                                'real' => 0,
                            ]);
                        }


                        if ($count_partner >= 2) {
                            break;
                        }
                    }
                }
                //TODO 处理结束 更新命令config信息
                $page++;
                $this->commandModel->pageSuccess($page);
                if ($count < $page_size) { // 全部执行完毕
                    $this->commandModel->allSuccess($end_timestamp + 1);
                    DB::connection('db001')->commit();
                    break;
                }

                DB::connection('db001')->commit();
            } catch (\Exception $e) {
                DB::connection('db001')->rollBack();
                $msg = '第' . $page . '页, ID：' . $this->current . '||' . $e->getMessage() . 'line:' . $e->getLine();
                $this->info($msg);
                $this->commandModel->error($msg);
                break;
            }
        }
        $this->info('end! 耗时：' . round((time() - $use_timestamp) / 60, 2) . '分钟');

    }

    /*
     * 记录日志
     */
    private function log($msg)
    {
        Storage::disk('local')->append('callback_document/OtherMaid/jd.txt', var_export($msg, true));
    }

}