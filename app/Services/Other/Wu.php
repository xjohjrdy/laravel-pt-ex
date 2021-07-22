<?php

namespace App\Services\Other;


use App\Entitys\Other\JdMaidOldOther;
use App\Entitys\Other\ManagerMaidAutoList;
use App\Entitys\Other\ManagerPretendMaid;
use App\Entitys\Other\PddMaidOldOther;
use App\Entitys\Other\TaobaoMaidOldOther;
use App\Entitys\Other\ThreeEleMaidOld;
use App\Entitys\Other\UserThreeUpMaid;
use App\Entitys\Other\CardMaid as CardMaidOther;

class Wu
{
    //


    public function getMonthData($app_id, $month_start, $month_end)
    {
        /**
         * 取自政航多个计算方法逻辑
         * 当前取的时间是本月
         * 如果有歧义，要换成上个月
         */
        $taobaoMaidOldModel = new TaobaoMaidOldOther();
        $jdMaidOldModel = new JdMaidOldOther();
        $pddMaidOldModel = new PddMaidOldOther();
        $eleMaidOldModel = new ThreeEleMaidOld();
        $shopMaidModel = new UserThreeUpMaid();
        $cardMaidModel = new CardMaidOther();
        $taobao = $taobaoMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $month_start, $month_end);
        $jd = $jdMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $month_start, $month_end);
        $pdd = $pddMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $month_start, $month_end);
        $ele = $eleMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $month_start, $month_end);
        $card = $cardMaidModel->getMaidMoneyForMonth($app_id, 2, $month_start, $month_end);
        $shop = $shopMaidModel->where(['app_id' => $app_id])->whereBetween('created_at', [$month_start, $month_end])->sum('money');

        $all_pretend_money_today = $taobao + $jd + $pdd + $ele + $card + $shop;

        return $all_pretend_money_today;
    }

    /**
     * 获取已经结束的数据
     */
    public function getMonthEndData($app_id, $month_start, $month_end)
    {

        $taobaoMaidOldModel = new TaobaoMaidOldOther();
        $jdMaidOldModel = new JdMaidOldOther();
        $pddMaidOldModel = new PddMaidOldOther();
        $eleMaidOldModel = new ThreeEleMaidOld();
        $shopMaidModel = new UserThreeUpMaid();
        $cardMaidModel = new CardMaidOther();
        $taobao = $taobaoMaidOldModel->getMaidMoneyForMonth($app_id, 1, 1, $month_start, $month_end);
        $jd = $jdMaidOldModel->getMaidMoneyForMonth($app_id, 1, 1, $month_start, $month_end);
        $pdd = $pddMaidOldModel->getMaidMoneyForMonth($app_id, 1, 1, $month_start, $month_end);
//            $mt = $mtMaidOldModel->getMaidMoneyForMonth($app_id, 1, 1, $month_start, $month_end);
        $ele = $eleMaidOldModel->getMaidMoneyForMonth($app_id, 1, 1, $month_start, $month_end);
        $card = $cardMaidModel->getMaidMoneyForMonth($app_id, 2, $month_start, $month_end);
        $shop = $shopMaidModel->where(['app_id' => $app_id])->whereBetween('created_at', [$month_start, $month_end])->sum('money');

        $all_pretend_money_today = $taobao + $jd + $pdd + $ele + $card + $shop;

        return $all_pretend_money_today;

    }

    /**
     * 不需要开始时间结束时间的计算
     */
    public function getAllData($app_id)
    {
        $taobaoMaidOldModel = new TaobaoMaidOldOther();
        $jdMaidOldModel = new JdMaidOldOther();
        $pddMaidOldModel = new PddMaidOldOther();
        $eleMaidOldModel = new ThreeEleMaidOld();
        $shopMaidModel = new UserThreeUpMaid();
        $cardMaidModel = new CardMaidOther();
        $taobao = $taobaoMaidOldModel->where('app_id', '=', $app_id)
            ->where('type', '=', 1)
            ->where('real', '=', 1)
            ->sum('maid_money');
        $jd = $jdMaidOldModel->where('app_id', '=', $app_id)
            ->where('type', '=', 1)
            ->where('real', '=', 1)
            ->sum('maid_money');
        $pdd = $pddMaidOldModel->where('app_id', '=', $app_id)
            ->where('type', '=', 1)
            ->where('real', '=', 1)
            ->sum('maid_money');
//            $mt = $mtMaidOldModel->getMaidMoneyForMonth($app_id, 1, 1, $month_start, $month_end);
        $ele = $eleMaidOldModel->where('app_id', '=', $app_id)
            ->where('type', '=', 1)
            ->where('real', '=', 1)
            ->sum('maid_money');
        $card = $cardMaidModel
            ->where('app_id', '=', $app_id)
            ->where('type', '=', 2)
            ->sum('maid_ptb');
        $shop = $shopMaidModel->where(['app_id' => $app_id])->sum('money');

        $all_pretend_money_today = $taobao + $jd + $pdd + $ele + ($card / 10) + $shop;

        return $all_pretend_money_today;
    }

    /**
     * @param $app_id
     * @param $type
     * @param $time_start_str
     * @param $time_end_str
     * @return mixed
     */
    public function getCompanyData($app_id, $type, $time_start_str, $time_end_str)
    {
        $manage_pretend_maid = new ManagerPretendMaid();
        return $manage_pretend_maid
            ->where('created_at', '>=', $time_start_str)
            ->where('created_at', '<=', $time_end_str)
            ->where([
                'status' => 0,
                'type' => $type,
                'app_id' => $app_id
            ])->sum('money');
    }


}
