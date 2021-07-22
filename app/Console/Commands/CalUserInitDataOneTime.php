<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CommandConfig;
use App\Entitys\App\StartPageIndex;
use App\Services\Xin\CalUserData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalUserInitDataOneTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CalUserInitDataOneTime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用户单独计算用户预估收入，只计算一次';

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
                    $pretend_all_user_get = $calUserDataService->getUserAllPreIncome();// 用户全部预估收入 非累加字段
                    $contition = $startPageModel->where(['app_id' => $this->current]);
                    if ($contition->exists()) {
                        $entity = [
                            'pretend_all_user_get' => $pretend_all_user_get,
                        ];
                        $contition->update($entity);
                    } else {
                        $contition->create([
                            'app_id' => $this->current,
                            'pretend_all_user_get' => $pretend_all_user_get,
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
