<?php


namespace App\Services\Growth;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CardMaid;
use App\Entitys\App\EleMaidOld;
use App\Entitys\App\GrowthUserIncome;
use App\Entitys\App\JdMaidOld;
use App\Entitys\App\JsonConfig;
use App\Entitys\App\MtMaidOld;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\App\UserManagerLog;
use App\Exceptions\ApiException;

class UserIncome
{

    private $app_id = ''; // 用户ID
    private $begin_time = ''; // 起始日期
    private $end_time = '';
    private $begin_timestamp = ''; //起始时间轴
    private $end_timestamp = '';
    private $user = ''; // 根据appId获得用户信息
    private $ad_user = ''; // 根据appId获得用户信息
    private $group_id = 0; // 23 vip 用户即v1 24 合伙人 v2  小于23 普通用户 即 v0
    private $level = 0; // 用户等级=>1,无;2实习;3,转正;4,经理;5,董事;
    private $userModel = null;
    private $adUserInfo = null;
    private $is_current_month = false; // 是否适当当前月份主要用来改变提示文字title
    private $growthIncome = null;

    /**
     * UserIncome constructor.
     * @param string $app_id
     * @param string $timestamp 计算时间轴
     */
    public function __construct($app_id, $timestamp)
    {
        $this->app_id = $app_id;
        $this->begin_time = date('Y-m-01 00:00:00', $timestamp);
        $current_time = date('Y-m-01 00:00:00', time());
        $month = date('Y-m', $timestamp);
        if ($this->begin_time == $current_time) { // 判断是否事当前所在月份
            $this->is_current_month = true;
        }
        $this->end_time = date('Y-m-d 00:00:00', strtotime("$this->begin_time +1 month"));
        $this->begin_timestamp = strtotime($this->begin_time);
        $this->end_timestamp = strtotime($this->end_time);
        $this->userModel = new AppUserInfo();
        $this->user = $this->userModel->getUserById($app_id);
        if (empty($this->user)) {
            throw new ApiException('无效得app_id', 1001);
        }
        $this->level = $this->user['level'];
        $this->adUserInfo = new AdUserInfo();
        $this->ad_user = $this->adUserInfo->appToAdUserId($app_id);
        $this->group_id = $this->ad_user['groupid'];
        $incomeModel = new GrowthUserIncome();
        $this->growthIncome = $incomeModel->getUserIncome($app_id, $month);
    }

    /**
     * 1.v0和v0-经理只能看到，本月预估报销、本月预估佣金
     * 2.v1用户只能看到，本月专区销售收益（专区销售收入）、本月预估报销、本月预估佣金
     * 3.v2能看到，本月专区销售收益（专区销售收入、管理费）、本月预估报销、本月预估佣金、本月预估管理费
     * 4.v1-经理，v2-经理能看到，本月专区销售收益（专区销售收入、管理费、培训费）、本月预估报销、本月预估佣金、本月预估管理费
     * @return mixed
     */
    public function getCurrentMonthInfo()
    {
        $commission = $this->getAllCommission(); // 预估佣金
        $reimbursement = $this->getAllReimbursement(); // 预估报销
//        $sales = $this->getSalesRecord(); // 预估销售
        $arr = [];
        if ($this->group_id <= 10) { // 普通用户 v0
            $arr = [$reimbursement, $commission];
        }
        if ($this->group_id == 23) { //  v1
            $arr = [$reimbursement, $commission];
        }
        if ($this->group_id == 24 || $this->level >= 4) { // v2
            $serviceFee = $this->getTeamServiceFee(); // 预估管理费
            $arr = [$reimbursement, $commission, $serviceFee];
        }


        foreach ($arr as $key => $item) {
            if ($this->is_current_month) {
                unset($arr[$key]['title2']);
            } else {
                $arr[$key]['title'] = $arr[$key]['title2'];
                unset($arr[$key]['title2']);
            }
            foreach ($arr[$key]['items'] as $key2 => $item2) {
                if ($this->is_current_month) {
                    unset($arr[$key]['items'][$key2]['title2']);
                } else {
                    $arr[$key]['items'][$key2]['title'] = $arr[$key]['items'][$key2]['title2'];
                    unset($arr[$key]['items'][$key2]['title2']);
                }
            }
        }
        return $arr;
    }

    private function setItem($t1, $t2, $value)
    {
        return [
            'title' => $t1,
            'title2' => $t2,
            'value' => $value,
        ];
    }

    /**
     * 获取销售记录
     * 1.v0和v0-经理只能看到，本月预估报销、本月预估佣金
     * 2.v1用户只能看到，本月专区销售收益（专区销售收入）
     * 3.v2能看到，本月专区销售收益（专区销售收入、管理费）
     * 4.v1-经理，v2-经理能看到，本月专区销售收益（专区销售收入、管理费、培训费）
     * @return mixed
     */
    public function getSalesRecord()
    {
        $records = [
            'title' => '本月专区销售收益',
            'title2' => '专区销售收益记录',
        ];
        $value1 = empty($this->growthIncome['growth_sale_one']) ? '0.00' : $this->growthIncome['growth_sale_one'];
        $value2 = empty($this->growthIncome['growth_sale_two']) ? '0.00' : $this->growthIncome['growth_sale_two'];
        $userManage = new UserManagerLog();
        $value3 = $userManage->getMaidMoneyForMonth($this->group_id, $this->begin_time, $this->end_time);
        $value3 = empty($value1) ? '0.00' : $value3;
        $item1 = $this->setItem('专区销售收入', '专区销售收入', $value1 . '元');
        $item2 = $this->setItem('专区管理费', '专区管理费', $value2 . '元');
        $item3 = $this->setItem('专区培训费', '专区培训费', $value3 . '元');
        if ($this->group_id <= 10) { // v0
            $records['items'] = [];
        }

        if ($this->group_id == 23) { // v1
            if ($this->level == 4) {
                $records['items'] = [
                    $item1, $item2, $item3
                ];
            } else {
                $records['items'] = [
                    $item1
                ];
            }
        }

        if ($this->group_id == 24) { // v2
            if ($this->level == 4) {
                $records['items'] = [
                    $item1, $item2, $item3
                ];
            } else {
                $records['items'] = [
                    $item1, $item2
                ];
            }
        }
        return $records;
    }

    /**
     * 获取所有渠道报销记录
     */
    public function getAllReimbursement()
    {
        $records = [
            'title' => '本月预估报销',
            'title2' => '本月报销记录',
        ];
        if ($this->is_current_month) {
            $records['sub_title'] = '（次月可提现）';
        }
        $taobaoMaid = new TaobaoMaidOld();
        $cardMaid = new CardMaid();
        $jdMaid = new JdMaidOld();
        $pddMaid = new PddMaidOld();
        $eleMaidOldModel = new EleMaidOld();
        $mtMaidOldModel = new MtMaidOld();
        $value1 = ''; // taobao
        $value2 = ''; // jd
        $value3 = ''; // pdd
        $value4 = $cardMaid->getMaidMoneyForMonth($this->app_id, 1, $this->begin_time, $this->end_time);
        $value5 = ''; // 饿了么
        $mt_value = ''; // 美团
        if ($this->is_current_month) {
            $value1 = $taobaoMaid->where('app_id', '=', $this->app_id)
                ->where('type', '=', 2)
                ->where('created_at', '>=', $this->begin_time)
                ->where('created_at', '<=', $this->end_time)
                ->sum('maid_money');
            $value2 = $jdMaid->where('app_id', '=', $this->app_id)
                ->where('type', '=', 2)
                ->where('created_at', '>=', $this->begin_time)
                ->where('created_at', '<=', $this->end_time)
                ->sum('maid_money');
            $value3 = $pddMaid->where('app_id', '=', $this->app_id)
                ->where('type', '=', 2)
                ->where('created_at', '>=', $this->begin_time)
                ->where('created_at', '<=', $this->end_time)
                ->sum('maid_money');
            $value5 = $eleMaidOldModel->where('app_id', '=', $this->app_id)
                ->where('type', '=', 2)
                ->where('created_at', '>=', $this->begin_time)
                ->where('created_at', '<=', $this->end_time)
                ->sum('maid_money');
            $mt_value = $mtMaidOldModel->where('app_id', '=', $this->app_id)
                ->where('type', '=', 2)
                ->where('created_at', '>=', $this->begin_time)
                ->where('created_at', '<=', $this->end_time)
                ->sum('maid_money');
        } else {
            $value1 = $taobaoMaid->getMaidMoneyForMonth($this->app_id, 2, 1, $this->begin_time, $this->end_time);
            $value2 = $jdMaid->getMaidMoneyForMonth($this->app_id, 2, 1, $this->begin_time, $this->end_time);
            $value3 = $pddMaid->getMaidMoneyForMonth($this->app_id, 2, 1, $this->begin_time, $this->end_time);
            $value5 = $eleMaidOldModel->getMaidMoneyForMonth($this->app_id, 2, 1, $this->begin_time, $this->end_time);
            $mt_value = $mtMaidOldModel->getMaidMoneyForMonth($this->app_id, 2, 1, $this->begin_time, $this->end_time);
        }
        $value1 = empty($value1) ? '0.00' : $value1;
        $value2 = empty($value2) ? '0.00' : $value2;
        $value3 = empty($value3) ? '0.00' : $value3;
        $value4 = empty($value4) ? '0.00' : $value4;
        $value5 = empty($value5) ? '0.00' : $value5;
        $mt_value = empty($mt_value) ? '0.00' : $mt_value;
        $item1 = $this->setItem('淘宝预估报销', '淘宝报销', $value1 . '元');
        $item2 = $this->setItem('京东预估报销', '京东报销', $value2 . '元');
        $item3 = $this->setItem('拼多多预估报销', '拼多多报销', $value3 . '元');
        $item4 = $this->setItem('信用卡预估返现', '信用卡返现', $value4 . '元');
        $item5 = $this->setItem('饿了么预估报销', '饿了么报销', $value5 . '元');
        $mt_item5 = $this->setItem('美团预估报销', '美团报销', $mt_value . '元');
        if (true) {
            $records['items'] = [
                $item1, $item2, $item3, $item4, $item5, $mt_item5
            ];
        }
        return $records;
    }

    /**
     * 获取所有渠道佣金
     */
    public function getAllCommission()
    {
        $records = [
            'title' => '本月预估佣金',
            'title2' => '本月佣金记录',
        ];
        $value1 = empty($this->growthIncome['growth_taobao_one']) ? '0.00' : $this->growthIncome['growth_taobao_one'];
        $value2 = empty($this->growthIncome['growth_jd_one']) ? '0.00' : $this->growthIncome['growth_jd_one'];
        $value3 = empty($this->growthIncome['growth_pdd_one']) ? '0.00' : $this->growthIncome['growth_pdd_one'];
        $value4 = empty($this->growthIncome['growth_card_one']) ? '0.00' : $this->growthIncome['growth_card_one'];
        $value5 = empty($this->growthIncome['growth_shop_one']) ? '0.00' : $this->growthIncome['growth_shop_one'];
        $value6 = empty($this->growthIncome['growth_circle_one']) ? '0.00' : $this->growthIncome['growth_circle_one'];
        $value7 = empty($this->growthIncome['growth_article_one']) ? '0.00' : $this->growthIncome['growth_article_one'];
        $eleMaidOldModel = new EleMaidOld();
        $mtMaidOldModel = new MtMaidOld();

        if($this->is_current_month){
            $ele_value = $eleMaidOldModel->where('app_id', '=', $this->app_id)
                ->where('type', '=', 1)
                ->where('created_at', '>=', $this->begin_time)
                ->where('created_at', '<=', $this->end_time)
                ->sum('maid_money');
            $mt_value = $mtMaidOldModel->where('app_id', '=', $this->app_id)
                ->where('type', '=', 1)
                ->where('created_at', '>=', $this->begin_time)
                ->where('created_at', '<=', $this->end_time)
                ->sum('maid_money');
        } else {
            $ele_value = $eleMaidOldModel->getMaidMoneyForMonth($this->app_id, 1, 1, $this->begin_time, $this->end_time);
            $mt_value = $mtMaidOldModel->getMaidMoneyForMonth($this->app_id, 1, 1, $this->begin_time, $this->end_time);
        }
        $ele_value = empty($ele_value) ? '0.00' : $ele_value;
        $mt_value = empty($mt_value) ? '0.00' : $mt_value;
        $item1 = $this->setItem('淘宝预估佣金', '淘宝佣金', $value1 . '元');
        $item2 = $this->setItem('京东预估佣金', '京东佣金', $value2 . '元');
        $item3 = $this->setItem('拼多多预估佣金', '拼多多佣金', $value3 . '元');
        $item4 = $this->setItem('信用卡佣金', '信用卡佣金', $value4 . '元');
        $item5 = $this->setItem('爆款商城预估佣金', '爆款商城佣金', $value5 . '元');
        $item6 = $this->setItem('圈子收入', '圈子收入', $value6 . '元');
        $item7 = $this->setItem('广告包收入', '广告包收入', $value7 . '元');
        $ele_item =  $this->setItem('饿了么预估佣金', '饿了么佣金', $ele_value . '元');
        $mt_item =  $this->setItem('美团预估佣金', '美团佣金', $mt_value . '元');
        if ($this->group_id <= 10) { // v0
            $records['items'] = [
                $item1, $item2, $item3, $item4, $item5, $item6, $ele_item, $mt_item
            ];
        }
        if ($this->group_id == 23) { // v1
            $records['items'] = [
                $item1, $item2, $item3, $item4, $item5, $item6, $item7, $ele_item, $mt_item
            ];
        }

        if ($this->group_id == 24) { // v2
            $records['items'] = [
                $item1, $item2, $item3, $item4, $item5, $item6, $item7, $ele_item, $mt_item
            ];
        }
        return $records;
    }

    /**
     * 获取所有渠道团队管理费
     */
    public function getTeamServiceFee()
    {
        $records = [
            'title' => '', //本月预估管理费
            'title2' => '购物管理费记录',
        ];
        $value1 = empty($this->growthIncome['growth_taobao_two']) ? '0.00' : $this->growthIncome['growth_taobao_two'];
        $value2 = empty($this->growthIncome['growth_jd_two']) ? '0.00' : $this->growthIncome['growth_jd_two'];
        $value3 = empty($this->growthIncome['growth_pdd_two']) ? '0.00' : $this->growthIncome['growth_pdd_two'];
        $value4 = empty($this->growthIncome['growth_card_two']) ? '0.00' : $this->growthIncome['growth_card_two'];
        $value5 = empty($this->growthIncome['growth_shop_two']) ? '0.00' : $this->growthIncome['growth_shop_two'];
        $value6 = empty($this->growthIncome['growth_circle_two']) ? '0.00' : $this->growthIncome['growth_circle_two'];
        $value7 = empty($this->growthIncome['growth_article_two']) ? '0.00' : $this->growthIncome['growth_article_two'];
        $item1 = $this->setItem('淘宝预估管理费', '淘宝管理费', $value1 . '元');
        $item2 = $this->setItem('京东预估管理费', '京东管理费', $value2 . '元');
        $item3 = $this->setItem('拼多多预估管理费', '拼多多管理费', $value3 . '元');
        $item4 = $this->setItem('信用卡管理费', '信用卡管理费', $value4 . '元');
        $item5 = $this->setItem('爆款商城预估管理费', '爆款商城管理费', $value5 . '元');
        $item6 = $this->setItem('圈子管理费', '圈子管理费', $value6 . '元');
        $item7 = $this->setItem('广告包管理费', '广告包管理费', $value7 . '元');
        if ($this->group_id <= 10) { // v0
            $records['items'] = [
//                $item1, $item2, $item3, $item4, $item5, $item6
            ];
        }
        if ($this->group_id == 23) { // v1
            $records['items'] = [
//                $item1, $item2, $item3, $item4, $item5, $item6, $item7
            ];
        }

        if ($this->group_id == 24) { // v2
            $records['items'] = [
//                $item1, $item2, $item3, $item4, $item5, $item6, $item7
            ];
        }
        if($this->is_current_month){
            $obj_config = new JsonConfig();
            $arr_config_data = $obj_config->getValue('manage_config');
            if($arr_config_data != false){
                $records['manage'] = $arr_config_data;
            }
        }

        return $records;
    }
}