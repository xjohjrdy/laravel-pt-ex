<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Entitys\App\AppUserInfo;
use App\Observers\UserObserver;
use App\Entitys\App\CircleOrder;
use App\Observers\CircleOrderObserver;
use App\Entitys\Ad\VoipMoneyOrder;
use App\Entitys\App\SignLog;
use App\Observers\SignLogObserver;
use App\Entitys\App\ShopOrders;
use App\Observers\ShopOrdersObserver;
use App\Observers\Ad\VoipMoneyOrderObserver;
use App\Entitys\Ad\RechargeOrder;
use App\Observers\Ad\RechargeOrderObserver;
use App\Entitys\App\UserOrderNew;
use App\Observers\UserOrderNewObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //葡萄用户
//        AppUserInfo::observe(UserObserver::class);

        //广告联盟
//        RechargeOrder::observe(RechargeOrderObserver::class);

        //圈子
//        CircleOrder::observe(CircleOrderObserver::class);

        //报销
//        UserOrderNew::observe(UserOrderNewObserver::class);

        //葡萄通讯
//        VoipMoneyOrder::observe(VoipMoneyOrderObserver::class);

        //签到
//        SignLog::observe(SignLogObserver::class);

        //爆款商城
//        ShopOrders::observe(ShopOrdersObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
