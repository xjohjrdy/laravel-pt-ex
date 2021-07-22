<?php

namespace App\Console\Commands;

use App\Entitys\App\TaobaoEnterOrder;
use App\Services\Shop\Order;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TaobaoEnter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:taobaoEnter';

    protected $testObj;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动获取淘宝订单';

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
        $this->info("start");

        $real_data = [];
        $appkey = 25626319;
        $secret = '05668c4eefc404c0cd175fb300b2723d';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_parent_id,trade_id,num_iid,item_title,item_num,price,pay_price,seller_nick,seller_shop_title,commission,commission_rate,create_time,earning_time,tk_status,tk3rd_type,tk3rd_pub_id,order_type,income_rate,pub_share_pre_fee,subsidy_rate,subsidy_type,terminal_type,auction_category,site_id,site_name,adzone_id,adzone_name,alipay_total_price,total_commission_rate,total_commission_fee,subsidy_fee,relation_id,special_id,click_time");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-6 minute")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("12");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            return '该时间段没有订单';
        }
        $obj_taobao_order = new TaobaoEnterOrder();
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));
        foreach ($resp['results']['n_tbk_order'] as $item) {

            if ($item['tk_status'] == 12) {
                $status = 1;
            }
            if ($item['tk_status'] == 3) {
                $status = 2;
            }
            if ($item['tk_status'] == 13) {
                $status = 3;
            }
            if ($item['tk_status'] == 14) {
                $status = 1;
            }
            if (empty($status)) {
                continue;
            } else {
                $real_data[] = [
                    'status' => $status,
                    'order_number' => $item['trade_id'],
                    'commission' => $item['pub_share_pre_fee'],
                    'taobao_time' => strtotime($item['create_time']),
                ];
            }

            if ($obj_taobao_order->orderExists($item['trade_id'])) {
                $this->info("订单：{$item['trade_id']} 已存在，不作处理。");
                continue;
            }
            $obj_taobao_order->addOrder($item);
            $this->info("获取订单成功：" . $item['trade_id']);
        }


        $appkey = 25620531;
        $secret = 'b12d3463ad8c0609c648202aad946ddb';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_parent_id,trade_id,num_iid,item_title,item_num,price,pay_price,seller_nick,seller_shop_title,commission,commission_rate,create_time,earning_time,tk_status,tk3rd_type,tk3rd_pub_id,order_type,income_rate,pub_share_pre_fee,subsidy_rate,subsidy_type,terminal_type,auction_category,site_id,site_name,adzone_id,adzone_name,alipay_total_price,total_commission_rate,total_commission_fee,subsidy_fee,relation_id,special_id,click_time");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-6 minute")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("12");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $res_handle = $this->handleOrder($real_data);
            $this->info("1.处理完成-返回信息" . var_export($res_handle, true));
            return '该时间段没有订单';
        }
        $obj_taobao_order = new TaobaoEnterOrder();
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));
        foreach ($resp['results']['n_tbk_order'] as $item) {
            if ($item['tk_status'] == 12) {
                $status = 1;
            }
            if ($item['tk_status'] == 3) {
                $status = 2;
            }
            if ($item['tk_status'] == 13) {
                $status = 3;
            }
            if ($item['tk_status'] == 14) {
                $status = 1;
            }
            if (empty($status)) {
                continue;
            } else {
                $real_data[] = [
                    'status' => $status,
                    'order_number' => $item['trade_id'],
                    'commission' => $item['pub_share_pre_fee'],
                    'taobao_time' => strtotime($item['create_time']),
                ];
            }

            if ($obj_taobao_order->orderExists($item['trade_id'])) {
                $this->info("订单：{$item['trade_id']} 已存在，不作处理。");
                continue;
            }
            $obj_taobao_order->addOrder($item);
            $this->info("获取订单成功：" . $item['trade_id']);
        }

        $res_handle = $this->handleOrder($real_data);
        $this->info("2.处理完成-返回信息" . var_export($res_handle, true));
        $this->info("end");
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
