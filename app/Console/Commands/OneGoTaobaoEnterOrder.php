<?php

namespace App\Console\Commands;

use App\Entitys\App\OneGoAlimamaInfo;
use App\Entitys\App\OneGoH5CashGit;
use App\Entitys\App\OneGoMaidOld;
use App\Services\Alimama\NewAliOrderService;
use Illuminate\Console\Command;
use App\Entitys\App\OneGoTaobaoEnterOrder as OneGoTaobaoEnterOrderModel;

class OneGoTaobaoEnterOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:oneGoTaobaoEnterOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '一元购全量报销抓取订单';

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
        $new_ali_order_service = new NewAliOrderService();
        $cStartTime = microtime(true);

        $this->info("start");
        $orders_0_day = $new_ali_order_service->getChannelOrders(1);
        $orders_1_day = $new_ali_order_service->getChannelOrders(2);
        $orders_5_day = $new_ali_order_service->getChannelOrders(3);
        $orders_10_day = $new_ali_order_service->getChannelOrders(4);
        $orders_15_day = $new_ali_order_service->getChannelOrders(5);

        $all_orders = array_merge($orders_0_day, $orders_1_day, $orders_5_day, $orders_10_day, $orders_15_day);
        $this->info('总订单数量：' . count($all_orders));
        $bar = $this->output->createProgressBar(count($all_orders));
        $obj_one_go_taobao_enter_order = new OneGoTaobaoEnterOrderModel();
        foreach ($all_orders as $item) {
            $id_taobao_new = $obj_one_go_taobao_enter_order->where(['trade_id' => $item['trade_id']])->value('id');
            if (!empty($id_taobao_new)) {
                $obj_one_go_taobao_enter_order->where('id', $id_taobao_new)->update($item);
            } else {
                $obj_one_go_taobao_enter_order->create($item);
            }
            $obj_one_go_maid_old = new OneGoMaidOld();
            $obj_one_go_h5_cash_git = new OneGoH5CashGit();
            $obj_one_go_alimama_info = new OneGoAlimamaInfo();
            $user_app_id = $obj_one_go_alimama_info->where('relation_id', $item['relation_id'])->value('app_id');
            $data_one_go_h5 = $obj_one_go_h5_cash_git->where('item_id', $item['item_id'])->first();
            $maid_money = @$data_one_go_h5->price - @$data_one_go_h5->cash;
            $id_one_go = $obj_one_go_maid_old->where(['trade_id' => $item['trade_id']])->first();
            if ($id_one_go) {
                if (in_array($item['tk_status'], [3, 12, 14])) {
                    if ($item['total_commission_fee'] != $id_one_go->get_money) {
                        $obj_one_go_maid_old->delOrder($item['trade_id']);
                        $obj_one_go_maid_old->addSubsidy((string)$item['trade_id'], $user_app_id, $maid_money, $item['total_commission_fee']);
                    }
                } else {
                    $obj_one_go_maid_old->delOrder($item['trade_id']);
                }
            } else {
                if (in_array($item['tk_status'], [3, 12, 14])) {
                    $num_maid_old = $obj_one_go_maid_old->where(['app_id' => $user_app_id, 'trade_id' => $item['trade_id']])->count();
                    if ($num_maid_old < @$data_one_go_h5->number) {
                        $obj_one_go_maid_old->addSubsidy((string)$item['trade_id'], $user_app_id, $maid_money, $item['pub_share_pre_fee']);
                    }
                }
            }
            $bar->advance();

        }
        $bar->finish();
        $cEndTime = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($cEndTime - $cStartTime));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
    }

}
