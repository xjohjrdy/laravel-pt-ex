<?php
namespace App\Services\PutaoRealActive;

use App\Entitys\App\ActiveRealLog;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\ActiveRealCount;
use App\Services\Common\CommonFunction;
use App\Entitys\App\ActiveRealResult;
use App\Entitys\App\ActiveRealSign;
use App\Entitys\App\ActiveRealResultMonth;
use App\Entitys\App\ActiveRealResultMonthUser;

/**
 * 我的实时活跃值接口服务
 * @author sam
 */
class PutaoRealActive
{
    const EVENT_SIGN     = 1;
    const EVENT_VIP      = 2;
    const EVENT_VOIP     = 3;
    const EVENT_CASHBACK = 4;
    const EVENT_FANS     = 5;
    const EVENT_SHOP     = 6;
    const EVENT_CIRCLE   = 7;
    const EVENT_ADD      = 8;
    
    const REDIS_REAL_QUEUE_LOG = 'active_real_log';
    const REDIS_REAL_QUEUE_COUNT = 'active_real_count';
    const REDIS_REAL_QUEUE_RESULT = 'active_real_result';
    
    const TABLE_PARTITION_MINDATE = '2019-07-01';
    
    public function __construct()
    {
        
    }
    
    /**
     * 活跃值事件监听接口
     * @param int $user 用户ID
     * @param int $event_type 事件类型
     * @param float $event_val 事件值
     * @param float $event_env 事件参数
     * @param float $extra_param 额外参数
     * @param int|string $timeline 时间戳    
     * @param int $parent_id 用户父ID
     * @return void
     */
    public static function eventListen( $user_id, $event_type, $event_val = 1, $event_param = 0, $extra_param = 0, $timeline = null, $parent_id = null, $eventime = null )
    {
        try {
            $dayline = date('Y-m-d');
            $created_at = date('Y-m-d H:i:s');
            
            if ( !empty($timeline) ) {
                if ( is_int($timeline) ) {
                    $dayline = date('Y-m-d', $timeline);
                    $created_at = date('Y-m-d H:i:s', $timeline);
                } else {
                    $dayline = date('Y-m-d',strtotime($timeline));
                    $created_at = $timeline;
                }
            }
            if ( !empty($eventime) ) {
                if ( is_int($eventime) ) {
                    $created_at = date('Y-m-d H:i:s', $eventime);
                } else {
                    $created_at = $eventime;
                }
            }
            if ( strtotime($dayline) < strtotime(self::TABLE_PARTITION_MINDATE) ) {
                return;
            }
            if ( $parent_id === null ) {
                $user = AppUserInfo::find( $user_id );
                if ( empty( $user ) ) {
                    throw new \Exception('no user '.$user_id);
                }
                if ( $user->status == 2 ) {
                    throw new \Exception('fail user '.$user_id);
                }
                $parent_id = $user->getAttribute('parent_id');
            }
            
            $parent_id = $parent_id > 0 ? $parent_id : 0;
            $arr = [$dayline,$event_type,$user_id,$parent_id,$event_val,$event_param,$extra_param,$created_at];
            $str = serialize($arr);
            switch ( $event_type ) {
                case self::EVENT_SIGN:break;
                case self::EVENT_VIP:break;
                case self::EVENT_VOIP:break;
                case self::EVENT_CASHBACK:break;
                case self::EVENT_FANS:break;
                case self::EVENT_SHOP:break;
                case self::EVENT_CIRCLE:break;
                case self::EVENT_ADD:break;
                default:return;
            }
            if ( self::is_debug_data($event_type) ) {
                Storage::disk('local')->append("active/eventListen-{$dayline}.{$event_type}.data", "[{$created_at}]".$str);
            }
            
            Redis::rpush(self::REDIS_REAL_QUEUE_LOG, $str);
            Redis::rpush(self::REDIS_REAL_QUEUE_COUNT, $str);
            Redis::rpush(self::REDIS_REAL_QUEUE_RESULT, $str);
        } catch (\Exception $e) {
            try {
                if ( self::is_debug() ) {
                    $date = date('Y-m-d');
                    $time = date('Y-m-d H:i:s');
                    Storage::disk('local')->append("active/eventListenExceptions.{$date}.log", "[{$time}]".$e->getMessage());
                }
            } catch (\Exception $ee) {
            }
        }
    }
    
    /**
     * 日志处理
     * @param int $len 处理队列长度
     * @param ActiveRealLog $activeRealLog
     * @return void
     */
    public static function realLog( $len = 5000, ActiveRealLog $activeRealLog = null )
    {
        try {
            $activeRealLog = $activeRealLog ? $activeRealLog : new ActiveRealLog();
            $real_log_table = $activeRealLog->getTable();
            
            $str = $sql = '';
            $que_data = $sql_values = $org_data = $arr = [];
            $que_data = self::getOrSyncQueue(self::REDIS_REAL_QUEUE_LOG, $len );
            
            foreach ( $que_data as $str ) {
                if ( empty($str) ) {
                    break;
                }
                
                $arr = unserialize($str);
                $sql_values[] = "('{$arr[0]}',{$arr[1]},{$arr[2]},{$arr[3]},{$arr[4]},{$arr[5]},{$arr[6]},'{$arr[7]}')";
                $org_data[$arr[0]][$arr[1]][] = $str;
            }
            if ( !empty( $org_data ) ) {
               foreach ($org_data as $key => $value) {
                   foreach ( $value as $type => $dayline ) {
                       Storage::disk('local')->append("active/realLog.{$key}.{$type}.data", join("\r\n",$dayline));
                   }
               }
            }
            if ( !empty($sql_values) ) {                
                $sql = "INSERT IGNORE INTO {$real_log_table} (`dayline`,`event_type`,`user_id`,`parent_id`,`event_val`,`event_param`,`extra_param`,`created_at`) VALUES ".join(',', $sql_values).';';
                
                DB::connection("app38")->getPDO()->exec($sql);
            }
            
            unset($que_data,$org_data,$sql_values,$sql,$str,$arr);
        } catch (\Exception $e) {
            $date = date('Y-m-d');
            Storage::disk('local')->append("active/realLogExceptions.{$date}.log", $e->getMessage());
        }
    }
    
    /**
     * 计算处理
     * @param string $len
     * @param ActiveRealCount $activeRealCount
     * 
     * @return void
     */
    public static function realCount( $len = 5000, ActiveRealCount $activeRealCount = null )
    {
        try {
            $activeRealCount = $activeRealCount ? $activeRealCount : new ActiveRealCount();
            $real_count_table = $activeRealCount->getTable();
            $que_data = $sql_values = [];
            $que_data = self::getOrSyncQueue(self::REDIS_REAL_QUEUE_COUNT, $len );
            
            foreach( $que_data as $str ) {
                if ( empty($str) ) {
                    break;
                }
            
                $arr = unserialize($str);
                self::dataDriveCount($arr, $sql_values);
            }
            
            if ( !empty($sql_values) ) {
                $sql = "INSERT IGNORE INTO {$real_count_table} (`dayline`,`user_id`,`type`,`param`,`param1`,`param2`,`log_id`,`created_at`) VALUES ".join(',', $sql_values).';';
                DB::connection("app38")->getPDO()->exec($sql);
            }
            
            unset($que_data,$sql_values,$sql,$str,$arr);
        } catch (\Exception $e) {
            $date = date('Y-m-d');
            Storage::disk('local')->append("active/realCountExceptions-{$date}.log", $e->getMessage());
        }
    }
    
    /**
     * 统计处理（实时计算，性能差）
     * @param number $len
     * @param ActiveRealResult $activeRealResult
     * @return number
     */
    public static function realResult( $len = 5000, ActiveRealResult $activeRealResult, ActiveRealResultMonth $activeRealResultMonth )
    {
        try {
            $real_result_table = $activeRealResult->getTable();
            $real_result_month_table = $activeRealResultMonth->getTable();
            $str = $sqls = '';
            $que_data = $sql_values = [];
            $sql_values['count'] = [];
            $sql_values['sum'] = [];
            $que_data = self::getOrSyncQueue(self::REDIS_REAL_QUEUE_RESULT, $len );
            
            foreach ( $que_data as $str ) {
                if ( empty($str) ) {
                    break;
                }
                
                $arr = unserialize($str);
                self::dataDriveResult( $arr, $sql_values );
            }
            
            $onesql = '';
            if ( !empty( $sql_values['count']) ) {
                $tmp_values = [];
                foreach ( $sql_values['count'] as $value ) {
                    $tmp_values[] = $value[0];
                }
                $fields_values = join(',', $tmp_values);
                unset($tmp_values);
                $onesql .= "INSERT INTO {$real_result_table} (`dayline`,`user_id`,`type`,`param`,`monthline`) VALUES {$fields_values} ON DUPLICATE KEY UPDATE param=param+1;";
                $onesql .= "INSERT INTO {$real_result_month_table} (`dayline`,`user_id`,`type`,`param`,`monthline`) VALUES {$fields_values} ON DUPLICATE KEY UPDATE param=param+1;";
            }
            if ( !empty( $sql_values['sum'] ) ) {
                foreach ( $sql_values['sum'] as $value ) {
                    $sqls[] = "INSERT INTO {$real_result_table} (`dayline`,`user_id`,`type`,`param`,`param1`,`monthline`) VALUES {$value[0]} ON DUPLICATE KEY UPDATE param=param+1,param1=param1+{$value[1]};";
                    $sqls[] = "INSERT INTO {$real_result_month_table} (`dayline`,`user_id`,`type`,`param`,`param1`,`monthline`) VALUES {$value[0]} ON DUPLICATE KEY UPDATE param=param+1,param1=param1+{$value[1]};";
                }
                $onesql .= join('',$sqls);
            }
            
            if ( !empty($onesql) ) {
                DB::connection("app38")->getPDO()->exec($onesql);
            }
            
            unset($que_data, $sqls,$onesql,$str,$arr);
        } catch (\Exception $e) {
            $date = date('Y-m-d');
            Storage::disk('local')->append("active/realResultExceptions-{$date}.log", $e->getMessage());
        }
    }
    
    /**
     * 获取缓存队列长度
     * @return int
     */
    public static function getQueueLength( $queue_key )
    {
        return Redis::llen($queue_key);
    }
    
    /**
     * 重构某年某月某日
     * @param string $day
     * @param ActiveRealCount $activeRealCount
     * @param ActiveRealLog $activeRealLog
     * @return void
     */
    private static function reCountDay( $speed = 10000, $day = '0000-00-00', ActiveRealCount $activeRealCount = null, ActiveRealLog $activeRealLog = null )
    {   
        $activeRealCount = $activeRealCount ? $activeRealCount : new ActiveRealCount();
        $activeRealLog = $activeRealLog ? $activeRealLog : new ActiveRealLog();
        $real_count_table = $activeRealCount->getTable();
        $real_log_table = $activeRealLog->getTable();
        $partition = 'p'.date('Ymd',strtotime($day));
        $trancate_sql = "ALTER TABLE {$real_count_table} TRUNCATE PARTITION {$partition};";
        
        DB::connection("app38")->statement($trancate_sql);
        $chunk = $speed;
        DB::connection("app38")->table($real_log_table)->where('dayline',$day)->orderBy('dayline')->chunk($chunk, function($real_logs) use ($real_count_table) {
            $sql_values = [];
            foreach ($real_logs as $real_log) {
                $event_data = [];
                $event_data[0] = $real_log->dayline;
                $event_data[1] = $real_log->event_type;
                $event_data[2] = $real_log->user_id;
                $event_data[3] = $real_log->parent_id;
                $event_data[4] = $real_log->event_val;
                $event_data[5] = $real_log->event_param;
                $event_data[6] = $real_log->extra_param;
                $event_data[7] = $real_log->created_at;
                $event_data[8] = $real_log->id;
                
                self::dataDriveCount($event_data, $sql_values);
            }
            if ( !empty($sql_values) ) {
                $sql = "INSERT IGNORE INTO {$real_count_table} (`dayline`,`user_id`,`type`,`param`,`param1`,`param2`,`log_id`) VALUES ".join(',', $sql_values).';';
                DB::connection("app38")->getPDO()->exec($sql);
            }
        });
    }
    
    /**
     * 重构某年某月
     * @param string $year
     * @return void
     */
    private static function reCountMonth( $month = '0000-00', $speed = 10000, ActiveRealCount $activeRealCount = null, ActiveRealLog $activeRealLog = null  )
    {   
        $activeRealCount = $activeRealCount ? $activeRealCount : new ActiveRealCount();
        $activeRealLog = $activeRealLog ? $activeRealLog : new ActiveRealLog();
        $days = date('t',strtotime($month));
        for ( $i = 1; $i <= $days; $i++ ) {
            $day = str_pad($i, 2, "0", STR_PAD_LEFT);
            self::reCountDay( $month.'-'.$day, $speed, $activeRealCount, $activeRealLog );
        }
    }
    
    /**
     * 按日统计活跃值（性能高）
     * @param string $day
     * @param ActiveRealResult $activeRealResult
     * @return void
     */
    public static function reResultDay( $day = '0000-00-00', ActiveRealResult $activeRealResult, ActiveRealResultMonth $activeRealResultMonth )
    {
        $onesql = '';
        
        $real_result_table = $activeRealResult->getTable();
        $real_result_month_table = $activeRealResultMonth->getTable();
        
        $sqls = [];
        for ( $i=1;$i<=8;$i++ ) {
            switch ( $i ) {
                case self::EVENT_CASHBACK:case self::EVENT_SHOP:case self::EVENT_ADD://计量
                    $sqls[] = "INSERT INTO {$real_result_table} (dayline, user_id, type, param, param1, monthline) SELECT dayline,user_id,type,count(param), sum(param1),date_format(dayline,'%Y%m') FROM `lc_active_real_count` WHERE dayline = '{$day}' AND type = {$i} GROUP BY dayline,user_id,type;";
                    break;
                default://计数
                    $sqls[] = "INSERT INTO {$real_result_table} (dayline, user_id, type, param, monthline) SELECT dayline,user_id,type,count(param),date_format(dayline,'%Y%m') FROM `lc_active_real_count` WHERE dayline = '{$day}' AND type = {$i} GROUP BY dayline,user_id,type;";
                    break;
            }
        }
        
        for ( $i=1;$i<=8;$i++ ) {
            switch ( $i ) {
                case self::EVENT_CASHBACK:case self::EVENT_SHOP:case self::EVENT_ADD://计量
                    $sqls[] = "INSERT INTO {$real_result_month_table} (dayline, user_id, type, param, param1, monthline) SELECT dayline,user_id,type,count(param), sum(param1),date_format(dayline,'%Y%m') FROM `lc_active_real_count` WHERE dayline = '{$day}' AND type = {$i} GROUP BY dayline,user_id,type;";
                    break;
                default://计数
                    $sqls[] = "INSERT INTO {$real_result_month_table} (dayline, user_id, type, param, monthline) SELECT dayline,user_id,type,count(param),date_format(dayline,'%Y%m') FROM `lc_active_real_count` WHERE dayline = '{$day}' AND type = {$i} GROUP BY dayline,user_id,type;";
                    break;
            }
        }
        
        return join("\r\n",$sqls)."\r\n";
    }
    
    /**
     * 按日清空全部数据
     * @param string $day 2019-06-08
     * @param ActiveRealLog $activeRealLog
     * @param ActiveRealCount $activeRealCount
     * @param ActiveRealResult $activeRealResult
     */
    public static function truncate( $day = '0000-00-00', ActiveRealLog $activeRealLog, ActiveRealCount $activeRealCount, ActiveRealResult $activeRealResult, ActiveRealResultMonth $activeRealResultMonth )
    {
        $real_log_table = $activeRealLog->getTable();
        $real_count_table = $activeRealCount->getTable();
        $real_result_table = $activeRealResult->getTable();
        $real_result_month_table = $activeRealResultMonth->getTable();
        $partition = 'p'.date('Ymd',strtotime($day));
        
        $trancate_sql = [];
        $trancate_sql[] = "DELETE FROM {$real_result_month_table} WHERE dayline = '{$day}';";
        $trancate_sql[] = "ALTER TABLE {$real_result_table} TRUNCATE PARTITION {$partition};";
        $trancate_sql[] = "ALTER TABLE {$real_count_table} TRUNCATE PARTITION {$partition};";
        $trancate_sql[] = "ALTER TABLE {$real_log_table} TRUNCATE PARTITION {$partition};";
        
        return join("\r\n",$trancate_sql)."\r\n";   
    }
    
    /**
     * 活跃值数据重构
     * @param string $day
     * @param ActiveRealLog $activeRealLog
     * @param ActiveRealCount $activeRealCount
     * @param ActiveRealResult $activeRealResult
     * @param ActiveRealSign $activeRealSign
     */
    public static function rebuildAll( $day = '0000-00-00', ActiveRealLog $activeRealLog, ActiveRealCount $activeRealCount, ActiveRealSign $activeRealSign )
    {
        $start_time = $day . ' 00:00:00';
        $end_time = $day . ' 23:59:59';
        $start_timestamp = strtotime($start_time);
        $end_timestamp = strtotime($end_time);
        
        $real_log_table = $activeRealLog->getTable();
        $real_count_table = $activeRealCount->getTable();
        $real_sign_table = $activeRealSign->getTable();

        $init = [];
        $init[] = "INSERT IGNORE INTO {$real_log_table} (dayline,event_type,user_id,parent_id,event_val,created_at) SELECT dayline,1,user_id,parent_id,1,created_at from {$real_sign_table} where dayline = '{$day}';";
        $init[] = "INSERT IGNORE INTO app38.{$real_log_table} (dayline,event_type,user_id,parent_id,event_val,created_at) select FROM_UNIXTIME(pao.submitdate,'%Y-%m-%d') as dayline,2,lu.id as user_id,lu.parent_id,1,FROM_UNIXTIME(pao.submitdate,'%Y-%m-%d %H:%i:%s') as created_at from a1191125678.pre_aljbgp_order pao inner join a1191125678.pre_common_member pcm on pao.uid = pcm.uid INNER JOIN app38.lc_user lu on pcm.pt_id = lu.id where pao.submitdate >= {$start_timestamp} and pao.submitdate <= {$end_timestamp} AND pao.groupid = 23 and pao.status=2;";
        $init[] = "INSERT IGNORE INTO app38.{$real_log_table} (dayline,event_type,user_id,parent_id,event_val,created_at) SELECT date_format(pvmo.created_at,'%Y-%m-%d'),3,pvmo.app_id,lu.parent_id,pvmo.id,pvmo.created_at FROM a1191125678.pre_voip_money_order pvmo INNER JOIN app38.lc_user lu on pvmo.app_id = lu.id WHERE pvmo.status=1 and pvmo.created_at BETWEEN '{$start_time}' and '{$end_time}';";
        $init[] = "INSERT IGNORE INTO {$real_log_table} (dayline,event_type,user_id,parent_id,event_val,event_param,created_at) SELECT  FROM_UNIXTIME(luon.create_time,'%Y-%m-%d'),4,luon.user_id,lu.parent_id,luon.id,luon.cashback_amount,FROM_UNIXTIME(luon.create_time,'%Y-%m-%d %H:%i:%s') FROM lc_user_order_new luon inner join lc_user lu on luon.user_id = lu.id where luon.`status` in (3,4,9) and luon.create_time >= {$start_timestamp} and luon.create_time <= {$end_timestamp};";
        $init[] = "INSERT IGNORE INTO {$real_log_table} (dayline,event_type,user_id,parent_id,event_val,created_at) select FROM_UNIXTIME(create_time,'%Y-%m-%d'),5,parent_id,0,id,FROM_UNIXTIME(create_time,'%Y-%m-%d %H:%i:%s') from lc_user where create_time >= {$start_timestamp} and create_time <= {$end_timestamp} and parent_id > 0 and `status`=1;";
        $init[] = "INSERT IGNORE INTO {$real_log_table} (dayline,event_type,user_id,parent_id,event_val,event_param,created_at) select date_format(lso.created_at,'%Y-%m-%d'),6,lso.app_id,lu.parent_id,lso.id,lso.price,lso.created_at from lc_shop_orders lso INNER JOIN lc_user lu on lso.app_id = lu.id where created_at BETWEEN '{$start_time}' and '{$end_time}' and lso.price !=800 and lso.status=3;";
        $init[] = "INSERT IGNORE INTO {$real_log_table} (dayline,event_type,user_id,parent_id,event_val,created_at) select date_format(lcrao.created_at,'%Y-%m-%d'),7,lcrao.app_id,lu.parent_id,lcrao.id,lcrao.created_at from lc_circle_ring_add_order lcrao inner join lc_user lu on lcrao.app_id = lu.id where lcrao.status=1 and lcrao.created_at BETWEEN '{$start_time}' and '{$end_time}' and lcrao.money = 600;";
        $init[] = "INSERT IGNORE INTO {$real_log_table} (dayline,event_type,user_id,parent_id,event_val,event_param,created_at) select date_format(created_at,'%Y-%m-%d'),8,app_id,0,id,active,created_at from lc_superaddition_active where created_at BETWEEN '{$start_time}' and '{$end_time}';";
        for ( $i = 1;$i <=8; $i++ ) {
            switch ($i) {
                case self::EVENT_CASHBACK:case self::EVENT_SHOP:case self::EVENT_ADD://计量
                    $init[] = "INSERT IGNORE INTO {$real_count_table} (dayline,user_id,type,param,param1,log_id,created_at) select dayline,user_id,event_type,event_val,event_param,id,created_at from {$real_log_table} where dayline = '{$day}' and event_type = {$i};";
                    $init[] = "INSERT IGNORE INTO {$real_count_table} (dayline,user_id,type,param,param1,log_id,created_at) select dayline,parent_id,event_type,event_val,event_param,id,created_at from {$real_log_table} where dayline = '{$day}' and event_type = {$i} and parent_id > 0;";
                    break;
                case self::EVENT_FANS://不需要父类
                    $init[] = "INSERT IGNORE INTO {$real_count_table} (dayline,user_id,type,param,log_id,created_at) select dayline,user_id,event_type,event_val,id,created_at from {$real_log_table} where dayline = '{$day}' and event_type = {$i};";
                    break;
                default://计数
                    $init[] = "INSERT IGNORE INTO {$real_count_table} (dayline,user_id,type,param,log_id,created_at) select dayline,user_id,event_type,event_val,id,created_at from {$real_log_table} where dayline = '{$day}' and event_type = {$i};";
                    $init[] = "INSERT IGNORE INTO {$real_count_table} (dayline,user_id,type,param,log_id,created_at) select dayline,parent_id,event_type,event_val,id,created_at from {$real_log_table} where dayline = '{$day}' and event_type = {$i} and parent_id > 0;";
                    break;
                    
           }
        }
        
        return join("\r\n", $init)."\r\n";
    }
    
    /**
     * 数据驱动分析计算
     * @param array $event_data
     * @param array &$values
     * @return void
     */
    private static function dataDriveCount( $event_data, & $values = [] )
    {
        $dayline     = $event_data[0];
        $type        = $event_data[1];
        $user_id     = $event_data[2];        
        $parent_id   = $event_data[3];
        $event_val   = $event_data[4];
        $event_param = $event_data[5];
        $extra_param = $event_data[6];
        $created_at  = $event_data[7];
        $log_id      = isset($event_data[8]) ? $event_data[8] : 0;
        switch ( $type ) {
            case PutaoRealActive::EVENT_SIGN://1.签到用户和上级计算参量不同
                
                $values[] = "('{$dayline}',{$user_id},{$type},{$event_val},{$event_param},0,{$log_id},'{$created_at}')";
                if ( $user_id != $parent_id && $parent_id > 0 ) {
                    $values[] = "('{$dayline}',{$parent_id},{$type},{$user_id},{$extra_param},0,{$log_id},'{$created_at}')";
                }
                break;
            case PutaoRealActive::EVENT_FANS://5.只要计算自己的就好了
                $values[] = "('{$dayline}',{$user_id},{$type},{$event_val},0,0,{$log_id},'{$created_at}')";
                break;
            default://大部分计算参量相同
                $values[] = "('{$dayline}',{$user_id},{$type},{$event_val},{$event_param},0,{$log_id},'{$created_at}')";
                if ( $user_id != $parent_id && $parent_id > 0 ) {
                    $values[] = "('{$dayline}',{$parent_id},{$type},{$event_val},{$event_param},0,{$log_id},'{$created_at}')";
                }
                break;
        }
    }
    
    /**
     * 数据驱动聚合计算
     * @param array $event_data
     * @param array &$values
     * @param string $real_result_table 聚合表名
     * @return void
     */
    private static function dataDriveResult( $event_data, & $values = [] )
    {
        $dayline     = $event_data[0];
        $type        = $event_data[1];
        $user_id     = $event_data[2];
        $parent_id   = $event_data[3];
        $event_param = $event_data[5];
        $monthline   = (int)date('Ym',strtotime($dayline));
        
        switch ( $type ) {
            case self::EVENT_FANS://计数
                $values['count'][] = ["('{$dayline}',{$user_id},{$type},1,{$monthline})",1];
                break;
            case self::EVENT_CASHBACK:case self::EVENT_SHOP:case self::EVENT_ADD://计量
                $values['sum'][] = ["('{$dayline}',{$user_id},{$type},1,{$event_param},{$monthline})",$event_param];
                if ( $user_id != $parent_id && $parent_id > 0 ) {
                    $values['sum'][] = ["('{$dayline}',{$parent_id},{$type},1,{$event_param},{$monthline})",$event_param];
                }
                break;
            default://计数
                $values['count'][] = ["('{$dayline}',{$user_id},{$type},1,{$monthline})",1];
                if ( $user_id != $parent_id && $parent_id > 0 ) {
                    $values['count'][] = ["('{$dayline}',{$parent_id},{$type},1,{$monthline})",1];
                }
                break;
        }
    }
    
    /**
     * 实时查询用户活跃值（按自然月）
     * @param int $user_id 用户ID
     * @param string $month 月格式日期 1999-01
     */
    public static function getUserActivePointByMonth( $user_id, $month )
    {
        bcscale(2);
        
        $user_actives = [];
        $cache_key = CommonFunction::getUserMonthActiveCacheKey( $user_id, $month );
        $user_actives = Cache::store('redis')->get($cache_key);
        if ( !empty($user_actives) ) {
            return $user_actives;
        }
        $activeRealResultMonthUser = new ActiveRealResultMonthUser();
        $ret = $activeRealResultMonthUser->where('monthline',$month)->where('user_id',$user_id)->get();
        
        $res = [];
        $value = $active_value = 0;
        foreach ( $ret as $obj ) {
            $type = $obj->type;
            if ( $obj->type == self::EVENT_CASHBACK || $obj->type == self::EVENT_SHOP || $obj->type == self::EVENT_ADD ) {
                $value = $obj->param1;
            } else {
                $value = $obj->param;
            }
            $res[$type] = !isset($res[$type]) ? $value : bcadd( $res[$type], $value );
        }
        for ( $i = 1; $i <= 8; $i++ ) {
            $result = 0;
            if ( !isset( $res[ $i ] ) ) {
                $user_actives[$i] = 0;
                continue;
            }
            
            $active_value = $res[ $i ];
            switch ( $i ) {
                case self::EVENT_SIGN://1.封顶20
                    $result = min( bcmul($active_value,0.02), 20 );
                    break;
                case self::EVENT_VIP://2.封顶30
                    $result = min( bcmul($active_value,2), 30 );
                    break;
                case self::EVENT_VOIP://3.封顶20
                    $result = min( bcmul($active_value,1), 20 );
                    break;
                case self::EVENT_CASHBACK://4.封顶20
                    $result = min( bcmul($active_value,0.1), 20 );
                    break;
                case self::EVENT_FANS://5.封顶20
                    $result = min( bcmul($active_value,1), 20 );
                    break;
                case self::EVENT_SHOP://6.封顶10
                    $result = min( bcmul($active_value,0.05), 10 );
                    break;
                case self::EVENT_CIRCLE://7.封顶30
                    $result = min( bcmul($active_value,2), 30 );
                    break;
                case self::EVENT_ADD://8.附加分
                    $result = bcmul($active_value, 1);
                    break;
            }
            
            $user_actives[ $i ] = $result;
        }
        foreach ($user_actives as $k => $v) {
            $user_actives[$k] = number_format($v,2);
        }
        Cache::store('redis')->put( $cache_key, $user_actives, 30 );
        
        return $user_actives;
    }
    
    /**
     * 获取用户上个月活跃值
     * @param int $user_id
     * @return array
     */
    public static function lastMonthActive( $user_id )
    {
        $lastmonth = date('Ym',mktime(0,0,0,date('m')-1,date('d'),date('Y')));
        $arr = self::getUserActivePointByMonth($user_id,$lastmonth);
        $sum = array_sum($arr);
        $sum = empty($sum) ? '0.00' : bcmul($sum, 1, 2);
        return $sum;
    }
    
    /**
     * 获取用户本月活跃值
     * @param int $user_id
     * @return array
     */
    public static function currMonthActive( $user_id ) 
    {
        $arr = self::getUserActivePointByMonth($user_id,date('Ym'));
        $sum = array_sum($arr);
        $sum = empty($sum) ? '0.00' : bcmul($sum, 1, 2);
        return $sum;
    }
    
    /**
     * 获取用户当日活跃值统计
     * @param int $user_id
     * @param string $day
     * @return array
     */
    public static function getUserActivePointByDay( $user_id, $day = '0000-00-00' )
    {
        bcscale(2);
        
        $user_actives = [];
        if ( $day == '0000-00-00' ) {
            $day = date('Y-m-d');
        }
        $cache_key = CommonFunction::getUserMonthActiveCacheKey( $user_id, $day );
        $user_actives = Cache::store('redis')->get($cache_key);
        if ( !empty($user_actives) ) {
            return $user_actives;
        }
        $ActiveRealResult = new ActiveRealResult();
        $ret = $ActiveRealResult->where('dayline',$day)->where('user_id',$user_id)->get();
        
        $res = [];
        $value = $active_value = 0;
        foreach ( $ret as $obj ) {
            $type = $obj->type;
            if ( $obj->type == self::EVENT_CASHBACK || $obj->type == self::EVENT_SHOP || $obj->type == self::EVENT_ADD ) {
                $value = $obj->param1;
            } else {
                $value = $obj->param;
            }
            $res[$type] = !isset($res[$type]) ? $value : bcadd( $res[$type], $value );
        }
        for ( $i = 1; $i <= 8; $i++ ) {
            $result = 0;
            if ( !isset( $res[ $i ] ) ) {
                $user_actives[$i] = '0.00';
                continue;
            }
            
            $active_value = $res[ $i ];
            switch ( $i ) {
                case self::EVENT_SIGN://1.封顶20
                    $result = min( bcmul($active_value,0.02), 20 );
                    break;
                case self::EVENT_VIP://2.封顶30
                    $result = min( bcmul($active_value,2), 30 );
                    break;
                case self::EVENT_VOIP://3.封顶20
                    $result = min( bcmul($active_value,1), 20 );
                    break;
                case self::EVENT_CASHBACK://4.封顶20
                    $result = min( bcmul($active_value,0.1), 20 );
                    break;
                case self::EVENT_FANS://5.封顶20
                    $result = min( bcmul($active_value,1), 20 );
                    break;
                case self::EVENT_SHOP://6.封顶10
                    $result = min( bcmul($active_value,0.05), 10 );
                    break;
                case self::EVENT_CIRCLE://7.封顶30
                    $result = min( bcmul($active_value,2), 30 );
                    break;
                case self::EVENT_ADD://8.附加分
                    $result = bcmul($active_value, 1);
                    break;
            }
            
            $user_actives[$i] = empty($result) ? '0.00' : bcmul($result, 1);
        }
        Cache::store('redis')->put( $cache_key, $user_actives, 30 );
        
        return $user_actives;
    }
    
    /**
     * 获取用户日活跃明细
     * @param int $user_id
     * @param string $day
     * @param int $type
     * @return array
     */
    public static function getUserActivePointDetail( $user_id, $day, $type )
    {
        
    }
    
    /**
     * 队列数据批量获取加迁移
     * @param string $src_queue
     * @param number $len
     * @param string $target_queue
     * @return array or null
     */
    private static function getOrSyncQueue( $src_queue, $len = 5000, $target_queue = null )
    {
        $quedata = Redis::lrange($src_queue, 0, $len);
        Redis::ltrim($src_queue, $len+1, -1 );
        if ( !empty($quedata) && !empty($target_queue) ) {
            Redis::rpush($target_queue, $quedata);
        }
        return $quedata;
    }
    
    /**
     * 调试日志
     * @param \Exception $e
     * @param string $file
     */
    public static function debug_log( \Exception $e, $file = 'debug' )
    {
        try {
            if ( self::is_debug()  ) {
                if ( $file != 'debug' ) {
                    $file = 'debug-'.$file;
                }
                $date = date('Y-m-d');
                $time = date('Y-m-d H:i:s');
                Storage::disk('local')->append("active/{$file}-{$date}.log", "[{$time}]".$e->getMessage());
            }
        } catch ( \Exception $ee ) {
            
        }
    }
    
    /**
     * 活跃值模块调试异常日志开关状态
     * @return bool
     */
    private static function is_debug()
    {
        $active_debug = Redis::get('active_debug');
        if ( empty($active_debug) ) {
            return false;
        }
        return true;
    }
    
    /**
     * 接口数据日志开关
     * @param int $event_type
     */
    private static function is_debug_data($event_type)
    {
        $active_debug_log = Redis::get('active_debug_log_'.$event_type);
        if ( empty($active_debug_log) ) {
            return false;
        }
        return true;
    }
    
    /**
     * 差异性数据修复，用于实时和统计存在数据差异，弥补差异性数据
     * @param string $day 指定天
     * @param number $fixtype 指定修复类型
     */
    public static function fixData( $day = '0000-00-00', $fixtype = 0 )
    {
        $start_time = $day . ' 00:00:00';
        $end_time = $day . ' 23:59:59';
        
        switch ( $fixtype ) {            
            case self::EVENT_FANS:
                $m = new AppUserInfo();
                $mc = $m->whereBetween('create_time',[strtotime($start_time),strtotime($end_time)])->where('parent_id','>',0)->where('status',1)->count();
                $n = new ActiveRealLog();
                $nc = $n->where('dayline',$day)->where('event_type',self::EVENT_FANS)->count();
                if ( $mc > $nc ) {
                    
$sql = <<<EOT
SELECT
	FROM_UNIXTIME(a.create_time, '%Y-%m-%d') AS dayline,
	5 AS event_type,
	b.id AS user_id,
	b.parent_id AS parent_id,
	a.id AS event_val,
	FROM_UNIXTIME(
		a.create_time,
		'%Y-%m-%d %H:%i:%s'
	) AS created_at
FROM
	(
		SELECT
			*
		FROM
			lc_user
		WHERE
			create_time >= UNIX_TIMESTAMP('{$start_time}')
		AND create_time <= UNIX_TIMESTAMP('{$end_time}')
		AND parent_id > 0
		AND parent_id NOT IN (
			SELECT
				user_id
			FROM
				lc_active_real_log
			WHERE
				dayline = '{$day}'
			AND event_type = 5
		)
		AND `status` = 1
	) a
LEFT JOIN lc_user b ON a.parent_id = b.id
EOT;

                    $res = DB::connection("app38")->select($sql);
                    foreach ($res as $value) {
                        self::eventListen($value->user_id, $value->event_type, $value->event_val, 0, 0, $value->created_at, $value->parent_id );
                    }
                }
                break;
        }
    }
}