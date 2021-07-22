<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CardMaid;
use App\Entitys\App\CircleMaid;
use App\Entitys\App\CircleOrder;
use App\Entitys\App\GrowthUserIncome;
use App\Entitys\App\JdMaidOld;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\PretendShopOrdersMaid;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\TaobaoMaidOld;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalUserIncomeEveryday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CalUserIncomeEveryday';
    protected $growthModel = null;
    protected $growthUserModel = null;
    private $begin_time = ''; // 起始日期
    private $end_time = '';
    private $begin_timestamp = ''; //起始时间轴
    private $end_timestamp = '';
    private $month = '';
    private $adUserModel = null;
    private $appUserModel = null;
    private $page_size = 10000;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天统计用户的个渠道收入';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->growthModel = new GrowthUserIncome();
        $this->growthUserModel = new GrowthUserIncome();
        $this->adUserModel = new AdUserInfo();
        $this->appUserModel = new AppUserInfo();
        $month = date('Y-m', time());
        $this->month = $month;
        $this->begin_time = date('Y-m-01 00:00:00', strtotime($month));
        $this->end_time = date('Y-m-d 23:59:59', strtotime("$this->begin_time +1 month -1 day"));
        $this->begin_timestamp = strtotime($this->begin_time);
        $this->end_timestamp = strtotime($this->end_time);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //mktime(1,1,1,11,1,2019)
        try{
            $this->growthModel->clearIncomeByMonth($this->month); // 同步之前更新当前月份的金额为0
            $this->info('开始：' . $this->month);
            $this->calArticle();
            $this->calCard();
            $this->calCircle();
            $this->calJd();
            $this->calPdd();
            $this->calSales();
            $this->calShop();
            $this->calTaoBao();
            $this->info('结束！' );
        } catch (\Exception $e){
            $this->info('异常：' . $e->getMessage());
        }
    }

    /**
     * 销售收入计算
     */
    public function calSales()
    {
        $aljbModel = new RechargeCreditLog();
        $shopOrdersModel = new ShopOrders();
        $count = $aljbModel->whereBetween('dateline', [$this->begin_timestamp, $this->end_timestamp])->count();
        $page_total = ceil($count / $this->page_size); #总页数
        $this->info('销售佣金计算, 总量 ：' . $count . ' 共' . $page_total . '页');
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $credit_list = $aljbModel->whereBetween('dateline', [$this->begin_timestamp, $this->end_timestamp])->forPage($page, $this->page_size)->get();
            foreach ($credit_list as $key => $item) {
                $order = $shopOrdersModel->getByOrderId($item['orderid']); // 找到订单
                if (empty($order)) {
                    continue;
                }
                $user = $this->adUserModel->getUserById($item['uid']); // uid 转 pt_id 及 app_id;
                $appUser = $this->appUserModel->getUserById($order['app_id']); // 查找该用户上级
                if ($user['pt_id'] == $appUser['parent_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                    $this->growthUserModel->createOrUpdateColumn($user['pt_id'], $this->month, 'growth_sale_one', $item['money'] / 10); // 单位葡萄币
                } else {
                    $this->growthUserModel->createOrUpdateColumn($user['pt_id'], $this->month, 'growth_sale_two', $item['money'] / 10);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
    }

    /**
     * 圈子收入计算
     */
    public function calCircle()
    {
        $circleMaidModel = new CircleMaid();
        $circleOrderModel = new CircleOrder();
        $count = $circleMaidModel->whereBetween('created_at', [$this->begin_time, $this->end_time])->count();
        $page_total = ceil($count / $this->page_size); #总页数
        $this->info('圈子佣金计算, 总量 ：' . $count . ' 共' . $page_total . '页');
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $circleMaids = $circleMaidModel->whereBetween('created_at', [$this->begin_time, $this->end_time])->forPage($page, $this->page_size)->get();
            foreach ($circleMaids as $key => $item) {
                $order = $circleOrderModel->getByOrderId($item['order_id']);
                $appUser = $this->appUserModel->getUserById($order['app_id']);
                if ($item['app_id'] == $appUser['parent_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_circle_one', $item['money'] / 10); // 单位葡萄币
                } else {
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_circle_two', $item['money'] / 10);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
    }

    /**
     * 淘宝收入计算
     */
    public function calTaoBao()
    {
        $taoBaoMaidModel = new TaobaoMaidOld();
        $count = $taoBaoMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->count();
        $page_total = ceil($count / $this->page_size); #总页数
        $this->info('淘宝佣金计算, 总量 ：' . $count . ' 共' . $page_total . '页');
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $taoBaoMaids = $taoBaoMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->forPage($page, $this->page_size)->get();
            foreach ($taoBaoMaids as $key => $item) {
                $appUser = $this->appUserModel->getUserById($item['father_id']); // 查找该用户上级
                if ($item['app_id'] == $appUser['parent_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_taobao_one', $item['maid_money']); // 单位元
                } else {
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_taobao_two', $item['maid_money']);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
    }


    /**
     * 京东收入计算
     */
    public function calJd()
    {
        $jdMaidModel = new JdMaidOld();
        $count = $jdMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->count();
        $page_total = ceil($count / $this->page_size); #总页数
        $this->info('京东佣金计算, 总量 ：' . $count . ' 共' . $page_total . '页');
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $jdMaids = $jdMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->forPage($page, $this->page_size)->get();
            foreach ($jdMaids as $key => $item) {

                if ($item['app_id'] == $item['father_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_jd_one', $item['maid_money']);
                } else {
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_jd_two', $item['maid_money']);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
    }

    /**
     * 拼多多收入计算
     */
    public function calPdd()
    {
        $pddMaidModel = new PddMaidOld();
        $count = $pddMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->count();
        $page_total = ceil($count / $this->page_size); #总页数
        $this->info('拼多多佣金计算, 总量 ：' . $count . ' 共' . $page_total . '页');
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $pddMaids = $pddMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->forPage($page, $this->page_size)->get();
            foreach ($pddMaids as $key => $item) {
                $appUser = $this->appUserModel->getUserById($item['father_id']); // 查找该用户上级
                if ($item['app_id'] == $appUser['parent_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_pdd_one', $item['maid_money']); // 单位元
                } else {
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_pdd_two', $item['maid_money']);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
    }

    /**
     * 信用卡分佣计算
     */
    public function calCard()
    {
        $cardMaidModel = new CardMaid();
        $count = $cardMaidModel->where(['type' => 2])->whereBetween('created_at', [$this->begin_time, $this->end_time])->count();
        $page_total = ceil($count / $this->page_size); #总页数
        $this->info('信用卡佣金计算, 总量 ：' . $count . ' 共' . $page_total . '页');
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $cardMaids = $cardMaidModel->where(['type' => 2])->whereBetween('created_at', [$this->begin_time, $this->end_time])->forPage($page, $this->page_size)->get();
            foreach ($cardMaids as $key => $item) {
                $appUser = $this->appUserModel->getUserById($item['father_id']); // 查找该用户上级
                if ($item['app_id'] == $appUser['parent_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_card_one', $item['maid_ptb'] / 10); // 单位葡萄比
                } else {
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_card_two', $item['maid_ptb'] / 10);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
    }

    /**
     * 商城收入计算
     */
    public function calShop()
    {
        $shopMaidModel = new PretendShopOrdersMaid();
        $shopOrdersModel = new ShopOrders();
        $count = $shopMaidModel->whereBetween('created_at', [$this->begin_time, $this->end_time])->count();
        $page_total = ceil($count / $this->page_size); #总页数
        $this->info('商城佣金计算, 总量 ：' . $count . ' 共' . $page_total . '页');
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $shopMaids = $shopMaidModel->whereBetween('created_at', [$this->begin_time, $this->end_time])->forPage($page, $this->page_size)->get();
            foreach ($shopMaids as $key => $item) {
                $order = $shopOrdersModel->getByOrderId($item['order_id']); // 找到订单
                if (empty($order)) {
                    continue;
                }
                $appUser = $this->appUserModel->getUserById($order['app_id']); // 查找该用户上级
                if ($item['app_id'] == $appUser['parent_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_shop_one', $item['money'] / 10); // 单位葡萄比
                } else {
                    $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_shop_two', $item['money'] / 10);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
    }

    /**
     * 广告包收入计算
     */
    public function calArticle()
    {
        $aljbModel = new RechargeCreditLog();
        $shopOrdersModel = new ShopOrders();
        $count = $aljbModel->where('money', '<', 50)->whereBetween('dateline', [$this->begin_timestamp, $this->end_timestamp])->count();
        $page_total = ceil($count / $this->page_size); #总页数
        $this->info('广告包佣金计算, 总量 ：' . $count . ' 共' . $page_total . '页');
        $bar = $this->output->createProgressBar($page_total);
        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            $credit_list = $aljbModel->where('money', '<', 50)->whereBetween('dateline', [$this->begin_timestamp, $this->end_timestamp])->forPage($page, $this->page_size)->get();
            foreach ($credit_list as $key => $item) {
                $order = $shopOrdersModel->getByOrderId($item['orderid']); // 找到订单
                if (empty($order)) {
                    continue;
                }
                $user = $this->adUserModel->getUserById($item['uid']); // uid 转 pt_id 及 app_id;
                $appUser = $this->appUserModel->getUserById($order['app_id']); // 查找该用户上级
                if ($user['pt_id'] == $appUser['parent_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                    $this->growthUserModel->createOrUpdateColumn($user['pt_id'], $this->month, 'growth_sale_one', $item['money'] / 10); // 单位葡萄比
                } else {
                    $this->growthUserModel->createOrUpdateColumn($user['pt_id'], $this->month, 'growth_sale_two', $item['money'] / 10);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
    }
}
