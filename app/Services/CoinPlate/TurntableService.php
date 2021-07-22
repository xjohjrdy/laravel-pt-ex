<?php

namespace App\Services\CoinPlate;

use App\Entitys\App\CoinTurntableOrders;
use App\Entitys\App\CoinTurntablePrize;
use App\Services\Advertising\AdPackage;

class TurntableService
{

    /*
     * 随机生成n条中奖记录
     */
    public function rollWinPrizeInfo($n, $arr_turntable_prize_id)
    {
        //得到转盘奖品指定数据
        $coinTurntablePrize = new CoinTurntablePrize();
        $obj_prize_assign_info = $coinTurntablePrize->getTurntablePrizeAssignInfo($arr_turntable_prize_id);

        //根据概率生成奖品组
        $arr_prize = [];
        foreach ($obj_prize_assign_info as $v) {
            $arr_prize[] = array_fill(0, ceil($v->win_probability) + 1, $v->title);
        }
        $result = array_reduce($arr_prize, 'array_merge', array());

        $tel_arr = ['13', '14', '15', '16', '17', '18', '19'];
        for ($i = 0; $i < $n; $i++) {
            $tmp[] = $tel_arr[array_rand($tel_arr)] . mt_rand(0, 9) . '****' . mt_rand(1000, 9999) . ' 刚刚抽中了' . $result[array_rand($result)];
        }
        return array_unique($tmp);
    }

    /*
     * 转盘抽奖
     */
    function startLuckyDraw($proArr)
    {
        if (empty($proArr)) return false;

        $result = array();
        foreach ($proArr as $key => $val) {
            $arr[$key] = $val['probability'];
        }
        // 概率数组的总概率
        $proSum = array_sum($arr);
        asort($arr);
        // 概率数组循环
        foreach ($arr as $k => $v) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $v) {
                $result = $proArr[$k];
                break;
            } else {
                $proSum -= $v;
            }
        }
        return $result;
    }

    /*
     * 发放抽到的非实物奖品
     */
    public function virtualGoodGive($app_id, $award)
    {
        $adPackage = new AdPackage();
        $coinCommonService = new CoinCommonService($app_id);

        switch ($award['type']) {
            case 2:
                $coinCommonService->plusCoin($award['luck_draw_get'], CoinConst::COIN_PLUS_TASK_TURNTABLE, '大转盘抽奖获得');#加金币
                break;
            case 3:
                $adPackage->handleArticle($app_id, $award['luck_draw_get']);#加文章
                break;
            default:
        }

        return true;
    }
}
