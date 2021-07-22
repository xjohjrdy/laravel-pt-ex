<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAccount;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\PtMoneyChangeLog;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use App\Services\Common\UserMoney;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PtbToMoneyAllUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PtbToMoneyAllUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '所有用户的ptb转余额脚本';


    private $ptMoneyModel = null;
    private $appUserModel = null;
    private $adUserModel = null;
    private $userAccountModel = null;
    private $userMoneyService = null;

    public function __construct()
    {
        parent::__construct();
        $this->ptMoneyModel = new PtMoneyChangeLog();
        $this->appUserModel = new AppUserInfo();
        $this->adUserModel = new AdUserInfo();
        $this->userAccountModel = new UserAccount();
        $this->userMoneyService = new UserMoney();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $page_size = 10000;
        $count = $this->userAccountModel->where('extcredits4', '>', 0)->count();
        $page_total = ceil($count / $page_size); #总页数
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $list = $this->userAccountModel->where('extcredits4', '>', '0')->forPage(1, $page_size)->get(['extcredits4', 'uid']);
            foreach ($list as $key => $item) {
                $uid = $item['uid'];
                $app_user = $this->adUserModel->getUserById($uid);
                if (empty($app_user)) {
                    continue;
                }
                $app_id = $app_user['pt_id'];
                $change_log = $this->ptMoneyModel->getLogByAppId($app_id);
                $ptb = $item['extcredits4']; # 葡萄币数量
                if($ptb <= 0){ // 如果用户葡萄币小于等于0 直接跳过
                    continue;
                }
                $money = $ptb / 10;
                if (empty($change_log)) {
                    try {
                        DB::connection('app38')->beginTransaction();
                        DB::connection('a1191125678')->beginTransaction();
                        $this->userAccountModel->where(['uid' => $uid])->update(['extcredits4' => DB::raw("extcredits4 - " . $ptb)]); // 扣除ptb
                        $this->plusCnyAndLog($app_id, $money, '62'); // 增加余额
                        $this->ptMoneyModel->ptb2RmbLog($app_id, $ptb, $money); // 记录新日志表
                        DB::connection('app38')->commit();
                        DB::connection('a1191125678')->commit();
                    } catch (\Exception $exception) {
                        DB::connection('app38')->rollBack();
                        DB::connection('a1191125678')->rollBack();
                        $this->info($exception->getMessage() . $exception->getLine());
                    }
                }
            }
            $bar->advance();
        }
        $bar->finish();
    }

    public function plusCnyAndLog($app_id, $cny, $from_type, $from_info = '')
    {

        try {
            if ($cny <= 0) throw new \Exception('cny value error');
            $taobao_user = new TaobaoUser();//用户真实分佣表
            $taobao_change_user_log = new TaobaoChangeUserLog();//记录日志表
            $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();
            if (empty($obj_taobao_user)) {
                $obj_taobao_user = $taobao_user->create([
                    'app_id' => $app_id,
                    'money' => $cny,
                    'next_money' => 0,
                    'last_money' => 0,
                ]);
            } else {
                $obj_taobao_user->money = $obj_taobao_user->money + $cny;
                $obj_taobao_user->save();
            }
            $taobao_change_user_log->create([
                'app_id' => $app_id,
                'before_money' => $obj_taobao_user->money - $cny, //变化前
                'before_next_money' => $cny,  //变化的值
                'before_last_money' => 0,
                'after_money' => $obj_taobao_user->money,   //变化后
                'after_next_money' => 0,
                'after_last_money' => 0,
                'from_type' => $from_type,
                'from_info' => $from_info,
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return true;
    }
}
