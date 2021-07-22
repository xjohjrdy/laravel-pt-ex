<?php

namespace App\Services\CoinPlate;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CoinChangeLog;
use App\Entitys\App\CoinTaskConfig;
use App\Entitys\App\CoinTaskFinishLog;
use App\Entitys\App\CoinUser;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CoinCommonService
{

    private $coinChangeModel;
    private $coinUserModel;
    private $coinUserInfo;
    private $app_id;
    private $taskLogModel;
    private $taskModel;

    public function __construct($app_id)
    {
        $this->coinChangeModel = new CoinChangeLog();
        $this->coinUserModel = new CoinUser();
        $this->taskModel = new CoinTaskConfig();
        $this->taskLogModel = new CoinTaskFinishLog();
        $this->app_id = $app_id;
    }

    private function setCoinUserInfo()
    {
        if (empty($this->coinUserInfo)) {
            $userBModel = $this->coinUserModel->rightJoin('lc_user', 'lc_coin_user.app_id', '=', 'lc_user.id')->where(['lc_user.id' => $this->app_id]);
            $this->coinUserInfo = $userBModel->first(['lc_user.id as user_id', 'lc_user.*', 'lc_coin_user.*']);
            if (empty($this->coinUserInfo['app_id'])) {
                $is_new = 1;
                if ($this->coinUserInfo['create_time'] > strtotime('2020-07-01')) { // 判断是否是新用户
                    $is_new = 0;
                }
                $this->coinUserModel->create([
                    'app_id' => $this->app_id,
                    'is_new' => $is_new
                ]);
                $this->coinUserInfo = $userBModel->first(['lc_user.id as user_id', 'lc_user.*', 'lc_coin_user.*']);
            }
        }


    }

    /**
     * 用户金币新增公用方法
     * @param $coin 变动金币数量
     * @param $type 类型
     * @param $remark 备注
     * @throws \Exception
     */
    public function plusCoin($coin, $type, $remark = '')
    {
        $this->setCoinUserInfo();
        if (!is_numeric($coin) || $coin <= 0) {
            throw new \Exception('金额必须为整形并且大于0');
        }
        try {
            CoinConst::plusLogDesc($type);
        } catch (\Throwable $exception) {
            throw new \Exception('您输入的类型有误，请先去app\Services\CoinPlate\CoinConst注册:' . $exception->getMessage());
        }
        $after = $this->coinUserInfo['coin'] + $coin;
        $this->coinChangeModel->create([
            'app_id' => $this->app_id,
            'type' => $type,
            'change_coin' => $coin,
            'after' => $after,
            'remark' => $remark
        ]);
        $this->coinUserModel->where([
            'app_id' => $this->app_id
        ])->update(['coin' => $after]);
    }

    /**
     * 用户金币扣除公用方法
     * @param $coin 变动金币数量
     * @param $type 类型
     * @param $remark 备注
     * @throws \Exception
     */
    public function minusCoin($coin, $type, $remark = '')
    {
        $this->setCoinUserInfo();
        if (!is_numeric($coin) || $coin >= 0) {
            throw new \Exception('金额必须为整形并且小于0');
        }
        try {
            CoinConst::minusLogDesc($type);
        } catch (\Throwable $exception) {
            throw new \Exception('您输入的类型有误，请先去app\Services\CoinPlate\CoinConst注册:' . $exception->getMessage());
        }
        $after = $this->coinUserInfo['coin'] + $coin;
        $this->coinChangeModel->create([
            'app_id' => $this->app_id,
            'type' => $type,
            'change_coin' => $coin,
            'after' => $after,
            'remark' => $remark
        ]);
        $this->coinUserModel->where([
            'app_id' => $this->app_id
        ])->update(['coin' => $after]);
    }


    /**
     * 成功完成任务
     * @param $task_id 任务id
     * @param $time 任务完成时间戳
     * @param $plus_task_money 默认是否加任务获得的金币， 默认是，特殊的需要传false 如签到
     */
    public function successTask($task_id, $time, $plus_task_money = true)
    {
        $task = $this->taskModel->where(['id' => $task_id])->first();
        if (empty($task)) {
            throw new \Exception('无效得任务id', 1000);
        }
        $beginToday = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $endToday = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 1;
        $max_count = $task['max_count'];
        $type = $task['type'];
        $plus_type = 0;
        if ($type == CoinConst::TASK_DAILY) {
            $plus_type = CoinConst::COIN_PLUS_TASK_DAILY;
            $query = $this->taskLogModel->where(['app_id' => $this->app_id, 'task_id' => $task_id])->whereBetween('finish_time', [$beginToday, $endToday]);
            $count = $query->count();
            if ($count >= $max_count) {
                throw new \Exception('今日该任务完成量已达上限！', 1000);
            }
        }

        if ($type == CoinConst::TASK_NEW) {
            $appUserInfo = new AppUserInfo();
            $user_id = $appUserInfo->where('id', $this->app_id)->value('create_time');
            $new_user_time = strtotime('2020-07-01');//定义新人注册的时间
            if ($user_id < $new_user_time) {
                throw new \Exception('你不是新用户,不能完成新手任务！', 1000);
            }

            $plus_type = CoinConst::COIN_PLUS_TASK_NEW;
            $query = $this->taskLogModel->where(['app_id' => $this->app_id, 'task_id' => $task_id]);
            $count = $query->count();
            if ($count >= $max_count) {
                throw new \Exception('你已经完成过该任务了！', 1000);
            }
        }
        if ($plus_task_money) {
            $this->plusCoin($task['number'], $plus_type, $task['title']);
        }
        $this->taskLogModel->create([
            'app_id' => $this->app_id,
            'task_id' => $task_id,
            'get_coin' => $task['number'],
            'finish_time' => $time
        ]);

        // 更新任务缓存
        $this->updateTaskCacheInfo($type, $time);

        return $task['number'];
    }


    private function updateTaskCacheInfo($type, $time)
    {
        $beginToday = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $endToday = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 1;
//        // 更新任务缓存
//        if (Cache::has(CoinConst::TASK_HASH_KEY . $type . '_'  . $this->app_id)) {
//            return Cache::get(CoinConst::TASK_HASH_KEY . $type . '_'  . $this->app_id);
//        }
        $t1 = $this->taskModel->getTable();
        $t2 = $this->taskLogModel->getTable();
        $task_list = $this->taskModel->where(['type' => $type])->orderByDesc('sort')->get([
            'id', 'type', 'url_ico', 'title', 'desc', 'number', 'sort', 'btn_text', 'max_count', 'show_count_flag', 'redirect_url', 'page_params',
            'index', 'redirect_type', 'extra_params', 'show_text'
        ]);
        switch ($type) {
            case CoinConst::TASK_DAILY :
                $finish_list = $this->taskLogModel->leftJoin($t1, $t1 . '.id', '=', $t2 . '.task_id')
                    ->where([$t2 . '.app_id' => $this->app_id])
                    ->where([$t1 . '.type' => $type])
                    ->whereBetween($t2 . '.finish_time', [$beginToday, $endToday])
                    ->groupBy('task_id', 'app_id')->get(['app_id', 'task_id', DB::raw('count(task_id) as count')]);
                $task_list = $task_list->toArray();
                $finish_list = collect($finish_list->toArray());
                foreach ($task_list as $key => $item) {
                    $cur_count = $finish_list->where('task_id', $item['id']);
                    $data = $cur_count->first();
                    if (empty($data)) {
                        $data = ['count' => 0];
                    }
                    $task_list[$key]['page_params'] = json_decode($item['page_params']);
                    $task_list[$key]['count'] = $data['count'];
                    if ($data['count'] >= $item['max_count']) {
                        $task_list[$key]['all_ok'] = 1;
                        $task_list[$key]['btn_text'] = '已完成';
                    } else {
                        $task_list[$key]['all_ok'] = 0;
                    }
                }
                break;
            case CoinConst::TASK_NEW :
                $finish_list = $this->taskLogModel->leftJoin($t1, $t1 . '.id', '=', $t2 . '.task_id')
                    ->where([$t2 . '.app_id' => $this->app_id])
                    ->where([$t1 . '.type' => $type])
                    ->groupBy('task_id', 'app_id')->get(['app_id', 'task_id', DB::raw('count(task_id) as count')]);
                $task_list = $task_list->toArray();
                $finish_list = collect($finish_list->toArray());
                $all_new_task = 1;
                foreach ($task_list as $key => $item) {
                    $cur_count = $finish_list->where('task_id', $item['id']);
                    $data = $cur_count->first();
                    if (empty($data)) {
                        $data = ['count' => 0];
                    }
                    $task_list[$key]['page_params'] = json_decode($item['page_params']);
                    $task_list[$key]['count'] = $data['count'];
                    if ($data['count'] >= $item['max_count']) {
                        $task_list[$key]['all_ok'] = 1;
                        $task_list[$key]['btn_text'] = '已完成';
                    } else {
                        $task_list[$key]['all_ok'] = 0;
                        $all_new_task = 0;
                    }
                }
                if ($all_new_task == 1) { // 完成全部新手任务
                    $this->coinUserModel->where(['app_id' => $this->app_id])->update(['is_new' => $all_new_task]);
                };
                break;
            case CoinConst::TASK_HIGH :
                ;
                break;
        }
//        Cache::put(CoinConst::TASK_HASH_KEY . $type . '_' . $this->app_id, $task_list, 5);
//        $redis = Redis::connection();
//        $redis->hset(CoinConst::TASK_HASH_KEY . $type, $this->app_id, json_encode($task_list));
//        $redis->hset(CoinConst::TASK_LAST_FINISH_TIME, $this->app_id, date('Ymd', $time));
        return $task_list;
    }

    /**
     * 获取任务信息
     */
    public function getTaskInfo($type)
    {
        $time = time();
        $date = date('Ymd', $time);
//        $redis = Redis::connection();
//        $last_update_date = $redis->hget(CoinConst::TASK_LAST_FINISH_TIME, $this->app_id);
//        $task_list = null;
        $task_list = $this->updateTaskCacheInfo($type, $time);
//        if ($type == CoinConst::TASK_DAILY && (empty($last_update_date) || $date != $last_update_date)) {
//            $task_list = $this->updateTaskCacheInfo($type, $time);
//        } else {
//            $task_list = $redis->hget(CoinConst::TASK_HASH_KEY . $type, $this->app_id);
//
//            $task_list = json_decode($task_list);
//            if (empty($task_list)) {
//                $task_list = $this->updateTaskCacheInfo($type, $time);
//            }
//        }
        return $task_list;
    }

    /**
     * 新增转盘数量
     * @param $number
     */
    public function incrementTurntableNum($number)
    {
        $this->setCoinUserInfo();
        $this->coinUserModel->where('app_id', $this->app_id)->increment('turntable_count', $number);
    }
}
