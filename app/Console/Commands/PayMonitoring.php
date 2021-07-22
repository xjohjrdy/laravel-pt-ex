<?php

namespace App\Console\Commands;

use App\Entitys\App\ShopOrders;
use App\Services\Common\DingAlerts;
use App\Services\Common\NewSms;
use Illuminate\Console\Command;

class PayMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PayMonitoring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '支付监控';

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
        $this->info("开始检测");

        //获取前5分钟订单
        $shopOrders = new ShopOrders();
        $time = date("Y-m-d H:i:s", strtotime("-5 minute", time()));
        $this->info('得到时间大于' . $time . '的订单');
        $obj_order_data = $shopOrders->where('created_at', '>=', $time)->get();

        $app_pay_count = 0;
        $mini_pay_count = 0;
        $app_pay = false;
        $mini_pay = false;
        foreach ($obj_order_data as $v) {
            if ($v->type == 1) continue;

            $order = substr($v->order_id, 0, 1);
            if ($order == 'W') {
                $mini_pay_count++;
                if (!empty($v->sub_sign)) {
                    $mini_pay = true;
                }
            } else {
                $app_pay_count++;
                if (!empty($v->sub_sign)) {
                    $app_pay = true;
                }
            }
        }

        //是否通知
        $dingAlerts = new DingAlerts();
        $obj = new NewSms();
        if (!$app_pay && $app_pay_count >= 10) {
            $this->info('客户端支付异常:' . $time);
            $dingAlerts->sendByText('客户端支付异常:' . $time);

            $obj->SendMsg('15980277249', '客户端支付异常');
            $obj->SendMsg('18805029611', '客户端支付异常');
            $obj->SendMsg('18559910139', '客户端支付异常');
        }
        if (!$mini_pay && $mini_pay_count >= 10) {
            $this->info('小程序支付异常:' . $time);
            $dingAlerts->sendByText('小程序支付异常:' . $time);
            
            $obj->SendMsg('15980277249', '小程序支付异常');
            $obj->SendMsg('18805029611', '小程序支付异常');
            $obj->SendMsg('18559910139', '小程序支付异常');
        }

        $this->info("结束");
    }
}
