<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CardEnterOrders;
use App\Entitys\App\CardMaid;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;
use App\Services\KaDuoFen\KaDuoFenServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 同步信用卡订单类，及订单分佣处理
 * Class SyncICardOrders
 * @package App\Console\Commands
 */
class SyncICardOrders2 extends Command
{
    protected $userAccount;
    protected $userModel;
    protected $cardMaidModel;
    protected $userMoneyService;
    protected $lcUserModel;
    protected $rateGroup = [ // 定义分佣比率数组
        'current' => [ // 下单用户
            'default' => 0.2, // 默认分佣比率
            'vip' => 0.4, // vip 比率
            'partner' => 0.4 // 合伙人 比率
        ],
        'redirect_parent' => [ // 直接上级
            'default' => 0.2,
            'vip' => 0.4,
            'partner' => 0.4
        ],
        'first_parent' => [ // 第一合伙人
            'default' => 0.1,
            'vip' => 0.1,
            'partner' => 0.1
        ],
        'second_parent' => [ // 第二合伙人
            'default' => 0.05,
            'vip' => 0.05,
            'partner' => 0.05
        ]
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SyncICardOrders2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '倍政';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->userAccount = new UserAccount();
        $this->userModel = new AdUserInfo();
        $this->cardMaidModel = new CardMaid();
        $this->userMoneyService = new UserMoney();
        $this->lcUserModel = new AppUserInfo();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 测试app_id 16944511
        $this->getOrdersList();
        $this->info('同步完成');
        // 测试分佣专用
//            $this->subCommission('1694511', 100, 100861694511);
    }

    public function getOrdersList()
    {

        $kaDuoFenServices = new KaDuoFenServices();
        $cardEnterModel = new CardEnterOrders();
//        $page_no = 1;
//        $page_size = 500;
//        $dataFlag = 2; // 1-根据创建时间查询,2-根据更新时间查询
//        $star_date = date('Y-m-d\TH:m:s\.000+0800', strtotime('-7 day -6 minute'));
//        $end_date = date('Y-m-d\TH:m:s\.000+0800');

//            $this->info('开始查询:' . $star_date . '-' . $end_date . ' pageNo:' . $page_no . ' size：' . $page_size);
        $response = json_decode($kaDuoFenServices->getOrderListByOpenId('7644896'), true);
        if (empty($response)) {
            $this->info('查询失败！相应结果为null');
            return;
        }
        if (@$response['result'] == true && @$response['code'] == 200) {
            $orders = $response['data'];
            $this->info('查询数量:' . count($orders));
            foreach ($orders as $order) {
//                $this->info(json_encode($order));
                try {
                    $enter_order = [];
                    $enter_order['app_id'] = empty($order['supplierCustNo']) ? '' : $order['supplierCustNo'];
                    $enter_order['record_id'] = empty($order['id']) ? '' : $order['id']; // 	信用卡办理记录ID
                    $enter_order['completionFlag'] = empty($order['completionFlag']) ? '' : $order['completionFlag']; // 完件标记，1代表完件
                    $enter_order['bankCode'] = empty($order['bankCode']) ? '' : $order['bankCode']; // 银行码
                    $enter_order['bankName'] = empty($order['bankName']) ? '' : $order['bankName']; // 银行名称
                    $enter_order['cardName'] = empty($order['cardName']) ? '' : $order['cardName']; // 信用卡名称
                    $enter_order['cardIcon'] = empty($order['cardIcon']) ? '' : $order['cardIcon']; // 信用卡图片
                    $enter_order['bankIcon'] = empty($order['bankIcon']) ? '' : $order['bankIcon']; // 银行图标
                    $enter_order['custNo'] = empty($order['custNo']) ? '' : $order['custNo']; // 客户号
                    $enter_order['custName'] = empty($order['custName']) ? '' : $order['custName']; // 申请人姓名，掩码
                    $enter_order['mobileNo'] = empty($order['mobileNo']) ? '' : $order['mobileNo']; // 申请人手机号，掩码显示
                    $enter_order['supplierCustNo'] = empty($order['supplierCustNo']) ? '' : $order['supplierCustNo']; // 合作伙伴客户号，用户唯一标识符,即userId
                    $enter_order['orgBonus'] = empty($order['orgBonus']) ? '' : $order['orgBonus']; // 合作伙伴订单返佣金额
                    $enter_order['bonusRate'] = empty($order['bonusRate']) ? '' : $order['bonusRate']; // 办卡人分佣比例，来自合作伙伴初始化，百分比显示
                    $enter_order['shareRate'] = empty($order['shareRate']) ? '' : $order['shareRate']; // 客户分享分佣比例，来自合作伙伴初始化，百分比显示
                    $enter_order['applyStatus'] = empty($order['applyStatus']) ? '' : $order['applyStatus']; // 订单状态
                    $enter_order['applyDatetime'] = empty($order['applyDatetime']) ? '' : $order['applyDatetime']; // 申请时间(时间戳)(历史字段，已废弃)
                    $enter_order['bonus'] = empty($order['bonus']) ? '' : $order['bonus']; // 办卡用户奖励金额(单位元) bonus =bonusRate/100 * orgBonus,如果存再/需要
                    $enter_order['supplierBonus'] = empty($order['supplierBonus']) ? '' : $order['supplierBonus']; // 供应商佣金奖励(历史字段，已废弃)
                    $enter_order['createDatetime'] = empty($order['createDatetime']) ? '' : $order['createDatetime']; // 创建时间,时间戳:1566905903591
                    $enter_order['updateDatetime'] = empty($order['updateDatetime']) ? '' : $order['updateDatetime']; // 更新时间,时间戳:1566905903591
                    $enter_order['cardProgressSwitch'] = empty($order['cardProgressSwitch']) ? '' : $order['cardProgressSwitch']; // 是否支持进度查1，支持;2，不支持
                    $enter_order['addProgressInfo'] = empty($order['addProgressInfo']) ? '' : $order['addProgressInfo']; // 是否需要补全查询信息
                    $enter_order['crawlId'] = empty($order['crawlId']) ? '' : $order['crawlId']; //进度查询id
                    $enter_order['issueFlag'] = empty($order['issueFlag']) ? '' : $order['issueFlag']; //Y-该订单给佣金，N-该订单无佣金
                    $enter_order['remittanceFlag'] = empty($order['remittanceFlag']) ? '' : $order['remittanceFlag']; //打款标识Y已经打过款,N-未打款(无特殊需求不需要使用)
                    $enter_order['newUserFlag'] = empty($order['newUserFlag']) ? '' : $order['newUserFlag']; //新户标志:1-新户，2-非新户 . 合作商无需关注
                    $enter_order['failReason'] = empty($order['failReason']) ? '' : $order['failReason']; //失效订单原因
                    $enter_order['cardId'] = empty($order['cardId']) ? '' : $order['cardId']; //信用卡类型ID
                    $enter_order['isOutStock'] = empty($order['isOutStock']) ? '' : $order['isOutStock']; //卡片是否下架;1上架，2下架
                    $res_order = $cardEnterModel->where(['record_id' => $order['id']])->first();
                    if (empty($res_order)) { // 空的话则新增
                        if (@$order['applyStatus'] == '003' && @$order['issueFlag'] == 'Y') { // 判断是否满足分佣条件
                            $this->subCommission2($enter_order['app_id'], $enter_order['orgBonus'], $enter_order['record_id']);
                            $enter_order['is_ptb'] = 1; // 设置分佣状态 1 已分佣  0 未分佣 --- 默认0
                        }
                        $cardEnterModel->create($enter_order);
                    } else {
                        if (@$res_order['is_ptb'] == 0 && @$order['applyStatus'] == '003' && @$order['issueFlag'] == 'Y') { // 判断是否满足分佣条件
                            // 符合分佣 调起分佣计算。
                            $this->subCommission2($enter_order['app_id'], $enter_order['orgBonus'], $enter_order['record_id']);
                            $enter_order['is_ptb'] = 1; // 设置分佣状态 1 已分佣  0 未分佣 --- 默认0
                        }
                        $cardEnterModel->where(['record_id' => $order['id']])->update($enter_order);
                    }
                } catch (\Exception $e) {
                    $this->info('异常：' . $e->getMessage());
                }
            }
//                if (count($orders) < $page_size) { // 如果返回结果小于分页数量则结束，反之则加载下一页
//                    break;
//                } else {
//                    $page_no++;
//                }

        } else {
            $this->info('查询失败！code: ' . $response['code'] . ' msg: ' . $response['msg']);
        }


    }

    /**
     * 分佣
     */
    public function subCommission2($userId, $money, $orderId)
    {
        $rate_key = 'default'; // 默认普通用户  --- default：普通用户  --- vip：vip用户 --- partner： 合伙人
        $userInfo = $this->userModel->where(['pt_id' => $userId])->first(['uid', 'pt_id', 'pt_pid', 'groupid']);
        if(empty($userInfo)){
            $parentInfo = $this->lcUserModel->getUserById($userId);
            $user = [
                'uid' => '',
                'pt_id' => $userId,
                'groupid' => 10,
                'pt_pid' => $parentInfo['parent_id'],
                'identify' => 'current'
            ];
        } else {
            $user = [
                'uid' => $userInfo['uid'],
                'pt_id' => $userInfo['pt_id'],
                'groupid' => $userInfo['groupid'],
                'pt_pid' => $userInfo['pt_pid'],
                'identify' => 'current'
            ];
        }

        if ($user['groupid'] == 23) { // vip用户
            $rate_key = 'vip';
        }
        if ($user['groupid'] == 24) { // 合伙人
            $rate_key = 'partner';
        }
        $real_rate = $this->rateGroup[$user['identify']][$rate_key]; // 获取最终要分佣的金额比率
        $commission = $money * $real_rate * 10; // 我的币数量
        $card_maid = [ // 信用卡分佣记录入库
            'from_app_id' => $user['pt_id'],
            'record_id' => $orderId,
            'group_id' => $user['groupid'],
            'maid_ptb' => $commission,
            'type' => 1,
            'app_id' => $user['pt_id'],
        ];
//        $this->addPtb($user['pt_id'], $commission); // 信用卡分佣我的币记录
        $commissionRMB = $money * $real_rate;
        $this->userMoneyService->plusCnyAndLog($user['pt_id'], $commissionRMB, '58');
        $this->cardMaidModel->create($card_maid);
        if(!empty($userInfo['pt_pid'])){
            $this->subCommissionParent($userInfo['pt_pid'], $money, $user['pt_id'], $orderId);
        }
    }


    /**
     * 上级分佣
     * @param $userId
     * @param $money
     * @param $orderId
     * @throws \Exception
     */
    public function subCommissionParent($userId, $money, $from, $orderId)
    {
        $rate_key = 'default'; // 默认普通用户  --- default：普通用户  --- vip：vip用户 --- partner： 合伙人
        $userInfo = $this->userModel->where(['pt_id' => $userId])->first(['uid', 'pt_id', 'pt_pid', 'groupid']);
        $user = [
            'uid' => $userInfo['uid'],
            'pt_id' => $userInfo['pt_id'],
            'groupid' => $userInfo['groupid'],
            'pt_pid' => $userInfo['pt_pid'],
            'identify' => 'redirect_parent'];
        if ($user['groupid'] == 23) { // vip用户
            $rate_key = 'vip';
        }
        if ($user['groupid'] == 24) { // 合伙人
            $rate_key = 'partner';
        }
        $real_rate = $this->rateGroup[$user['identify']][$rate_key]; // 获取最终要分佣的金额比率
        $commission = $money * $real_rate * 10; // 我的币数量
        $card_maid = [ // 信用卡分佣记录入库
            'from_app_id' => $from,
            'record_id' => $orderId,
            'group_id' => $user['groupid'],
            'maid_ptb' => $commission,
            'type' => 2,
            'app_id' => $user['pt_id'],
        ];
//        $this->addPtb($user['pt_id'], $commission); // 信用卡分佣我的币记录
        $commissionRMB = $money * $real_rate;
        $this->userMoneyService->plusCnyAndLog($user['pt_id'], $commissionRMB, '58');
        $this->cardMaidModel->create($card_maid);

    }
}
