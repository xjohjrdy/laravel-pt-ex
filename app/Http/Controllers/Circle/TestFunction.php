<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\ExchangeGrapeOrder;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Ad\VoipAccount;
use App\Entitys\Ad\VoipMoneyOrder;
use App\Entitys\Ad\VoipMoneyOrderMaid;
use App\Entitys\Ad\VoipType;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\PretendShopOrdersMaid;
use App\Entitys\App\ReturnBack;
use App\Entitys\App\ShopIndex;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersOne;
use App\Services\Advertising\AdvertisingUser;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\Shop\Order;
use App\Services\Voip\Buy;
use App\Services\Voip\Call;
use App\Services\Wechat\Wechat;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order, Wechat $wechat, AdvertisingUser $advertisingUser, Client $client, ShopIndex $shopIndex, ExchangeGrapeOrder $exchangeGrapeOrder, ReturnBack $returnBack, RechargeCreditLog $rechargeCreditLog, ShopOrdersOne $shopOrdersOne, RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, Call $call, Buy $buy, VoipType $voipType, VoipMoneyOrder $voipMoneyOrder, VoipAccount $voipAccount, ShopOrders $shopOrders, UserAboutLog $aboutLog, UserCreditLog $creditLog, AdUserInfo $adUserInfo, UserAccount $userAccount, VoipMoneyOrderMaid $voipMoneyOrderMaid, AppUserInfo $appUserInfo, PretendShopOrdersMaid $pretendShopOrdersMaid)
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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $res = $this->exchangeGrapeOrder->where(['status' => 1])->get();
        $bar = $this->output->createProgressBar($res->count());
        $arr = [];
        foreach ($res as $item) {
            $bar->advance();
            $updated_at = date('Y-M-d', $item->upts);
            if (empty($arr[$updated_at])) {
                $arr[$updated_at] = 0;
            }
            $arr[$updated_at] += $item->amount;
        }
        $bar->finish();
        var_dump($arr);
        exit();

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
