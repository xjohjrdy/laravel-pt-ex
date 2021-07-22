<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\PddEnterOrders;
use App\Entitys\App\PddMaidOld;
use App\Services\Other\OtherCountService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PddEnterOrderOneTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PddEnterOrderOneTime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理拼多多指定天数内的订单';
    private $countService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->countService  = new OtherCountService();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("start");
        $c_start_time = microtime(true);
        $start_time = date("Y-m-d H:i:s", strtotime("-57 day"));
        $obj_pdd_enter_orders = new PddEnterOrders();
        $int_sum_orders = $obj_pdd_enter_orders->getOrdersSum($start_time);
        $bar = $this->output->createProgressBar($int_sum_orders);
        $page_size = 1000;
        $page_total = ceil($int_sum_orders / $page_size); #总页数

        for ($i = 1; $i <= $page_total; $i++) {
            $cut_page_data = $obj_pdd_enter_orders->getCutData($page_size, $i, $start_time);
            foreach ($cut_page_data as $datum) {
                $bar->advance();

                $app_id = $datum->app_id;                     #取得用户appid
                $order_status = $datum->order_status;         #订单状态： -1 未支付; 0-已支付；1-已成团；2-确认收货；3-审核成功；4-审核失败（不可提现）；5-已经结算；8-非多多进宝商品（无佣金订单）
                $promotion_amount = $datum->promotion_amount; #佣金金额(分)
                $order_sn = $datum->order_sn;                 #推广编号id
//                if (Cache::has('p_d_d_' . $order_sn . $app_id)) {
//                    continue;
//                }
//                Cache::put('p_d_d_' . $order_sn . $app_id, 1, 0.2);



                $obj_pdd_maid_old = new PddMaidOld();
                if ($order_status == 1 || $order_status == 2 || $order_status == 3 || $order_status == 5) {

                    $bool_pdd_maid_old = $obj_pdd_maid_old->where(['trade_id' => $order_sn, 'app_id' => $app_id, 'real' => 0])->first();
                    if ($bool_pdd_maid_old) {
                        if ($order_status == 1 || $order_status == 2 || $order_status == 3) {
                            $this->anewCommissionAllot2($app_id, $promotion_amount, $order_sn);
                        } elseif ($order_status == 5) {
                            $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();
                            $group_id = $ad_user_info->groupid;

                            if (in_array($group_id, [23, 24])) {
                                $f_commission = round($promotion_amount * 0.00645, 2);
                            } else {
                                $f_commission = round($promotion_amount * 0.0042, 2);
                            }
                            if ($f_commission == round($bool_pdd_maid_old->maid_money, 2)) {
                                continue;
                            } else {
                                $obj_pdd_maid_old->delOrder($app_id, $order_sn);
                                $this->anewCommissionAllot2($app_id, $promotion_amount, $order_sn);
                            }
                        }
                    } else {
                        $this->anewCommissionAllot2($app_id, $promotion_amount, $order_sn);
                    }
                } else {
                    $obj_pdd_maid_old->delOrder($app_id, $order_sn);
                }
            }
        }
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming}s\r\n");
    }


    public function anewCommissionAllot2($app_id, $promotion_amount, $order_sn)
    {
        $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

        if (empty($ad_user_info)) {
            return false;
        }
        $commission = $promotion_amount / 100;#分变元
        $group_id = $ad_user_info->groupid;
        if (in_array($group_id, [23, 24])) {
            $f_commission = round($commission * 0.645, 2);
        } else {
            $f_commission = round($commission * 0.42, 2);
        }
        $order_commission = $f_commission;
        if (!PddMaidOld::where(['trade_id' => (string)$order_sn, 'app_id' => (string)$app_id, 'type' => 2])->exists()) {
            PddMaidOld::create([
                'father_id' => 0,
                'trade_id' => (string)$order_sn,
                'app_id' => $app_id,
                'group_id' => $group_id,
                'maid_money' => $f_commission,
                'type' => 2,
                'real' => 0,
            ]);
        }


        $due_rmb = 0;
        $tmp_next_id = $ad_user_info->pt_pid;
        $parent_info = AdUserInfo::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);
        if(empty($parent_info)){
            return $order_commission;
        }
        $p_groupid = $parent_info['groupid'];
        $p_pt_id = $parent_info['pt_id'];
        if ($p_groupid == 23) {
            $due_rmb = round($commission * 0.1, 2);
        } elseif ($p_groupid == 24) {
            $due_rmb = round($commission * 0.1, 2);
        } else {
            $due_rmb = round($commission * 0.05, 2);
        }
        if (!PddMaidOld::where(['trade_id' => (string)$order_sn, 'app_id' => (string)$p_pt_id, 'type' => 1])->exists()) {

            PddMaidOld::create([
                'father_id' => $app_id,
                'trade_id' => (string)$order_sn,
                'app_id' => $p_pt_id,
                'group_id' => $p_groupid,
                'maid_money' => $due_rmb,
                'type' => 1,
                'real' => 0,
            ]);
        }

    }
}
