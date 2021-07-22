<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
//        'App\Console\Commands\CountUserActiveTemp',
//        'App\Console\Commands\CountUserActiveTempTest',
        'App\Console\Commands\testComm',
        'App\Console\Commands\TestDemo',
        'App\Console\Commands\CountActiveTogether',
        'App\Console\Commands\CountArticleNumber',
//        'App\Console\Commands\AllBuyBack',
        'App\Console\Commands\AllErrorUserCount',
        'App\Console\Commands\WarnCodeUseFinish',
        'App\Console\Commands\CountUserHigh',
        'App\Console\Commands\MaidNoMaid',
        'App\Console\Commands\DissatisfactionOption',
        'App\Console\Commands\GetArticleByWu',
        'App\Console\Commands\JdOrder',
        'App\Console\Commands\AutoConfirm',
        'App\Console\Commands\AutoCalculateDailyFinancial',
//        'App\Console\Commands\TestFunction',
        'App\Console\Commands\testScript',
        'App\Console\Commands\AutoTestStepOne',
        'App\Console\Commands\AutoTestStepTwo',
        'App\Console\Commands\AutoPayOff',

        'App\Console\Commands\ClearUseless',
        'App\Console\Commands\TaobaoEnter',
        'App\Console\Commands\TaobaoEnterAll',
		'App\Console\Commands\TaobaoEnterDetails',
        'App\Console\Commands\TaobaoEnterLow',
        'App\Console\Commands\TaobaoEnterAllLow',
        'App\Console\Commands\CountEverydayActiveTemp',
        'App\Console\Commands\CountEverydayActiveTempL1',
        'App\Console\Commands\CountEverydayActiveSum',
        'App\Console\Commands\CountEverydayActiveSumL1',
        'App\Console\Commands\CountOrderAmount',

        'App\Console\Commands\TaobaoEnterPush',
        'App\Console\Commands\IndexArticleUpdate',
        'App\Console\Commands\ArticleRealEventObserver',
        'App\Console\Commands\ActiveRealEventObserver',

        'App\Console\Commands\PushExchangeUser',
        'App\Console\Commands\MedicalLeadData',
		'App\Console\Commands\TempFun',
		'App\Console\Commands\TempFun1',
		'App\Console\Commands\TempFun2',
		'App\Console\Commands\TempFun3',
		'App\Console\Commands\TempFun4',
		'App\Console\Commands\TempFun5',
		'App\Console\Commands\ChannelTaobaoEnterDetails',
		'App\Console\Commands\JdFalseCommission',
		'App\Console\Commands\SyncJdOrders',
		'App\Console\Commands\SyncJdOrders2',
		'App\Console\Commands\JdCountOrderAmount',
		'App\Console\Commands\SyncICardOrders2',
		
		'App\Console\Commands\PddCountOrderAmount',
		'App\Console\Commands\TaobaoMissingOrder',
        'App\Console\Commands\PddEnterOrder',
		'App\Console\Commands\SyncPddOrders',
		'App\Console\Commands\SyncICardOrders',
		'App\Console\Commands\OneGoTaobaoEnterOrder',
		'App\Console\Commands\AuthRegister',
		'App\Console\Commands\SyncPddOrders2',
		'App\Console\Commands\AATest',
		
		'App\Console\Commands\ClearSign',
        'App\Console\Commands\WuHangTest',
		 'App\Console\Commands\CountUserGrowthValue',
		 'App\Console\Commands\CalUserIncomeEveryday',

        'App\Console\Commands\CalUserInitDataEveryday',
		'App\Console\Commands\CalUserIncome2020',
		'App\Console\Commands\SendAppAlerts',
		
		'App\Console\Commands\ElemeEnterOrder',
        'App\Console\Commands\EleCountOrderAmount',
		'App\Console\Commands\OtherEleCountOrderAmount',
		'App\Console\Commands\PtbToMoneyAllUser',
		'App\Console\Commands\OtherPddCountOrderAmount', 
        'App\Console\Commands\OtherJdCountOrderAmount',
        'App\Console\Commands\OtherTaobaoCountOrderAmount',
		'App\Console\Commands\CalUserInitDataOneTime',
		'App\Console\Commands\OtherPddMaidOld',
		'App\Console\Commands\OtherJdMaidOld',
        'App\Console\Commands\OtherTaoBaoMaidOld',
		'App\Console\Commands\OtherCardMaidOld',
		'App\Console\Commands\OtherElemeMaidOld',
		'App\Console\Commands\OtherMtMaidOld',//管理费 美团假分佣
        'App\Console\Commands\OtherMtCountOrderAmount',//管理费 美团假分佣变真分佣
		'App\Console\Commands\PddEnterOrderOneTime',
		
		'App\Console\Commands\MtEnterOrder',
		'App\Console\Commands\MtCountOrderAmount',
		'App\Console\Commands\MorningMoney',
		'App\Console\Commands\HandleHarryNoCallOrder',//众薪未回调订单修复
		'App\Console\Commands\Test',
		
		'App\Console\Commands\PayMonitoring',//支付监控


        'App\Console\Commands\AutoTodayMoney',//自动计算每日
        'App\Console\Commands\AutoTodayMoneyManageMent',//自动计算每日管理费

        'App\Console\Commands\AutoMaid',//自动分佣
		
		'App\Console\Commands\FuluGoodsInfo',//福禄商品拉取
		'App\Console\Commands\Assistant\SendInfo',//机器人脚本30分钟一次
		
		'App\Console\Commands\LivePushWatchNum',//直播观看人数推送
		
		'App\Console\Commands\OtherEleMaidSum',
        'App\Console\Commands\OtherJdMaidSum',
        'App\Console\Commands\OtherTaobaoMaidSum',
        'App\Console\Commands\OtherPddMaidSum',
		
		'App\Console\Commands\TurntableOrdersSignFor',//转盘实物自动确认收货
		'App\Console\Commands\Invite\InviteRank',// 新人邀请脚本统计
        'App\Console\Commands\Invite\DivideMoney',// 新人邀请奖金瓜分礼物发放
		'App\Console\Commands\User\WithdrawReject',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
