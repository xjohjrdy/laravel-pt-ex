<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\MorningReports;
use App\Entitys\App\MorningSchemes;
use App\Entitys\App\MorningUser;
use App\Entitys\App\MorningUserRecords;
use App\Services\Common\UserMoney;
use App\Services\Morning\MorningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MorningMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MorningMoney';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '早安打卡每日瓜分金额';
    private $activity_no = ''; // 当前期号
    private $morningRecordsModel = null;
    private $morningUserModel = null;
    private $morningSchemesModel = null;
    private $morningReportModel = null;
    private $userMoneyService = null;
    private $time = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        //
        $time = time();
        $this->time = $time;
        $this->activity_no = date('Ymd', $time - 86400); // 获取上一期
        $this->morningRecordsModel = new MorningUserRecords();
        $this->morningUserModel = new MorningUser();
        $this->morningSchemesModel = new MorningSchemes();
        $this->morningReportModel = new MorningReports();
        $this->userMoneyService = new UserMoney();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info($this->activity_no);
        $page_size = 10000;
        #todo 获取早期之星 已废弃
        $year = date('Y', $this->time);
        $month = date('m', $this->time);
        $day = date('d', $this->time);
        $time_2 = mktime(10, 0, 0, $month, $day, $year); // 等级2的分隔时间戳 打卡结束时间， 脚本开始执行
        if ($this->time < $time_2) {
            $this->info("未到瓜分时间， 请十点后操作");
            return;
        }
//        $earliest = $this->morningRecordsModel->where(['activity_no' => $this->activity_no])->orderBy('sign_time', 'desc')->first();
//        $userModel = new AppUserInfo();
//        $user = $userModel->getUserById($earliest['app_id']);
//
//        if (!empty($user)) {
//            $name = $user['real_name'];
//            $name_len = mb_strlen($name);
//            if ($name_len == 2) {
//                $name = mb_substr($name, 0, 1) . '**';
//            } else if ($name_len >= 3) {
//                $start = mb_substr($name, 0, 1);
//                $end = mb_substr($name, $name_len - 1, 1);
//                $name = $start . '**' . $end;
//            }
//            $img = $user['avatar'];
//            $value = date('i:s', $earliest['sign_time']);
//            $res = [
//                'title' => '早起之星',
//                'name' => $name,
//                'value' => $value . '打卡',
//                'img' => $img,
//                'flag' => 1,
//            ];
//            Cache::put(MorningService::ACTIVITY_EARLIER_KEY, $res, 60 * 48); // 存入缓存
//        }
        $schemes = $this->morningSchemesModel->get()->toArray(); // 获取全部方案
        try {
            #todo 按早餐打卡方案分类统计本期报名人数，实际打卡人数。
            foreach ($schemes as $scheme) {
                $average_money = 0; // 当前方案可瓜分金额
                $flag_1_max = 0; // 当状态为 1 时，最大可瓜分人数
                $flag_2_max = 0;
                $flag_1_cur = 0; // 当前瓜分至第几人
                $limit_money = 0.01; // 沉淀金额获取条件值
                $limit_get_money = 0.01; // 用户满足条件最低瓜分金额
                $get_money = 0; // 沉淀金额
                $apply_total_user = $this->morningRecordsModel
                    ->where(['activity_no' => $this->activity_no, 'scheme_id' => $scheme['id']])
                    ->count();
                $success_total_user = $this->morningRecordsModel
                    ->where(['activity_no' => $this->activity_no, 'scheme_id' => $scheme['id']])
                    ->whereNotNull('sign_time')->count();
                #todo 先判断 报名人数和打卡人数是否一样，一样就多做操作原路返回金额 标记flag
                if ($success_total_user == $apply_total_user) {
                    $flag = 0; // 原路返回
                } else { #todo 有未打卡用户
                    $can_money = ($apply_total_user - $success_total_user) * $scheme['money'];
                    $average_money = round($can_money / $success_total_user, 2);
                    if ($average_money <= $limit_money) { // 可瓜分的金额
                        $flag = 1; // 按打卡时间前z名 瓜分 0.01 后续原金额返回
                        $flag_1_max = round($can_money * (1 / $limit_money), 0);
                    } else {
                        $flag = 2; // 每人瓜分 随机 limit_get_money 至 flag_2_max ,此状态随机抽取沉淀金
                        $money2 = $scheme['money'] * 0.12;
                        if($average_money < $money2){
                            $flag_2_max = $average_money * 100;
                        } else {
                            $flag_2_max = $money2 * 100;
                        }
                    }
                }
                $this->info('******************');
                $this->info('方案id：' . $scheme['id']);
                $this->info('报名用户：' . $apply_total_user);
                $this->info('打卡用户：' . $success_total_user);
                $this->info('flag：' . $flag);
                $this->info('flag_1_max：' . $flag_1_max);
                $this->info('average_money：' . $average_money);
                $this->info('******************');
                #todo 根据今日统计的人数报表，根据报表瓜分金额, 记录沉淀金额
                $page = 1;
                while (1) {
                    $records_list = $this->morningRecordsModel->where(['activity_no' => $this->activity_no, 'scheme_id' => $scheme['id']])
                        ->whereNotNull('sign_time')
                        ->orderBy('sign_time', 'asc')
                        ->forPage($page, $page_size)->get();
                    foreach ($records_list as $record) {
                        if ($record['success_money'] == 0) {
                            try {
                                DB::connection('app38')->beginTransaction();
                                $app_id = $record['app_id'];
                                $money = $scheme['money'];
                                $from_type = 10003;
                                $from_info = $flag;
                                switch ($flag) {
                                    case 0:
                                        break;
                                    case 1:
                                        $flag_1_cur++;
                                        if ($flag_1_cur <= $flag_1_max) {
                                            $money = $money + $limit_get_money;
                                        }
                                        break;
                                    case 2:
                                        $rate_money = mt_rand($limit_money * 100, $flag_2_max - 1);
//                                        $get_money = $average_money - round($rate_money / 100, 2);
                                        $money = $money + round($rate_money / 100, 2);
                                        break;
                                }
//                            $this->info($app_id . '--' . $money . '--' . $from_type . '--' . $from_info);
                                $this->userMoneyService->plusCnyAndLogNoTrans($app_id, $money, $from_type, $from_info);
                                $this->morningUserModel->where(['app_id' => $app_id])->update([
                                    'success_total_money' => DB::raw('success_total_money +' . $money)
                                ]);
                                $this->morningRecordsModel->where(['app_id' => $app_id, 'activity_no' => $this->activity_no])->update([
                                    'success_money' => $money
                                ]);
                                DB::connection('app38')->commit();
                            } catch (\Throwable $e) {
                                DB::connection('app38')->rollBack();
                                $this->info($e->getMessage());
                            }
                        }
                    }
                    $length = $apply_total_user;
                    if ($length < $page_size) {
                        break;
                    } else {
                        $page++;
                    }
                }
                $total_success_money = $this->morningRecordsModel
                    ->where(['activity_no' => $this->activity_no, 'scheme_id' => $scheme['id']])->sum('success_money');
                $get_money = $apply_total_user * $scheme['money'] - $total_success_money;
                $exit = $this->morningReportModel->where(['activity_no' => $this->activity_no, 'scheme_id' => $scheme['id']])->exists();
                if ($exit) {
                    $this->morningReportModel->where(['activity_no' => $this->activity_no, 'scheme_id' => $scheme['id']])
                        ->update([
                            'apply_count' => $apply_total_user,
                            'sign_count' => $success_total_user,
                            'get_money' => $get_money,
                        ]);
                } else {
                    $this->morningReportModel->create([
                        'apply_count' => $apply_total_user,
                        'sign_count' => $success_total_user,
                        'activity_no' => $this->activity_no,
                        'get_money' => $get_money,
                        'scheme_id' => $scheme['id']
                    ]);
                }
            }
        } catch (\Throwable $e) {
            $this->info($e->getMessage());
        }
    }
}
