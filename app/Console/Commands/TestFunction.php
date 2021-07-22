<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\CommissionLog;
use App\Entitys\Ad\ExchangeGrapeCard;
use App\Entitys\Ad\ExchangeGrapeOrder;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Ad\UserWork;
use App\Entitys\Ad\UserWorkRecord;
use App\Entitys\Ad\VoipAccount;
use App\Entitys\Ad\VoipMoneyOrder;
use App\Entitys\Ad\VoipMoneyOrderMaid;
use App\Entitys\Ad\VoipType;
use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\ApplyCash;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\CircleCommonNotify;
use App\Entitys\App\CircleOrder;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\JdGetOneShow;
use App\Entitys\App\JdPhonePutIn;
//use App\Entitys\App\MarketUser;
use App\Entitys\App\NoShowAndroid;
use App\Entitys\App\PretendShopOrdersMaid;
use App\Entitys\App\ReturnBack;
use App\Entitys\App\ShopAddress;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndex;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\ShopOrdersOne;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoEnterOrder;
use App\Entitys\App\TaobaoMaid;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\App\TaobaoUser;
use App\Entitys\App\TaobaoUserGet;
use App\Entitys\App\UserOrderTao;
use App\Entitys\Xin\Notice;
use App\Entitys\Xin\UserIdol;
use App\Services\Advertising\AdvertisingUser;
use App\Services\Alimama\BigWashUser;
use App\Services\Circle\BecomeHost;
use App\Services\Commands\CountActiveness;
use App\Services\Common\NewSms;
//use App\Services\Market\Sms;
use App\Services\Recharge\PurchaseUserGroup;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\Shop\Order;
use App\Services\Taobaoke\Utils;
use App\Services\Voip\Buy;
use App\Services\Voip\Call;
use App\Services\Voip\Special;
use App\Services\Wechat\Wechat;
use App\Services\Xin\GroupManageServices;
use App\Services\ZhongKang\ZhongKangServices;
use ETaobao\Factory;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;

class TestFunction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TestFunction';
    /*
        20181010165653gL4AY
        20181008090812NOv0n
        20181008090543nNSLz
        201810011003099jgvy
         *
         *
         */
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用来测试方法，防止上线方法出现错误';

    protected $order;
    protected $appUserInfo;
    protected $pretendShopOrdersMaid;
    protected $shopOrders;
    protected $voipMoneyOrderMaid;
    protected $adUserInfo;
    protected $userAccount;
    protected $aboutLog;
    protected $creditLog;
    protected $voipMoneyOrder;
    protected $voipAccount;
    protected $call;
    protected $buy;
    protected $voipType;
    protected $rechargeUserLevel;
    protected $rechargeOrder;
    protected $client;
    protected $excel;
    protected $shopOrdersOne;
    protected $returnBack;
    protected $rechargeCreditLog;
    protected $exchangeGrapeOrder;
    protected $shopIndex;
    protected $wechat;
    protected $advertisingUser;
    protected $exchangeGrapeCard;
    protected $userWorkRecord;
    protected $shopAddress;
//    protected $marketUser;
    protected $applyCash;
    protected $bonusLog;
    protected $userOrderTao;
    protected $shopGoods;
    protected $circleRing;
    protected $circleOrder;
    protected $circleRingAdd;
    protected $jdPhonePutIn;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order, JdPhonePutIn $jdPhonePutIn, BonusLog $bonusLog, CircleRing $circleRing, CircleOrder $circleOrder, CircleRingAdd $circleRingAdd, ShopGoods $shopGoods, UserOrderTao $userOrderTao, Wechat $wechat,  ShopAddress $shopAddress, UserWorkRecord $userWorkRecord, ExchangeGrapeCard $exchangeGrapeCard, AdvertisingUser $advertisingUser, Client $client, ShopIndex $shopIndex, ExchangeGrapeOrder $exchangeGrapeOrder, ReturnBack $returnBack, RechargeCreditLog $rechargeCreditLog, ShopOrdersOne $shopOrdersOne, RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, Call $call, Buy $buy, VoipType $voipType, VoipMoneyOrder $voipMoneyOrder, VoipAccount $voipAccount, ShopOrders $shopOrders, UserAboutLog $aboutLog, UserCreditLog $creditLog, AdUserInfo $adUserInfo, UserAccount $userAccount, VoipMoneyOrderMaid $voipMoneyOrderMaid, AppUserInfo $appUserInfo, PretendShopOrdersMaid $pretendShopOrdersMaid)
    {
        parent::__construct();
        $this->order = $order;
        $this->appUserInfo = $appUserInfo;
        $this->rechargeOrder = $rechargeOrder;
        $this->rechargeUserLevel = $rechargeUserLevel;
        $this->pretendShopOrdersMaid = $pretendShopOrdersMaid;
        $this->shopOrders = $shopOrders;
        $this->voipMoneyOrderMaid = $voipMoneyOrderMaid;
        $this->voipMoneyOrder = $voipMoneyOrder;
        $this->adUserInfo = $adUserInfo;
        $this->userAccount = $userAccount;
        $this->aboutLog = $aboutLog;
        $this->creditLog = $creditLog;
        $this->voipAccount = $voipAccount;
        $this->call = $call;
        $this->buy = $buy;
        $this->client = $client;
        $this->voipType = $voipType;
        $this->shopOrdersOne = $shopOrdersOne;
        $this->returnBack = $returnBack;
        $this->rechargeCreditLog = $rechargeCreditLog;
        $this->exchangeGrapeOrder = $exchangeGrapeOrder;
        $this->shopIndex = $shopIndex;
        $this->wechat = $wechat;
        $this->advertisingUser = $advertisingUser;
        $this->exchangeGrapeCard = $exchangeGrapeCard;
        $this->userWorkRecord = $userWorkRecord;
        $this->shopAddress = $shopAddress;
        $this->userOrderTao = $userOrderTao;
        $this->bonusLog = $bonusLog;
        $this->shopGoods = $shopGoods;
        $this->circleRing = $circleRing;
        $this->circleOrder = $circleOrder;
        $this->circleRingAdd = $circleRingAdd;
        $this->jdPhonePutIn = $jdPhonePutIn;
    }

    /**
     * 分佣
     * @param $order_id
     * @return int
     */
    public
    function merge_maid($order_id)
    {
        $host = new BecomeHost();
        $circle_value = [];
        $order_info = $host->getInfoByOrderId($order_id);
        $circle_id = $order_info->circle_id;
        $circle_info = $host->getCircleInfo($circle_id);
        $circle_value['app_id'] = $order_info->app_id;
        $circle_value['price'] = $order_info->money * 1.2;
        $host->updateCircleNotNumber($order_id, $circle_id, $circle_value);
        $host->newBonus($order_id);
        $king_id = $circle_info->id;
        $host->kingBonus($king_id, $order_id);
        $host->addCircle($order_info->app_id, $circle_id);
        return 1;
    }

    /**
     * 处理非首次分佣的用户
     * @param $order_id
     * @return int
     * @throws \App\Exceptions\ApiException
     */
    public
    function no_merge_maid($order_id)
    {
        $host = new BecomeHost();
        $order_info = $host->getInfoByOrderId($order_id);
        $circle_id = $order_info->circle_id;
        $circle_info = $host->getCircleInfo($circle_id);
        $quondam_app_id = $circle_info->app_id;
        $app_id = $order_info->app_id;

        if ($order_info->money < $circle_info->price) {
            var_dump("当前订单金额，远小于圈子本身价格，不能执行逻辑，否则会有严重影响");
            exit();
        }
        $circle_params['app_id'] = $app_id;
        $circle_params['price'] = $order_info->money * 1.2;
        if ($host->isLock($circle_id)) {
            $circle_params['close'] = 1;
        }
        if (!empty($area)) {
            $circle_params['area'] = $area;
        }

        $host->updateCircle($order_id, $circle_id, $circle_params);
        $host->addCircle($order_info->app_id, $circle_id);
        $host->demotion($quondam_app_id, $circle_id);
        $return_ptb = round($order_info->money * 10 * config('ring.return_money'));
        $host->addPtb($quondam_app_id, $return_ptb);
        $host->addBoundsLog($quondam_app_id, $app_id, $order_id, $return_ptb);

        $re_obj_user = AppUserInfo::find($app_id);
        $obj_notify = new CircleCommonNotify();
        $n_data = [];
        $n_data['app_id'] = $quondam_app_id;
        $n_data['ico'] = 'http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/logo/logo.png';
        $n_data['username'] = '系统通知';
        if (empty($re_obj_user->user_name)) {
            $re_obj_user->user_name = 'ID：' . $re_obj_user->id;
        }
        $n_data['notify'] = "{$re_obj_user->user_name} 花费 " . ($order_info->money * 10) . "葡萄币，抢购了您的“{$circle_info->ico_title}”圈子！";
        $n_data['to_id'] = $circle_id;
        $n_data['type'] = 3;
        $obj_notify->addNotify($n_data);
        $host->bidBonus($order_id);
        return 1;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $no_show = new NoShowAndroid();
        $version = $no_show->getOneVersion();
        if ($version == '165') {
            var_dump(1);
        }
        var_dump($version);
        exit();


        ## 特殊查询需求---分析用户

        ## 特殊查询需求---分析用户

        /**
         * 这堆用户，充值的时候，没有找到系统的账号
         * string(11) "13704057975"
         * string(11) "13704057975"
         * string(11) "13938972820"
         * string(11) "13938972820"
         * string(11) "13649747186"
         * string(11) "13649747186"
         * string(11) "15043561212"
         * string(11) "17307443455"
         * string(11) "13841991785"
         * string(11) "13197396181"
         * string(11) "13013506788"
         * string(11) "13668163178"
         * string(11) "13668163178"
         * string(11) "15191710424"
         * string(11) "18577789333"
         * string(11) "17531386346"
         * string(11) "17531386346"
         * string(11) "18952908152"
         * string(11) "15326285162"
         * string(11) "17634039331"
         * string(11) "18117377585"
         * string(11) "18117377585"
         * string(11) "15276535231"
         * string(11) "17505319773"
         * string(11) "15966578743"
         * string(11) "15804181144"
         * string(11) "15804181144"
         * string(11) "18691521307"
         * string(11) "18615334288"
         * string(11) "18859697887"
         * string(11) "18281665586"
         * string(11) "18281665586"
         * string(11) "13639388208"
         * string(11) "13614641659"
         * string(11) "13875856897"
         * string(11) "17531163972"
         * string(11) "13513003080"
         * string(11) "17531163972"
         * string(11) "17531163972"
         * string(11) "17531163972"
         * string(11) "17520441913"
         * string(11) "17531163972"
         * string(11) "18131595855"
         * string(11) "15711074277"
         * string(11) "18993358198"
         * string(11) "18993358198"
         * string(11) "18993358198"
         * string(11) "18150280119"
         * string(11) "13455936476"
         * string(11) "13455936476"
         * string(11) "13227814397"
         * string(11) "13089349493"
         * string(11) "18095444777"
         * string(11) "13003585949"
         * string(11) "15162351615"
         * string(11) "13117092393"
         * string(11) "18186830669"
         * string(11) "18186831351"
         * string(11) "13152827347"
         * string(11) "13898548036"
         */
    }


}
