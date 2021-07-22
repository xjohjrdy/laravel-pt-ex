<?php

namespace App\Console\Commands;

use App\Entitys\App\CoinTurntableOrders;
use Illuminate\Console\Command;

class TurntableOrdersSignFor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TurntableOrdersSignFor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '转盘实物30天自动确认收货';

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
        //30天前的时间
        $start_time = date('Y-m-d H:i:s', time() - 2592000);

        //得到更新时间30天前的所有订单
        $coinTurntableOrders = new CoinTurntableOrders();
        $all_orders = $coinTurntableOrders->where('status', 2)
            ->where('updated_at', '<=', $start_time)
            ->get();

        $this->info('得到时间:' . $start_time . '前的所有订单,数量为' . count($all_orders));
        foreach ($all_orders as $v) {
            $this->info('自动确认订单号为:' . $v->order_no);
            $coinTurntableOrders->where(['order_no' => $v->order_no])->update(['status' => 3]);
        }

        $this->info("end");
    }
}
