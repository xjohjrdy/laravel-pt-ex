<?php

namespace App\Console\Commands;

use App\Entitys\App\TaobaoEnterOrder;
use Illuminate\Console\Command;

class TaobaoEnterPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:taobaoEnterPush';

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
    public function handle()
    {

        $this->info("start");

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
        $req->setOrderQueryType("settle_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            return '该时间段没有订单';
        }
        $obj_taobao_order = new TaobaoEnterOrder();
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));
        foreach ($resp['results']['n_tbk_order'] as $item) {
            if ($obj_taobao_order->orderExists($item['trade_id'])) {
                $obj_taobao_order->where('trade_id', (string)$item['trade_id'])->update($item);
                $this->info("订单：{$item['trade_id']} 已存在，更新中。");
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
        $req->setOrderQueryType("settle_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            return '该时间段没有订单';
        }
        $obj_taobao_order = new TaobaoEnterOrder();
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));
        foreach ($resp['results']['n_tbk_order'] as $item) {
            if ($obj_taobao_order->orderExists($item['trade_id'])) {
                $obj_taobao_order->where('trade_id', (string)$item['trade_id'])->update($item);
                $this->info("订单：{$item['trade_id']} 已存在，更新中。");
                continue;
            }
            $obj_taobao_order->addOrder($item);
            $this->info("获取订单成功：" . $item['trade_id']);
        }

        $this->info("end");
    }
}
