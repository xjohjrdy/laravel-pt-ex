<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testDemo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用于测试一些功能';

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
        $this->info('start');
        $countList = DB::connection('app38')
            ->table('lc_user')
            ->where('level', '>=',2)
            ->get(['id','history_active_value'])
        ;
        $time30 = "1530288000";

        foreach ($countList as $singleValue){
            $this->info('-------------------------'.$singleValue->id);
            $this->info($singleValue->history_active_value);

            $userActive= DB::connection('app38')
                ->table('lc_active_every_days')
                ->where(['time'=>$time30,'uid'=>$singleValue->id])
                ->first(); 
				
			if($userActive){
				$jsonValTemp = $userActive->context;
			}else{
				$jsonValTemp = '{}';
			}

            $this->info($jsonValTemp);
            $arrVal = json_decode($jsonValTemp,true);
            $newActive = @array_sum($arrVal);

            if ($newActive>$singleValue->history_active_value){ 
                $this->info('OK'.$newActive.'--'.$singleValue->history_active_value);
                DB::connection('app38')
                    ->table('lc_user')
                    ->where('id',$singleValue->id)
                    ->update(['history_active_value'=>$newActive]);


            }else{
                $this->info('NO');
            }
        }
    }
}
