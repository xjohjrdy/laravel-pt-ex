<?php

namespace App\Console\Commands;

use App\Entitys\App\JdTestWh;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class JdOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:jdOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command jdOrder';

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
        $this->info("===本次处理记录 开始");
        $time = date('Y-m-d H:i:s', time());
        $this->info($time);

        $client = new Client();
        $obj_jd_order = new JdTestWh();
        $end_time = strtotime(date('Y-m-d 23:59:59'));
        $begin_time = strtotime("-3 week");

        for ($i = 1; $i < 100; $i++) {
            $this->info('开始查询第' . $i . '页');
            $url = "apimd.haojingke.com/api/index/getorderlist1903?page={$i}&pagesize=100&uid=584021&type=1&begintime=1551369600&endtime={$end_time}";
            sleep(5);
            $this->info('访问api:' . $url);
            $obj_res = $client->request('POST', $url, ['verify' => false]);
            $arr_res = json_decode((string)$obj_res->getBody(), true);
            if (@$arr_res['status_code'] != 200) {
                $this->info('获取失败');
                break;
            }
            $is_die = true;
            foreach ($arr_res['data'] as $item) {
                $jd_order = $item['orderId'];
                if ($obj_jd_order->where('orderId', $jd_order)->exists()) {
                    $is_die = false;
                    $this->info('更新订单：' . $jd_order);
                    $obj_jd_order->where('orderId', $jd_order)->update($item);
                    continue;
                } else {
                    $is_die = false;
                }
                $this->info('插入订单：' . $jd_order);
                $obj_jd_order->create($item);
            }

            if ($is_die) {
                break;
            }
        }

        $this->info('===本次获取结束');
    }

}
