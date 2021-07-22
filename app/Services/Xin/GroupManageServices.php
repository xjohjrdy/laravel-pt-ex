<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/27
 * Time: 16:13
 */

namespace App\Services\Xin;


use App\Entitys\App\AppActive;
use App\Services\Common\Time;

class GroupManageServices
{
    /*
     * 计算活跃值
     */
    public function calculateActiveValue($app_id)
    {
        $arr_lastFive_month_timestamp = time::getLastFiveMonthTimestamp();
        $obj_app_active = new AppActive();
        $obj_get_up_five_active_value = $obj_app_active->getUpFiveActiveValue($app_id, $arr_lastFive_month_timestamp);
        $arr_get_up_five_active_value = $obj_get_up_five_active_value->toArray();
        $sum_arr_context = 0;
        foreach ($arr_get_up_five_active_value as $json_context) {
            $arr_context = json_decode($json_context, true);
            $one_sum_arr_context = 0;
            foreach ($arr_context as $one_arr_context) {
                $one_sum_arr_context += $one_arr_context;
            }
            $sum_arr_context += $one_sum_arr_context;
        }
        $int_active_value_max_id = $obj_app_active->getActiveValueMaxId($app_id);
        $arr_max_id_data = $obj_app_active->find($int_active_value_max_id);
        if (empty($arr_max_id_data)) {
            return $sum_arr_context;
        }

        $arr_max_id_data = $arr_max_id_data->toArray();
        $arr_max_id_context = json_decode($arr_max_id_data['context'], true);
        $max_id_sum_context = 0;
        foreach ($arr_max_id_context as $max_id_context) {
            $max_id_sum_context += $max_id_context;
        }
        return $sum_arr_context + $max_id_sum_context;
    }

}