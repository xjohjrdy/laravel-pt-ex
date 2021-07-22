<?php

namespace App\Console\Commands;

use App\Services\JdCommodity\JdCommandServices;
use App\Services\Other\OtherCountService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class JdFalseCommission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:jdFalseCommission';

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
        /*
        //脚本统计便捷方案
        $this->info("start");
        $c_start_time = microtime(true);//统计执行开始时间
        $count_number = 0; //统计总数
        $bar = $this->output->createProgressBar($count_number);

        //-----------------------------
        $bar->advance();//推动进度条
        //-----------------------------


        //统计结束
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");
        */

        $this->process();


    }


    public function process()
    {
        $s_jd = new JdCommandServices();

        $this->info("start");
        $c_start_time = microtime(true);//统计执行开始时间
        $start_time = date("Y-m-d H:i:s", strtotime("-51 day")); // 定义51天前的时间

        $count_number = $s_jd->countJdOrders($start_time); //统计总数
        $bar = $this->output->createProgressBar($count_number);

        $page_size = 1000;  //页大小

        $page_total = ceil($count_number / $page_size); //总页数

        for ($i = 1; $i <= $page_total; $i++) {
            $cut_page_data = $s_jd->getCutData($page_size, $i, $start_time);
            foreach ($cut_page_data as $datum) {

                $bar->advance();//推动进度条

                $trade_id = $datum->orderId;   //得到订单id
                $sku_id = $datum->skuId;   //得到sku_id 商品id
                $app_id = $datum->app_id;  //取得用户appid
                $maid_money = $datum->actualFee;    //实际佣金，实际获得佣金
                $frozen_sku_num = $datum->frozenSkuNum; //售后数量
                $sku_return_num = $datum->skuReturnNum; //商品已退货数量
                $valid_code = $datum->validCode; //16 17 18 16.已付款,17.已完成,18.已结算

//                $this->info('ing:' . $trade_id . ' - ' . $sku_id);


                //简单防重放
                if (Cache::has('j_f_o_' . $trade_id . $sku_id)) {
                    continue;
                }
                Cache::put('j_f_o_' . $trade_id . $sku_id, 1, 0.2);//缓存12秒


                $sus_order_num = $frozen_sku_num + $sku_return_num;

                $is_datum_data = $s_jd->isDatum($trade_id, $sku_id);

                if (empty($is_datum_data) && $sus_order_num > 0) {
                    continue;
                }

                if ($is_datum_data && $sus_order_num > 0) {
                    //  删除分佣操作
                    $s_jd->delDatum($trade_id, $sku_id);
                    continue;
                }

                //到这里都是没有售后的订单
                if ($is_datum_data) {
                    //用户没有退款，并且已经处理过分佣的订单
                    if (!in_array($valid_code, [16, 17, 18])) {
                        //  删除分佣操作
                        $s_jd->delDatum($trade_id, $sku_id);
                    }
                    continue;
                }

                //  进行插入操作，并进行分佣

                if ($valid_code == 17) {
                    $c_params = [
                        'app_id' => $app_id,    //该笔订单的主人app_id
                        'trade_id' => $trade_id,    //该笔订单大id
                        'sku_id' => $sku_id,    //该笔订单里面的子订单商品id
                        'maid_money' => $maid_money,    //该笔订单完整佣金
                    ];

                    $s_jd->commissionV2($c_params);
                }

            }
        }

        //统计结束
        $bar->finish();
        $c_end_time = microtime(true);
        $consuming = gmstrftime('%H:%M:%S', round($c_end_time - $c_start_time));
        $this->info("\r\n End Time-consuming {$consuming} s\r\n");

    }
}
