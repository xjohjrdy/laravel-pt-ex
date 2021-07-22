<?php


namespace App\Services\Morning;


use App\Entitys\App\JsonConfig;
use App\Entitys\App\MorningReports;
use App\Entitys\App\MorningUser;
use App\Entitys\App\MorningUserRecords;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MorningService
{

    /**
     * 1 时间 00:00:01 - 08:00:00
     * 2 时间 08:00:01 - 10:00:00
     * 3 时间 10:00:01 - 00:00:00
     */
    private $time_status = null; // 当前时间等级
    private $activity_no = ''; // 当前期号
    private $last_activity_no = ''; // 上一期
    private $app_id = null;
    private $time = null;
    private $time_1 = null;
    private $time_2 = null;
    private $time_3 = null;
    private $title_status = 1; // 1 今日可瓜分金额， 2 明日可瓜分金额
    private $sign_status = 1; // 1 昨日打卡情况， 2 今日打卡情况
    const ACTIVITY_REAL_MONEY = 'morning_activity_real_money'; // 真实用户参与总余额
    const ACTIVITY_TOTAL_REAL_USER = 'morning_activity_real_user'; // 真实用户参与用户数量
    const ACTIVITY_TOTAL_BUM_USER = 'morning_activity_bum_user'; // 假用户最大值
    const ACTIVITY_TOTAL_SUCCESS_USER = 'morning_activity_success_user'; // 用户打卡成功人数
    const ACTIVITY_IP_CHECK = 'morning_activity_ip_';
    const ACTIVITY_EARLIER_KEY = 'morning_activity_earlier_key';

    public function __construct($app_id, $time)
    {
        $this->time = $time;
        $year = date('Y', $this->time);
        $month = date('m', $this->time);
        $day = date('d', $this->time);
        $this->app_id = $app_id;
        $this->time_1 = mktime(8, 0, 0, $month, $day, $year); // 等级1的分隔时间戳 打卡开始时间
        $this->time_2 = mktime(10, 0, 0, $month, $day, $year); // 等级2的分隔时间戳 打卡结束时间， 报名开始时间
        $this->time_3 = mktime(0, 0, 0, $month, $day + 1, $year); //
//        $this->time = mktime(1,30,59, $month, $day, $year); // 测试专用
        #todo 设置时间等级，用于后续条件判断
        if ($this->time <= $this->time_1) {
            $this->time_status = 1;
            $this->activity_no = date('Ymd', $this->time - 86400); // 昨天期号
            $this->last_activity_no = date('Ymd', $this->time - 172800); // 昨天期号
            $this->title_status = 1;
            $this->sign_status = 1;
        } else if ($this->time <= $this->time_2) {
            $this->time_status = 2;
            $this->activity_no = date('Ymd', $this->time - 86400); // 昨天期号
            $this->last_activity_no = date('Ymd', $this->time - 172800); // 昨天期号
            $this->title_status = 1;
            $this->sign_status = 1;
        } else {
            $this->time_status = 3;
            $this->activity_no = date('Ymd', $this->time); // 今天期号最新一期
            $this->last_activity_no = date('Ymd', $this->time - 86400); // 昨天期号
            $this->title_status = 2;
            $this->sign_status = 2;
        }
    }

    /**
     * 获取可瓜分总金额 和 单日参与总人数，昨日参与总人数，总日瓜分总金额
     */
    public function getMoneyAndUserCount()
    {
        $redis = Redis::connection();
        $default = 0;
        $total_money = 0;
        $user_money_rate = 3;

        $obj_config = new JsonConfig();
        $config_data = $obj_config->getValue('morning_user_config');
        $max = empty($config_data[$this->activity_no][4]['day']) ? 0 : $config_data[$this->activity_no][4]['day'];
        $last_max = empty($config_data[$this->last_activity_no][4]['day']) ? 0 : $config_data[$this->last_activity_no][4]['day'];

//        if($redis->hexists(self::ACTIVITY_TOTAL_BUM_USER, $this->activity_no)){
//            $max = $redis->hget(self::ACTIVITY_TOTAL_BUM_USER, $this->activity_no); // 后台当日配置每日最大ai人数
//        } else {
//            $max = $default;
//        }
//        if($redis->hexists(self::ACTIVITY_TOTAL_BUM_USER, $this->last_activity_no)){
//            $last_max = $redis->hget(self::ACTIVITY_TOTAL_BUM_USER, $this->last_activity_no); // 后台昨日日配置每日最大ai人数
//        } else {
//            $last_max = 0;
//        }
        if ($redis->hexists(self::ACTIVITY_REAL_MONEY, $this->activity_no)) {
            $real_money = $redis->hget(self::ACTIVITY_REAL_MONEY, $this->activity_no); // 当日用户真实可瓜分金额
        } else {
            $real_money = $default;
        }
        if ($redis->hexists(self::ACTIVITY_TOTAL_SUCCESS_USER, $this->last_activity_no)) {
            $last_success_user = $redis->hget(self::ACTIVITY_TOTAL_SUCCESS_USER, $this->last_activity_no); // 昨日用户真实打卡人数
        } else {
            $last_success_user = 0;
        }
        if ($redis->hexists(self::ACTIVITY_REAL_MONEY, $this->last_activity_no)) {
            $last_real_money = $redis->hget(self::ACTIVITY_REAL_MONEY, $this->last_activity_no); // 昨日用户真实可瓜分金额
        } else {
            $last_real_money = 0;
        }
        if ($redis->hexists(self::ACTIVITY_TOTAL_REAL_USER, $this->activity_no)) {
            $real_user = $redis->hget(self::ACTIVITY_TOTAL_REAL_USER, $this->activity_no); // 当日用户真实参与人数
        } else {
            $real_user = $default;
        }
        if ($this->time_status == 3) {// 报名开始状态, 当前期号，根据时间戳差模拟加钱。
            $sub_time = $this->time - $this->time_1;
            if ($sub_time > $max) {
                $sub_time = $max;
            }
            $total_money = $sub_time * $user_money_rate + $real_money; // 当日可瓜分总金额
            $total_user = $sub_time + $real_user;  // 当日参与总人数
        } else {
            $total_money = $max * $user_money_rate + $real_money;
            $total_user = $max + $real_user;
        }
        $yesterday_money = $last_real_money + $last_max * $user_money_rate;
        $yesterday_user_count = $last_success_user + $last_max;
        return [
            'today_money' => $total_money,
            'today_user_count' => $total_user,
            'yesterday_money' => $yesterday_money,
            'yesterday_success_count' => $yesterday_user_count,
            'title_status' => $this->title_status,
            'sign_status' => $this->sign_status,
        ];


    }


    /**
     * 设置指定期号的缓存参与总人数（系统配置）
     */
    public function setTotalBumCount($no, $count)
    {
        $redis = Redis::connection();
        $redis->hset(self::ACTIVITY_TOTAL_BUM_USER, $no, $count);
    }

    /**
     * 获取中间展示按钮状态
     * status 1：我要挑战 2.立即打卡 3.报名已截止 4.已参与 5.已打卡
     * tip_status 0 无视 ，1 提示忘记打卡， 2 弹出瓜分金额
     */
    public function getMidBtnStatus()
    {
        $morningRecordModel = new MorningUserRecords();
        $status = 1; //
        $record = $morningRecordModel->where(['app_id' => $this->app_id, 'activity_no' => $this->activity_no])->first();
        $tip_status = 0;
        $money = 0;
        switch ($this->time_status) {
            case 1 :
                if (empty($record)) {
                    $status = 3;
                } else {
                    $status = 4;
                }
                break;
            case 2 :
                if (empty($record)) { // 用户未报名
                    $status = 3;
                } else {
                    if (empty($record['sign_time'])) {
                        $status = 2;
                    } else {
                        $status = 5;
                    }
                };
                break;
            case 3 :
                if (empty($record)) {
                    $status = 1;
                } else {
                    $status = 4;
                }
                $record_before = $morningRecordModel->where(['app_id' => $this->app_id, 'activity_no' => $this->last_activity_no, 'show_flag' => 0])->first(); // 查找上一期记录
                if (!empty($record_before)) {
                    $money = $record_before['success_money'];
                    if (empty($record_before['sign_time'])) {
                        $tip_status = 1;
                        $morningRecordModel->where(['app_id' => $this->app_id, 'activity_no' => $this->last_activity_no])->update(['show_flag' => 1]);
                    } else if ($money > 0) {
                        $tip_status = 2;
                        $morningRecordModel->where(['app_id' => $this->app_id, 'activity_no' => $this->last_activity_no])->update(['show_flag' => 1]);
                    }
                }
        }

        return [
            'tip_flag' => $tip_status,
            'mid_btn_status' => $status,
            'get_money' => $money,
        ];
    }


    /**
     * 获取底部排行榜
     * @return array
     */
    public function getRankList()
    {
        //判断小程序是否展示多条
        $obj_config = new JsonConfig();
        $config_data = $obj_config->getValue('morning_user_config');
        $ranking_name_1 = empty($config_data[$this->last_activity_no][1]['name']) ? '' : $config_data[$this->last_activity_no][1]['name']; // 早起之星
        $ranking_value_1 = empty($config_data[$this->last_activity_no][1]['value']) ? '' : $config_data[$this->last_activity_no][1]['value'];
        $image_1 = empty($config_data[$this->last_activity_no][1]['img']) ? '' : $config_data[$this->last_activity_no][1]['img'];
        $ranking_name_2 = empty($config_data[$this->last_activity_no][2]['name']) ? '' : $config_data[$this->last_activity_no][2]['name']; // 幸运之星
        $ranking_value_2 = empty($config_data[$this->last_activity_no][2]['value']) ? '' : $config_data[$this->last_activity_no][2]['value'];
        $image_2 = empty($config_data[$this->last_activity_no][2]['img']) ? '' : $config_data[$this->last_activity_no][2]['img'];
        $ranking_name_3 = empty($config_data[$this->last_activity_no][3]['name']) ? '' : $config_data[$this->last_activity_no][3]['name']; // 毅力之星
        $ranking_value_3 = empty($config_data[$this->last_activity_no][3]['value']) ? '' : $config_data[$this->last_activity_no][3]['value'];
        $image_3 = empty($config_data[$this->last_activity_no][3]['img']) ? '' : $config_data[$this->last_activity_no][3]['img'];


        return [ // 底部排行榜
            $this->getListItem('早起之星', $ranking_name_1, $ranking_value_1, $image_1, 1),
            $this->getListItem('幸运之星', $ranking_name_2, $ranking_value_2, $image_2, 2),
            $this->getListItem('毅力之星', $ranking_name_3, $ranking_value_3, $image_3, 3),
        ];
    }

    /**
     * 用户报名接口
     * @param $schemes_id
     * @param $money
     * @throws ApiException
     */
    public function userApply($schemes_id, $money, $ip, $device = '')
    {
        $cache_key = self::ACTIVITY_IP_CHECK . ip2long($ip);
        $exit = Cache::has($cache_key);
        if ($exit) {
            $count = Cache::get($cache_key);
            if ($count >= 20) {
                throw new ApiException('无法报名！', '1012');
            }
        }
        if ($this->time_status == 3) {
            $morningRecordModel = new MorningUserRecords();
            $record_info = $morningRecordModel->where(['app_id' => $this->app_id, 'activity_no' => $this->activity_no])->first();
            if (!empty($record_info)) {
                throw new ApiException('您已经报名过了！', '1010');
            }
            $morningRecordModel->create([
                'app_id' => $this->app_id,
                'scheme_id' => $schemes_id,
                'activity_no' => $this->activity_no,
                'apply_time' => $this->time, // 报名时间
                'apply_money' => $money, // 报名金额
                'ip' => ip2long($ip),
                'device' => $device
            ]);
            $morningUserModel = new MorningUser();
            $morning_user = $morningUserModel->where(['app_id' => $this->app_id])->first();
            if (empty($morning_user)) {
                $res = [
                    'app_id' => $this->app_id,
                    'apply_days' => 1,
                    'apply_total_money' => $money,
                    'success_days' => 0,
                    'success_total_money' => 0,
                    'continuous_days' => 1
                ];
                $morningUserModel->create($res);
            } else {
                $continues = 0;
                // 判断上期是否报名
                if ($morningRecordModel->where(['app_id' => $this->app_id, 'activity_no' => $this->last_activity_no])->exists()) {
                    $continues = DB::raw("continuous_days + " . 1);
                }
                $morning_user->where(['app_id' => $this->app_id])->update([
                    'apply_days' => DB::raw("apply_days + " . 1),
                    'apply_total_money' => DB::raw("apply_total_money + " . $money),
                    'continuous_days' => $continues
                ]);
            }
            $redis = Redis::connection();
            $redis->hincrby(self::ACTIVITY_TOTAL_REAL_USER, $this->activity_no, 1);
            $redis->hincrby(self::ACTIVITY_REAL_MONEY, $this->activity_no, $money);
            if ($exit) {
                Cache::increment($cache_key);
            } else {
                Cache::put($cache_key, 0, 60 * 12);
            }
        } else {
            throw new ApiException('当前不是报名时间！', '1011');
        }

    }

    /**
     * 用户打卡
     * @param $schemes_id
     * @param $money
     * @throws ApiException
     */
    public function userSign()
    {
        if ($this->time_status == 2) { // 判断是否是打卡时间
            $morningRecordModel = new MorningUserRecords();
            $morningUserModel = new MorningUser();
            $record_info = $morningRecordModel->where(['app_id' => $this->app_id,
                'activity_no' => $this->activity_no])->first();
            if (empty($record_info)) {
                throw new ApiException('您未报名上期早安打卡！无法打卡。', '1010');
            }
            if (!empty($record_info['sign_time'])) {
                throw new ApiException('您已经打卡过了，请等待系统瓜分金额！', '1011');
            }
            $morningRecordModel->where(['app_id' => $this->app_id, 'activity_no' => $this->activity_no])->update([
                'sign_time' => $this->time
            ]);
            $morningUserModel->where(['app_id' => $this->app_id])->update([
                'success_days' => DB::raw("success_days + " . 1),
            ]);
            $redis = Redis::connection();
            $redis->hincrby(self::ACTIVITY_TOTAL_SUCCESS_USER, $this->activity_no, 1);
        } else {
            throw new ApiException('当前不是打卡时间！', '1012');
        }
    }

    /**
     * 用户历史报名记录
     * status 0 待打卡， 1 打卡成功， 2打卡失败 , 3 待瓜分
     */
    public function userRecords()
    {
        $morningRecordModel = new MorningUserRecords();
        $record_info = $morningRecordModel->where(['app_id' => $this->app_id])->orderBy('activity_no', 'desc')->paginate(null, ['apply_time', 'sign_time', 'apply_money', 'success_money', 'activity_no']);
        $records = $record_info->toArray();
        foreach ($records['data'] as $key => $item) {
            if (!empty($item['sign_time'])) {
                $records['data'][$key]['sign_time'] = date('Y-m-d H:i:s', $item['sign_time']);
            }
            $records['data'][$key]['apply_time'] = date('Y-m-d H:i:s', $item['apply_time']);
            $status = 0;

            if ($item['activity_no'] == $this->activity_no) { // 当前期号
                if (empty($item['sign_time'])) {
                    $status = 0;
                } else {
                    if ($item['success_money'] == 0) {
                        $status = 3;
                    } else {
                        $status = 1;
                    }
                }
            } else if ($item['activity_no'] == $this->last_activity_no) { // 上一期号
                if (empty($item['sign_time'])) {
                    $status = 2;
                } else {
                    if ($item['success_money'] == 0) {
                        $status = 3;
                    } else {
                        $status = 1;
                    }
                }
            } else {// 历史期号
                if (empty($item['sign_time'])) {
                    $status = 2;
                } else {
                    $status = 1;
                }
            }
            switch ($status) {
                case 0 :
                    $records['data'][$key]['status_text'] = '待打卡';
                    $records['data'][$key]['status_content'] = '等待打卡中';
                    break; //  0 待打卡，
                case 1 :
                    $records['data'][$key]['status_text'] = '打卡成功';
                    $records['data'][$key]['status_content'] = '瓜分奖金' . $item['success_money'] . '元';
                    break; // 1 打卡成功，
                case 2 :
                    $records['data'][$key]['status_text'] = '打卡失败';
                    $records['data'][$key]['status_content'] = '未及时打卡';
                    break; // 2打卡失败 ,
                case 3 :
                    $records['data'][$key]['status_text'] = '待瓜分';
                    $records['data'][$key]['status_content'] = '等待瓜分奖金池';
                    break; //3 待瓜分
            }
            $records['data'][$key]['status'] = $status;
        }
        return $records;

    }

    /**
     * 检查单个ip是否重复报名多次
     * @param $ip
     */
    private function checkIpLimit($ip)
    {
        $exit = Cache::has(self::ACTIVITY_IP_CHECK . ip2long($ip));
        if ($exit) {
            $count = Cache::get(self::ACTIVITY_IP_CHECK);
            if ($count >= 20) {
                return true;
            }
        }
        return false;
    }

    private function getListItem($title, $name, $value, $img, $flag)
    {
        return [
            'title' => $title,
            'name' => $name,
            'value' => $value,
            'img' => $img,
            'flag' => $flag,
        ];
    }
}