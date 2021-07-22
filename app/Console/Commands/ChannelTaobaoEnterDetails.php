<?php

namespace App\Console\Commands;

use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\AlimamaInfoNew;
use App\Entitys\App\TaobaoEnterOrder;
use App\Entitys\App\TaobaoEnterOrderNew;
use App\Entitys\App\UserOrderNew;
use App\Entitys\App\UserOrderTao;
use App\Services\Ali\AliOrderService;
use App\Services\Alimama\NewAliOrderService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ChannelTaobaoEnterDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:channelTaobaoEnterDetails';

    protected $testObj;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'details.get';

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

        //Data import
        $this->info("start");
        $orders_0_day = $new_ali_order_service->getChannelOrders(1);
        $this->info('最近订单获取数量：' . count($orders_0_day));
        $this->info("end");

        //Data review
        $this->info("start");
        $orders_1_day = $new_ali_order_service->getChannelOrders(2);
        $orders_5_day = $new_ali_order_service->getChannelOrders(3);
        $orders_10_day = $new_ali_order_service->getChannelOrders(4);
        $orders_15_day = $new_ali_order_service->getChannelOrders(5);

        $host_orders = array_merge($orders_0_day, $orders_1_day, $orders_5_day);
        $this->syncOrders($host_orders);
        /***********************************/
        foreach ($host_orders as $order) {
            if ($order['order_type'] == '饿了么') continue;

            if ($order['adzone_id'] == '109375250125') {
                $obj_alimama_info = new AlimamaInfo();
                $app_id = $obj_alimama_info->where(['relation_id' => $order['relation_id'], 'adzone_id' => $order['adzone_id']])->value('app_id');
            } else {
                $obj_alimama_info_new = new AlimamaInfoNew();
                $app_id = $obj_alimama_info_new->where(['relation_id' => $order['relation_id'], 'adzone_id' => $order['adzone_id']])->value('app_id');
            }

            if (empty($app_id)) {
                $this->info('渠道id：' . $order['relation_id'] . ',adzone_id:' . $order['adzone_id'] . '不存在');
                continue;
            }
            //抛订单
            $this->newChannelSubmitOrderNumber($app_id, $order['trade_id']);
        }

        $all_orders = array_merge($orders_0_day, $orders_1_day, $orders_5_day, $orders_10_day, $orders_15_day);
        $this->info('本次待发送订单数：' . count($all_orders));
        $data_api = [];
        $status_api = [
            3 => 2,     //3：订单结算
            12 => 1,    //12：订单付款
            13 => 3,    //13：订单失效
            14 => 1     //14：订单成功
        ];
        foreach ($all_orders as $order) {
            if ($order['order_type'] == '饿了么') continue;

            $this->info('订单号：' . $order['trade_id']);
            $data_api[] = [
                'order_number' => $order['trade_id'],
                'status' => @$status_api[$order['tk_status']],
                'commission' => $order['pub_share_pre_fee'],
                'taobao_time' => strtotime($order['tk_create_time']), //新接口 为 tk_create_time
            ];
        }
        $this->handleOrderLoad($data_api);

        $this->info("end");
    }

    public function syncOrders($orders)
    {
        $obj_taobao_order_new = new TaobaoEnterOrderNew();
        $obj_taobao_order = new TaobaoEnterOrder();

        foreach ($orders as $item) {
            try {

                if (Cache::has('a_t_s_' . $item['trade_id'])) {
                    continue;
                }
                Cache::put('a_t_s_' . $item['trade_id'], 1, 0.5);


                if ($item['order_type'] == '饿了么') continue;

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

                //有则更新
                $id_taobao_new = $obj_taobao_order_new->where(['trade_id' => $item['trade_id']])->value('id');
                if (!empty($id_taobao_new)) {
                    $obj_taobao_order_new->where('id', $id_taobao_new)->update([
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
                }

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
                ], ''); // wanted array with empty value
                $enter_order = array_replace($keys, array_intersect_key($item, $keys)); // replace only the wanted keys
                $enter_order['price'] = $item['item_price'];
                $enter_order['num_iid'] = $item['item_id'];
                $enter_order['commission'] = $item['pub_share_fee'];
                $enter_order['commission_rate'] = $item['pub_share_rate'];
                $enter_order['create_time'] = $item['tk_create_time'];
                $enter_order['earning_time'] = 0;   //tk_earning_time
                $enter_order['tk3rd_type'] = $item['flow_source'];
                $enter_order['tk3rd_pub_id'] = $item['pub_id'];
                $enter_order['auction_category'] = $item['item_category_name'];

                $obj_taobao_order->firstOrCreate(['trade_id' => $item['trade_id']], $enter_order);

                //有则更新
                $id_taobao = $obj_taobao_order->where(['trade_id' => $item['trade_id']])->value('id');
                if (!empty($id_taobao)) {
                    $obj_taobao_order->where('id', $id_taobao)->update($enter_order);
                }
            } catch (\Exception $exception) {
                $this->info($exception->getLine() . '--' . $exception->getMessage());
                continue;
            }
        }
        return true;
    }

    public function handleOrderLoad($real_data)
    {
        $service = new AliOrderService();
        $fail_data = [];
        $success = 0;
        $fail = 0;
        foreach ($real_data as $item) {
            $data = [
                'order_number' => $item['order_number'],
                'status' => $item['status'],
                'commission' => $item['commission'],
                'taobao_time' => $item['taobao_time'],
                'create_time' => time(),
                'admin_id' => "0"//操作人 0表示后台
            ];
            /*
             * 添加订单防止特殊情况下重放
             */
            if (Cache::has('a_t_o_' . $item['order_number'])) {
                $res = 0;
                continue;
            } else {
                Cache::put('a_t_o_' . $item['order_number'], 1, 0.2);
                $res = $service->handleTaoBaoDataV1($data);
            }
            $success += $res ? 1 : 0;
            $fail += $res ? 0 : 1;
            if (!$res) {
                $fail_data[] = [
                    'order_number' => $item['order_number'],
                    'status' => @$item['status'],
                    'commission' => $item['commission'],
                    'taobao_time' => $item['taobao_time']
                ];
            }
        }

        print_r([
            'success' => $success,
            'fail' => $fail,
            'fail_data' => $fail_data
        ]);
    }

    public function handleOrder($real_data)
    {
        //验证用户是否存在手机号
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
        //发送post请求
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $arr_res = json_decode($json_res, true);
        return $arr_res;
    }

    public function newChannelSubmitOrderNumber($app_id, $trade_id)
    {
        $userOrderNew = new UserOrderNew();
        $userOrderTao = new UserOrderTao();
        //新表重复订单 不提交
        if ($userOrderNew->where(['order_number' => $trade_id])->first()) {
            return true;//跳出
        }

        //老表重复订单 不提交
        if ($userOrderTao->where(['order_number' => $trade_id])->first()) {
            return true;//跳出
        }

        $order_data['is_normal'] = 1;
        $order_data['order_number'] = (string)$trade_id;
        $order_data['user_id'] = $app_id;
        $order_data['cashback_amount'] = 0.00;
        $order_data['reason'] = " ";
        $order_data['create_time'] = time();
        $order_data['update_time'] = time();
        $userOrderNew->insert($order_data);
        return true;//正常返回数据
    }
}
