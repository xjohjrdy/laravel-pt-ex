<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Entitys\Article\ArticleInfo;
use Illuminate\Support\Facades\Cache;
use App\Services\Common\CommonFunction;
use Illuminate\Support\Facades\Redis;
use App\Services\PutaoRealActive\PutaoRealActive;
use App\Entitys\App\ActiveRealLog;
use App\Entitys\App\ActiveRealCount;
use App\Entitys\App\ActiveRealResult;

/**
 * 后台-用户活跃值事件监听处理脚本
 * @author putao
 */
class ArticleRealEventObserver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ArticleRealEventObserver {--qtype=} {--speed=} {--day=} {--month=} {--fixtype=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '活跃值脚本集合';

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
        $qtype = $this->option('qtype');
        $speed = $this->option('speed');
        $speed = $speed ? $speed : 5000;
        
        $activeRealLog = new ActiveRealLog();
        $activeRealCount = new ActiveRealCount();
        $activeRealResult = new ActiveRealResult();
        
        $this->info("command:ArticleRealEventObserver begin...");
        
        switch ( $qtype ) {
            case 1://日志处理（每分钟）
                PutaoRealActive::realLog( $speed, $activeRealLog );
                break;
            case 2://数据计算（每分钟）
                PutaoRealActive::realCount( $speed, $activeRealCount );
                break;
            case 3://实时策略（每分钟）（性能由并发数据量决定，活跃值4,6计量类型影响速度）
                PutaoRealActive::realResult( $speed, $activeRealResult );
                break;
            case 5://日统计策略（每天夜深人静时）（性能高）（指定恢复）
                $day = $this->option('day') ? $this->option('day') : date('Y-m-d',time()-86400);
                $this->info('ready to fix day result in '.$day.'...');
                $precess_info = PutaoRealActive::reResultDay( $day );
                $this->info($precess_info);
                $this->info('complete done...');
                break;
            case 6://月统计策略（异常处理用）
                break;
            case 7://日重构（数据异常处理用，一般用不到）
                $day = $this->option('day');
                PutaoRealActive::reCountDay( $speed, $day, $activeRealCount, $activeRealLog );
                $precess_info = PutaoRealActive::reResultDay( $day );
                $this->info($precess_info);
                $this->info('complete done...');
                break;
            case 8://月重构（数据异常处理用，一般用不到）
                break;
            case 9://差异性修复
                $day = $this->option('day') ? $this->option('day') : date('Y-m-d',time()-86400);
                $fixtype = $this->option('fixtype');
                if ( $fixtype ) {
                    PutaoRealActive::fixData($day, $fixtype);
                }
                break;
        }
    }

}
