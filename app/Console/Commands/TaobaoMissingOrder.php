<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Ad\VoipMoneyOrderMaid;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\JdGetOneShow;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoEnterOrder;
use App\Entitys\App\TaobaoEnterOrderNew;
use App\Entitys\App\TaobaoUser;
use App\Services\Alimama\NewAliOrderService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaobaoMissingOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:taobaoMissingOrder {order} {start}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command TaobaoMissingOrder';

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
        /*
        $this->info("start");
        $c_start_time = microtime(true);
        $count_number = 0;
        $bar = $this->output->createProgressBar($count_number);
        $bar->advance();
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
        */

        $this->process();


    }


    public function process()
    {

        $order = $this->argument('order');
        $start = $this->argument('start');

        $start_time = date('Y-m-d H:i:00', strtotime($start));
        $end_time = date('Y-m-d H:i:59', strtotime($start));


        $new_ali_order_service = new NewAliOrderService();

        $all_order = $new_ali_order_service->getAssignTimeOrdersAll($start_time, $end_time);


        foreach ($all_order as $item) {

            if ($item['trade_id'] != $order) continue;

            print_r('匹配成功');
            $this->syncOrders([
                $item
            ]);
            $data_api = [];
            $status_api = [
                3 => 2,
                12 => 1,
                13 => 3,
                14 => 1
            ];

            $this->info('订单号：' . $item['trade_id']);

            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['tk_create_time']),
            ];

            $this->handleOrder($data_api);

            dd($item);
            die();
        }

        print_r('未匹配');

        dd($all_order);

    }


    public function syncOrders($orders)
    {
        $obj_taobao_order_new = new TaobaoEnterOrderNew();
        $obj_taobao_order = new TaobaoEnterOrder();

        foreach ($orders as $item) {
            $obj_taobao_order_new->firstOrCreate(['trade_id' => $item['trade_id']], [
                'trade_parent_id' => $item['trade_parent_id'],
                'trade_id' => $item['trade_id'],
                'item_title' => $item['item_title'],
                'item_num' => $item['item_num'],
                'pay_price' => empty($item['pay_price']) ? 0 : $item['pay_price'],
                'seller_nick' => $item['seller_nick'],
                'seller_shop_title' => $item['seller_shop_title'],
                'tk_status' => $item['tk_status'],
                'order_type' => $item['order_type'],
                'income_rate' => $item['income_rate'],
                'pub_share_pre_fee' => $item['pub_share_pre_fee'],
                'subsidy_rate' => $item['subsidy_rate'],
                'subsidy_type' => $item['subsidy_type'],
                'terminal_type' => $item['terminal_type'],
                'site_id' => $item['site_id'],
                'site_name' => $item['site_name'],
                'adzone_id' => $item['adzone_id'],
                'adzone_name' => $item['adzone_name'],
                'alipay_total_price' => empty($item['alipay_total_price']) ? 0 : $item['alipay_total_price'],
                'total_commission_rate' => $item['total_commission_rate'],
                'total_commission_fee' => $item['total_commission_fee'],
                'subsidy_fee' => $item['subsidy_fee'],
                'relation_id' => empty($item['relation_id']) ? 0 : $item['relation_id'],
//                    'special_id' => empty($item['special_id']) ? 0 : $item['special_id'],
                'click_time' => $item['click_time'],
//                    'price' => empty($item['price']) ? 0 : $item['price'],
//                    'num_iid' => $item['num_iid'] ?? 0 ?: 0,
//                    'commission' => $item['commission'] ?? 0 ?: 0,
//                    'commission_rate' => $item['commission_rate'] ?? 0 ?: 0,
//                    'create_time' => $item['create_time'] ?? 0 ?: 0,
//                    'earning_time' => $item['earning_time'] ?? 0 ?: 0,
//                    'tk3rd_type' => $item['tk3rd_type'] ?? 0 ?: 0,
//                    'tk3rd_pub_id' => $item['tk3rd_pub_id'] ?? 0 ?: 0,
//                    'auction_category' => $item['auction_category'] ?? 0 ?: 0,
            ]);

            $keys = array_fill_keys([
                'trade_parent_id',
                'trade_id',
                'item_title',
                'item_num',
                'pay_price',
                'seller_nick',
                'seller_shop_title',
                'tk_status',
                'order_type',
                'income_rate',
                'pub_share_pre_fee',
                'subsidy_rate',
                'subsidy_type',
                'terminal_type',
                'site_id',
                'site_name',
                'adzone_id',
                'adzone_name',
                'alipay_total_price',
                'total_commission_rate',
                'total_commission_fee',
                'subsidy_fee',
                'relation_id',
                'special_id',
                'click_time'
            ], '');
            $enter_order = array_replace($keys, array_intersect_key($item, $keys));
            $enter_order['price'] = $item['item_price'];
            $enter_order['num_iid'] = $item['item_id'];
            $enter_order['commission'] = $item['pub_share_fee'];
            $enter_order['commission_rate'] = $item['pub_share_rate'];
            $enter_order['create_time'] = $item['tk_create_time'];
            $enter_order['earning_time'] = 0;
            $enter_order['tk3rd_type'] = $item['flow_source'];
            $enter_order['tk3rd_pub_id'] = $item['pub_id'];
            $enter_order['auction_category'] = $item['item_category_name'];

            $obj_taobao_order->firstOrCreate(['trade_id' => $item['trade_id']], $enter_order);
        }
        return true;
    }

    public function handleOrder($real_data)
    {
        $post_api_data = [
            'code' => 200,
            'data' => $real_data,
            'msg' => 'RYT',
            'count' => '4',
            'total' => '4',
        ];
        $json_post_data = ($post_api_data);
        $url = "http://api.36qq.com/api/ali_sync_gather_order";
        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'tokk' => '64d8ea7cf1dc5710d61e373d34f69e23',
            ],
            'json' => $json_post_data
        ];
        $client = new Client();
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $arr_res = json_decode($json_res, true);
        return $arr_res;
    }

}
