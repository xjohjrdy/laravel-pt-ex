<?php


namespace App\Services\Growth;


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

class CalGrowthMoney
{
    protected $growthUserModel = null;
    private $begin_time = ''; // 起始日期
    private $end_time = '';
    private $begin_timestamp = ''; //起始时间轴
    private $end_timestamp = '';
    private $month = '';
    private $adUserModel = null;
    private $appUserModel = null;

    /**
     * CalGrowthMoney constructor.
     * @param null $app_id
     * @param null $month
     */
    public function __construct($month)
    {
        $this->month = $month;
        $this->growthUserModel = new GrowthUserIncome();
        $this->adUserModel = new AdUserInfo();
        $this->appUserModel = new AppUserInfo();
        $this->begin_time = date('Y-m-01 00:00:00', strtotime($month));
        $this->end_time = date('Y-m-d 23:59:59', strtotime("$this->begin_time +1 month -1 day"));
        $this->begin_timestamp = strtotime($this->begin_time);
        $this->end_timestamp = strtotime($this->end_time);
    }


    /**
     * 销售收入计算
     */
    public function calSales()
    {
        $aljbModel = new RechargeCreditLog();
        $shopOrdersModel = new ShopOrders();
        $credit_list = $aljbModel->whereBetween('dateline', [$this->begin_timestamp, $this->end_timestamp])->get();
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
    }

    /**
     * 圈子收入计算
     */
    public function calCircle()
    {
        $circleMaidModel = new CircleMaid();
        $circleOrderModel = new CircleOrder();
        $circleMaids = $circleMaidModel->whereBetween('created_at', [$this->begin_time, $this->end_time])->get();
        foreach ($circleMaids as $key => $item) {
            $order = $circleOrderModel->getByOrderId($item['order_id']);
            $appUser = $this->appUserModel->getUserById($order['app_id']);
            if ($item['app_id'] == $appUser['parent_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_circle_one', $item['money'] / 10); // 单位葡萄币
            } else {
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_circle_two', $item['money'] / 10);
            }
        }
    }

    /**
     * 淘宝收入计算
     */
    public function calTaoBao()
    {
        $taoBaoMaidModel = new TaobaoMaidOld();
        $taoBaoMaids = $taoBaoMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->get();

        foreach ($taoBaoMaids as $key => $item) {
            if ($item['app_id'] == $item['father_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_taobao_one', $item['maid_money']); // 单位元
            } else {
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_taobao_two', $item['maid_money']);
            }
        }
    }

    /**
     * 京东收入计算
     */
    public function calJd()
    {
        $jdMaidModel = new JdMaidOld();
        $jdMaids = $jdMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->get();
        foreach ($jdMaids as $key => $item) {
            if ($item['app_id'] == $item['father_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_jd_one', $item['maid_money']);
            } else {
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_jd_two', $item['maid_money']);
            }
        }
    }

    /**
     * 拼多多收入计算
     */
    public function calPdd()
    {
        $pddMaidModel = new PddMaidOld();
        $pddMaids = $pddMaidModel->where(['type' => 1])->whereBetween('created_at', [$this->begin_time, $this->end_time])->get();
        foreach ($pddMaids as $key => $item) {
            if ($item['app_id'] == $item['father_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_pdd_one', $item['maid_money']); // 单位元
            } else {
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_pdd_two', $item['maid_money']);
            }
        }
    }

    /**
     * 信用卡分佣计算
     */
    public function calCard()
    {
        $cardMaidModel = new CardMaid();
        $cardMaids = $cardMaidModel->where(['type' => 2])->whereBetween('created_at', [$this->begin_time, $this->end_time])->get();
        foreach ($cardMaids as $key => $item) {
            if ($item['app_id'] == $item['from_app_id']) { // 如果相等，则是直推收入，如果不相当则是三级以外，称之服务费
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_card_one', $item['maid_ptb'] / 10); // 单位葡萄比
            } else {
                $this->growthUserModel->createOrUpdateColumn($item['app_id'], $this->month, 'growth_card_two', $item['maid_ptb'] / 10);
            }
        }
    }

    /**
     * 商城收入计算
     */
    public function calShop()
    {
        $shopMaidModel = new PretendShopOrdersMaid();
        $shopOrdersModel = new ShopOrders();
        $shopMaids = $shopMaidModel->whereBetween('created_at', [$this->begin_time, $this->end_time])->get();
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
    }

    /**
     * 广告包收入计算
     */
    public function calArticle()
    {
        $aljbModel = new RechargeCreditLog();
        $shopOrdersModel = new ShopOrders();
        $credit_list = $aljbModel->where('money', '<', 50)->whereBetween('dateline', [$this->begin_timestamp, $this->end_timestamp])->get();
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
    }
}