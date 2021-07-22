<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\ShopIndex;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersOne;
use App\Services\Shop\Order;
use Illuminate\Console\Command;

class MaidNoMaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MaidNoMaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '补分佣未分佣的订单';

    protected $order_return;
    protected $shopIndex;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order_return,ShopIndex $shopIndex)
    {
        parent::__construct();
        $this->order_return = $order_return;
        $this->shopIndex = $shopIndex;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $shopOrdersOne = new  ShopOrdersOne();
        $shopOrders = new ShopOrders();
        $appUserInfo = new AppUserInfo();
        $shop_orders_one = $shopOrdersOne->where(['status' => 3])->get();
        foreach ($shop_orders_one as $order_one) {
            $order = $shopOrders->getById($order_one->order_id);
            if (!$order){
                continue;
            }
            $app_user = $appUserInfo->getUserById($order->app_id);
            if (!$app_user) {
                continue;
            }
            if ($order->all_profit_value <> 0.00) {
                if (!$this->shopIndex->isVipGoods($order_one->good_id)) {
                    $this->order_return->returnCommission($order->order_id, $order->all_profit_value, $app_user->parent_id);
                }
            }
            if ($order_one->id < 3428 && ($this->shopIndex->isVipGoods($order_one->good_id))) {
                $this->order_return->returnCommission($order->order_id, '800', $app_user->parent_id);
            }
        }
    }
}
