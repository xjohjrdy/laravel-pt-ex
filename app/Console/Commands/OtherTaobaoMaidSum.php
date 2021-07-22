<?php

namespace App\Console\Commands;

use App\Entitys\Other\TaobaoMaidOldOther;
use App\Services\Common\Time;
use App\Services\Qmshida\OtherUserMoneyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OtherTaobaoMaidSum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OtherTaobaoMaidSum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '第三方淘宝奖励统计';

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
        $page_size = 1000;
        //得到上月时间戳范围
        $obj_timestamp = new Time();
        $last_month = $obj_timestamp->getLastMonthTimestamp();
        $begin_date = date('Y-m-d H:i:s', $last_month[0]);
        $end_date = date('Y-m-d H:i:s', $last_month[1]);
        $moneyService = new OtherUserMoneyService();
        $maidModel = new TaobaoMaidOldOther();
        $sql = "select SUM(maid_money) AS maid_money, app_id from lc_taobao_maid_old 
            where `real` = 0 and deleted_at is null and created_at BETWEEN '{$begin_date}' and '{$end_date}' GROUP BY app_id";

        while (1) {
            $list = DB::connection('db001')->table(DB::raw("($sql) cc"))->forPage(1, $page_size)->get();
            $count = count($list); // 总数 4659
            foreach ($list as $key => $item) {
                try{
                    $money = $item->maid_money;
                    $app_id = $item->app_id;
                    $this->info($app_id . '-> ' . $money);
                    DB::connection('db001')->beginTransaction();
                    $moneyService->plusThreeUserMoney($app_id, $money, 0, 'FTB');
                    $maidModel->where('app_id', $app_id)->whereBetween('created_at', [$begin_date, $end_date])->update([
                        'real' => 1
                    ]);
                    DB::connection('db001')->commit();
                }catch (\Exception $e){
                    DB::connection('db001')->rollBack();
                    $this->info($e->getMessage() . ':' . $e->getLine());
                }
            }
            $this->info('处理：' . $count . '条');
            if ($count < $page_size) {
                break;
            }
        }
    }
}
