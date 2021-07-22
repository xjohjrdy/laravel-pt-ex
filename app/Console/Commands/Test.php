<?php

namespace App\Console\Commands;
use function Amp\ParallelFunctions\parallelMap;
use function Amp\Promise\wait;

use App\Entitys\App\ReturnBack;
use App\Entitys\App\TaobaoUser;
use App\Services\Common\UserMoney;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:Test';

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
        $userMoneyService = new UserMoney();
        $taobaoUserModel = new TaobaoUser();
        $shopReturnModel = new ReturnBack();
        $sql = "select s1.id,s1.order_id as order_id,s2.id as one_id,s1.app_id,s1.real_price as real_price,ROUND(s2.real_price * s2.number + s2.postage,1) as real_price2,s2.postage, s1.ptb_number,sub_sign from lc_shop_orders s1, lc_shop_orders_one s2
where s1.id = s2.order_id and s1.real_price > 0 and s2.id in (
select orders_one_id from lc_shop_return_back where `status` = 8 and remark_type = 0 and created_at > '2020-03-01')";
        $list = DB::connection('app38')->select($sql);
        foreach ($list as $key=>$item){
            try{
                DB::connection('app38')->beginTransaction();
                $app_id = $item->app_id;
                $sub_money = $item->real_price;
                $user_money = $taobaoUserModel->getUserMoney($app_id);
                $order_id = $item->order_id;
                $one_id = $item->one_id;
                if($user_money >= $sub_money){
                    $this->info('用户：' . $app_id . '--' . '余额：' . $user_money . '--扣除：'. $sub_money);
                    $userMoneyService->minusCnyAndLogNoTrans($app_id, $sub_money, 20004, $order_id);
                    $shopReturnModel->where(['orders_one_id' => $one_id])->update(['status' => 10, 'remark_type' => 1]);
                }
                DB::connection('app38')->commit();
            }catch (\Throwable $e) {
                DB::connection('app38')->rollBack();
                $this->info($e->getMessage());
                continue;
            }
        }

    }
}
