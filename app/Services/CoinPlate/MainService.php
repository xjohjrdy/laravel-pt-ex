<?php

namespace App\Services\CoinPlate;

use App\Entitys\App\CoinMenu;
use App\Entitys\App\CoinTaskConfig;
use App\Entitys\App\CoinUser;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MainService
{

    private $sign_items = [
        ['coin' => 10, 'big_flag' => 0,],
        ['coin' => 10, 'big_flag' => 0,],
        ['coin' => 30, 'big_flag' => 1,],
        ['coin' => 20, 'big_flag' => 0,],
        ['coin' => 20, 'big_flag' => 0,],
        ['coin' => 20, 'big_flag' => 0,],
        ['coin' => 30, 'big_flag' => 1,],
    ]; // 连续签到7天对应获取金币数 [, 第1天 , ....第6天第7天 ]

    private $coinUserModel;
    private $coinUser = [];
    private $before_date; // 前一天年月日
    private $current_date; // 当前得年月日
    private $current_time; // 当前时间戳，初始化时构造参数赋值
    private $app_id;

    public function __construct($app_id, $current_time)
    {
        $this->app_id = $app_id;
        $this->coinUserModel = new CoinUser();
        $userBModel = $this->coinUserModel->rightJoin('lc_user', 'lc_coin_user.app_id', '=', 'lc_user.id')->where(['lc_user.id' => $app_id]);
        $this->coinUser = $userBModel->first(['lc_user.id as user_id', 'lc_user.*', 'lc_coin_user.*']);
        $this->current_time = $current_time;
        if (empty($this->coinUser['user_id'])) {
            throw new \Exception('无效用户', 1000);
        }
        if (empty($this->coinUser['app_id'])) {
            $is_new = 1;
            if ($this->coinUser['create_time'] > strtotime('2020-07-01')) { // 判断是否是新用户
                $is_new = 0;
            }
            $this->coinUserModel->create([
                'app_id' => $app_id,
                'is_new' => $is_new
            ]);
            $this->coinUser = $userBModel->first(['lc_user.id as user_id', 'lc_user.*', 'lc_coin_user.*']);
        }
        $this->current_date = date('Ymd', $current_time);
        $this->before_date = date('Ymd', $current_time - 86400);
    }

    public function getTodaySignInfo()
    {
        $sign_date = $this->coinUser['sign_time'];
        $sign_days = $this->coinUser['sign_days']; // 代表连续签到天数
        $continue_days = 1; // 连续天数 默认第一天开始
        $sign_flag = 2; // 0 未签到 1 已签到 2 去签到


        if ($sign_date == $this->current_date) { // 当日已签到
            $sign_flag = 1;
            $continue_days = $sign_days % 7;
            if($sign_days == 0){
                $sign_days = 1;
            }
            if ($continue_days == 0) {
                $continue_days = 7;
            }
        } else { // 还未签到
            #todo 判断是否断签
            if ($this->before_date == $sign_date) { // 未断签
                $sign_days += 1;
                $sign_flag = 2;
                $continue_days = $sign_days % 7;
                if ($continue_days == 0) {
                    $continue_days = 7;
                }
            } else {
                $sign_days = 1;
                $sign_flag = 2;
                $continue_days = 1;
            }
        }
        $compare_key = $continue_days - 1;


        return [
            'key' => $compare_key,
            'sign_flag' => $sign_flag,
            'sign_days' => $sign_days,
        ];
    }

    /**
     * 获取签到数据
     */
    public function getSignItemInfo()
    {
        $info = $this->getTodaySignInfo();
        $sign_items = $this->sign_items;
        $compare_key = $info['key'];
        $sign_flag = $info['sign_flag'];
        foreach ($sign_items as $key => $item) {
            if ($key < $compare_key) {
                $sign_items[$key]['sign_flag'] = 1;
            } else if ($key == $compare_key) {
                $sign_items[$key]['sign_flag'] = $sign_flag;
            } else {
                $sign_items[$key]['sign_flag'] = 0;
            }
        }
        return $sign_items;
    }


    /**
     * 用户签到
     */
    public function coinSign()
    {
        try {
            $time = time();
            $info = $this->getTodaySignInfo();
            $sign_flag = $info['sign_flag'];
            $sign_days = $info['sign_days'];
            $compare_key = $info['key'];
            $coin_number = $this->sign_items[$compare_key]['coin'];
            if ($sign_flag != 2) {
                throw new \Exception('您已经签到过了！', 1000);
            }
            $coinCommonService = new CoinCommonService($this->app_id);
            $taskModel = new CoinTaskConfig();
            $task_id = 6;
            $sign_task = $taskModel->where(['id' => $task_id, 'type' => CoinConst::TASK_DAILY])->first();
            if (empty($sign_task)) {
                throw new \Exception('签到任务尚未配置！请联系开发人员配置。', 1000);
            }
            DB::connection('app38')->beginTransaction();
            $this->coinUserModel->where(['app_id' => $this->app_id])->update([
                'sign_time' => $this->current_date,
                'sign_days' => $sign_days
            ]);
            $coinCommonService->successTask($task_id, $time, false);
            // 签到功能获取金币比较特殊。
            $coinCommonService->plusCoin($coin_number, CoinConst::COIN_PLUS_TASK_DAILY, '每日签到');
            DB::connection('app38')->commit();

            return $coin_number;
        } catch (\Throwable $exception) {
            DB::connection('app38')->rollBack();
            throw new \Exception($exception->getMessage(), 1000);
        }

    }

    public function getMenuList()
    {
        $coinMenuModel = new CoinMenu();
        $list = $coinMenuModel->where(['show_flag' => 1])->orderByDesc('sort')->get([
            'title', 'image_url', 'hide_flag', 'redirect_url', 'page_params', 'extra_params', 'index', 'redirect_type',
        ]);
        $list = $list->toArray();
        foreach ($list as $key => $item) {
            $list[$key]['page_params'] = json_decode($item['page_params']);
        }
        return $list;
    }

    public function getCoinMainInfo()
    {
        $coinCommonService = new CoinCommonService($this->app_id);
        $task_daily = $coinCommonService->getTaskInfo(CoinConst::TASK_DAILY);
        if ($this->coinUser['is_new'] == 0) {
            $task_new = $coinCommonService->getTaskInfo(CoinConst::TASK_NEW);
        } else {
            $task_new = [];
        }
//        $new_task_info = $coinCommonService->getTaskInfo(CoinConst::TASK_NEW);
        return [
            'sign_list' => $this->getSignItemInfo(),
            'menus' => $this->getMenuList(),
            'task_new_list' => $task_new,
            'task_daily_list' => $task_daily,
            'avatar' => $this->coinUser['avatar'],
            'real_name' => $this->coinUser['real_name'],
            'coin' => $this->coinUser['coin'],
            'phone' => $this->coinUser['phone'],
            'android' => 101511,
            'ios' => 101512
        ];
    }
}
