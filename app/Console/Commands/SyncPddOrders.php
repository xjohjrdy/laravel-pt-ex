<?php

namespace App\Console\Commands;

use App\Entitys\App\PddEnterOrders;
use App\Services\PddCommodity\PddCommodityServices;
use Illuminate\Console\Command;

/**
 * 同步拼多多订单脚本
 * Class SyncPddOrders
 * @package App\Console\Commands
 */
class SyncPddOrders extends Command
{
    protected $pddService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SyncPddOrders';

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
    public function handle(PddEnterOrders $pddEnterOrders)
    {

        $pddService = new PddCommodityServices();
        $page = 1;
        $page_size = 20;
        $time = time();
        $end_time = strtotime("-0 minute", $time);
        $start_time = strtotime("-36 minute", $time);
        $this->info("开始同步：" . $start_time . '--' . $end_time);
        while (true) {
            $this->info("第：" . $page . '页' . ' 每页数量：' . $page_size);
            $response = json_decode($pddService->getOrder($start_time, $end_time, $page, $page_size), true);
            $data = @$response['data'];
            if (!empty(@$data['error_response'])) {
                $this->info('error:' . @$data['error_response']['error_msg'] . 'code:' . @$data['error_response']['error_code']);
                break;
            }
            $total = @$data['total_count'];
            foreach (@$data['order_list'] as $order) {
                try {
                    $enter_order = [];
                    $enter_order['app_id'] = empty($order['custom_parameters']) ? 0 : $order['custom_parameters'];
                    $enter_order['p_id'] = $order['p_id'];
                    $enter_order['order_verify_time'] = empty($order['order_verify_time']) ? ' ' : $order['order_verify_time'];
                    $enter_order['order_pay_time'] = empty($order['order_pay_time']) ? ' ' : $order['order_pay_time'];
                    $enter_order['order_group_success_time'] = empty($order['order_group_success_time']) ? ' ' : $order['order_group_success_time'];
                    $enter_order['order_modify_at'] = empty($order['order_modify_at']) ? ' ' : $order['order_modify_at'];
                    $enter_order['order_status_desc'] = empty($order['order_status_desc']) ? ' ' : $order['order_status_desc'];
                    $enter_order['order_status'] = empty($order['order_status']) ? ' ' : $order['order_status'];
                    $enter_order['promotion_amount'] = empty($order['promotion_amount']) ? ' ' : $order['promotion_amount'];
                    $enter_order['promotion_rate'] = empty($order['promotion_rate']) ? ' ' : $order['promotion_rate'];
                    $enter_order['order_create_time'] = empty($order['order_create_time']) ? ' ' : $order['order_create_time'];
                    $enter_order['order_amount'] = empty($order['order_amount']) ? ' ' : $order['order_amount'];
                    $enter_order['goods_price'] = empty($order['goods_price']) ? ' ' : $order['goods_price'];
                    $enter_order['goods_quantity'] = empty($order['goods_quantity']) ? ' ' : $order['goods_quantity'];
                    $enter_order['goods_thumbnail_url'] = empty($order['goods_thumbnail_url']) ? ' ' : $order['goods_thumbnail_url'];
                    $enter_order['goods_name'] = empty($order['goods_name']) ? ' ' : $order['goods_name'];
                    $enter_order['goods_id'] = empty($order['goods_id']) ? ' ' : $order['goods_id'];
                    $enter_order['order_sn'] = empty($order['order_sn']) ? ' ' : $order['order_sn'];
                    $enter_order['custom_parameters'] = empty($order['custom_parameters']) ? ' ' : $order['custom_parameters'];
                    $enter_order['cpa_new'] = empty($order['cpa_new']) ? ' ' : $order['cpa_new'];
                    $enter_order['scene_at_market_fee'] = empty($order['scene_at_market_fee']) ? ' ' : $order['scene_at_market_fee'];
                    $pddEnterOrders->insertOrUpdate($enter_order);
                } catch (\Exception $e) {
                    $this->info('异常：' . $e->getMessage());
                }
            }
            if (($page_size * $page) >= $total) {
                break;
            } else {
                $page++;
                continue;
            }
        }
        $this->info('本次同步结束');
    }
}
