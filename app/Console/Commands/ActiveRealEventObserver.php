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
use Symfony\Component\Console\Helper\ProgressBar;
use App\Entitys\App\ActiveRealSign;
use Illuminate\Support\Facades\Storage;
use App\Entitys\App\ActiveRealResultMonth;

/**
 * 后台-用户活跃值事件监听处理脚本
 * @author putao
 */
class ActiveRealEventObserver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ActiveRealEventObserver {--qtype=} {--speed=} {--fixtype=}';

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
        $activeRealSign = new ActiveRealSign();
        $activeRealResultMonth = new ActiveRealResultMonth();
        
        $this->info("command:ActiveRealEventObserver begin...");
        
        switch ( $qtype ) {
            case 9://按日全量修复脚本
                $day = $this->ask('pls enter date(exp:2019-07-08) to build repair sql ? ');     
                
                if( date('Y-m-d', strtotime($day)) != $day ) {
                    return $this->info('not date');
                }                
                if ($this->confirm('are you sure to do this ? [y|n]')) {
                    $sql = '';
                    $sql .= PutaoRealActive::truncate($day, $activeRealLog, $activeRealCount, $activeRealResult, $activeRealResultMonth);
                    $sql .= PutaoRealActive::rebuildAll($day, $activeRealLog, $activeRealCount, $activeRealSign);
                    $sql .= PutaoRealActive::reResultDay($day, $activeRealResult, $activeRealResultMonth);
                    Storage::disk('local')->put("active/repair-day-{$day}.sql", $sql);
                    }
                break;
            case 10://按月全量修复脚本
                $month = $this->ask('pls enter month(exp:2019-07) to build repair sql ? ');
                if( date('Y-m', strtotime($month)) != $month ) {
                    return $this->info('not month');
                }                
                if ($this->confirm('are you sure to do this ? [y|n]')) {                    
                    $sql = '';
                    $days = date('t',strtotime($month));
                    for ( $i = 1; $i<=$days; $i++ ) {
                        $day = $month .'-'.str_pad($i, 2, '0', STR_PAD_LEFT);
                        $sql .= PutaoRealActive::truncate($day, $activeRealLog, $activeRealCount, $activeRealResult, $activeRealResultMonth);
                        $sql .= PutaoRealActive::rebuildAll($day, $activeRealLog, $activeRealCount, $activeRealSign);
                        $sql .= PutaoRealActive::reResultDay($day, $activeRealResult, $activeRealResultMonth);
                    }                    
                    Storage::disk('local')->put("active/repair-month-{$month}.sql", $sql);
                }
                
                break;
            default:
                PutaoRealActive::realLog( $speed, $activeRealLog );
                PutaoRealActive::realCount( $speed, $activeRealCount );
                PutaoRealActive::realResult( $speed, $activeRealResult, $activeRealResultMonth );
                break;
        }
    }

}
