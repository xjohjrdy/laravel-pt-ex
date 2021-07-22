<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CommandConfig;
use App\Entitys\App\StartPageIndex;
use App\Services\Xin\CalUserData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalUserInitDataEveryday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CalUserInitDataEveryday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command 计算用户初始展示的相关数据，每天执行一次';

    private $userModel = null;
    private $commandModel = null;
    private $current = ''; // 单前执行的用户

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new AppUserInfo();
        $this->commandModel = new CommandConfig();
        $this->commandModel->setCommandName($this->signature);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $end_timestamp = time();
        $use_timestamp = $end_timestamp;
        $command = $this->commandModel->initCommandInfo($end_timestamp);
        $startPageModel = new StartPageIndex();
        // 获取脚本执行的开始日期 相当于上次执行成功的结束日期
        $start_timestamp = $command['start_time']; //
        $page = $command['page_index'];
        $page_size = $command['page_size'];
        $retry_time = $command['end_time'];
        if ($retry_time > 0) { // 如果不为0，则表示该时间断内未全部执行完成
            $end_timestamp = $retry_time; // 结束时间
        }
        $start_time = date('Y-m-d H:i:s', $start_timestamp);
        $end_time = date('Y-m-d H:i:s', $end_timestamp);
//        $this->info($command->toJson());
        $this->info('开始： ' . $start_time . ' - ' . $end_time );
        $calUserDataService = new CalUserData($start_time, $end_time, $start_timestamp, $end_timestamp);
        $calUserDataService->setNewColumnKey($command);
        while (1) {
            try {
                DB::connection('app38')->beginTransaction();
                $user_list = $this->userModel->orderBy('id', 'asc')->forPage($page, $page_size)->get(['id']);
                $count = count($user_list);
                $this->output->write($page . '|');
                foreach ($user_list as $key => $item) {
                    $this->current = $item['id'];
                    $calUserDataService->setAppId($this->current);
                    $shop_result = $calUserDataService->getShopOrderCount();
                    $bonus_log_sum = $calUserDataService->getTotalDividendIncome(); // 总分红收益
                    $ad_maid_sum = $calUserDataService->getAdIncome();//广告联盟收益
                    $shop_maid_sum = $calUserDataService->getShopMaidIncome();//商城分佣收益
                    $option_maid_sum = $calUserDataService->getOptionIncome();//期权收益
                    $shop_orders_count = empty($shop_result['number']) ? 0 : $shop_result['number'];//商城订单数
                    $shop_all_sum = empty($shop_result['money']) ? 0 : $shop_result['money'];//商城总业绩
                    $voip_sum = $calUserDataService->getMobilePassMoney();//新版我的通讯总额
                    $all_user_answer = $calUserDataService->getReplyWorkCount();// 用户已回复工单的总数
                    $all_user_get = $calUserDataService->getUserAllDivideIncome();// 用户所有分佣记录值

//                    $pretend_all_user_get = $calUserDataService->getUserAllPreIncome();// 用户全部预估收入 非累加字段
//                    $team_vip_sum = $calUserDataService->getTeamVipCount();//团队VIP数 非累加字段
//                    $taobao_two_prediction_now = $calUserDataService->getCurrentPreIncome();// 本月淘宝预估收入 非累加字段
                    $contition = $startPageModel->where(['app_id' => $this->current]);
                    if ($contition->exists()) {
                        $entity = [
                            'bonus_log_sum' => DB::raw('bonus_log_sum + ' . $bonus_log_sum),
                            'ad_maid_sum' => DB::raw('ad_maid_sum + ' . $ad_maid_sum),
                            'shop_maid_sum' => DB::raw('shop_maid_sum + ' . $shop_maid_sum),
                            'option_maid_sum' => DB::raw('option_maid_sum + ' . $option_maid_sum),
                            'shop_orders_count' => DB::raw('shop_orders_count + ' . $shop_orders_count),
                            'shop_all_sum' => DB::raw('shop_all_sum + ' . $shop_all_sum),
                            'voip_sum' => DB::raw('voip_sum + ' . $voip_sum),
                            'all_user_answer' => DB::raw('all_user_answer + ' . $all_user_answer),
                            'all_user_get' => DB::raw('all_user_get + ' . $all_user_get),
//                            'pretend_all_user_get' => $pretend_all_user_get,
//                            'team_vip_sum' => $team_vip_sum,
//                            'taobao_two_prediction_now' => $taobao_two_prediction_now,
                        ];
                        $contition->update($entity);
                    } else {
                        $contition->create([
                            'app_id' => $this->current,
                            'bonus_log_sum' => $bonus_log_sum,
                            'ad_maid_sum' => $ad_maid_sum,
                            'shop_maid_sum' => $shop_maid_sum,
                            'option_maid_sum' => $option_maid_sum,
                            'shop_orders_count' => $shop_orders_count,
                            'shop_all_sum' => $shop_all_sum,
                            'voip_sum' => $voip_sum,
                            'all_user_answer' => $all_user_answer,
//                            'pretend_all_user_get' => $pretend_all_user_get,
                            'all_user_get' => $all_user_get,
//                            'team_vip_sum' => $team_vip_sum,
//                            'taobao_two_prediction_now' => $taobao_two_prediction_now,
                        ]);
                    }
                }
                $page++;
                $this->commandModel->pageSuccess($page);
                if ($count < $page_size) { // 全部执行完毕
                    $this->commandModel->allSuccess($end_timestamp + 1);
                    DB::connection('app38')->commit();
                    break;
                }
                DB::connection('app38')->commit();
                unset($user_list, $contition);
            } catch (\Exception $e) {
                DB::connection('app38')->rollBack();
                $msg = '第' . $page . '页, ID：' . $this->current . '||' . $e->getMessage() . 'line:' . $e->getLine();
                $this->info($msg);
                $this->commandModel->error($msg);
                break;
            }
        }
        $this->info('end! 耗时：' . round((time() - $use_timestamp) / 60, 2) . '分钟');

    }


}
