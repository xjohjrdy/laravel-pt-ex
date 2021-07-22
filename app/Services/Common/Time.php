<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/6
 * Time: 9:38
 */

namespace App\Services\Common;


class Time
{
    /*
     * 上月时间戳
     */
    static public function getLastMonthTimestamp()
    {
        $last_month_first = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
        $last_month_last = mktime(23, 59, 59, date('m') - 1, date('t', $last_month_first), date('Y'));
        return [$last_month_first, $last_month_last];

    }

    /*
     * 次月时间戳
     */
    static public function getNextMonthTimestamp()
    {
        $thismonth_start = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $thismonth_end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        return [
            $thismonth_start,
            $thismonth_end
        ];
    }

    /*
     * 当年的时间戳，1月1号的时间戳
     */
    public function getMonthTimestamp()
    {
        return strtotime(date('Y', time()) . '0101');

    }

    /*
     * 当月的时间戳1号的时间戳
     */
    public function getCurrentMonthTimestamp()
    {
        return strtotime(date('y-m-01', time()));

    }

    /*
   * 上5个月每月最后一天时间戳
   */
    static public function getLastFiveMonthTimestamp()
    {
        $date = new \DateTime();

        $arr_int_time = [];
        for ($i = 0; $i < 5; $i++) {
            $date->modify('last day of last month');
            $arr_int_time[] = strtotime($date->format('Y-m-d'));
        }
        return $arr_int_time;

    }

    /*
   * 今天时间戳
   */
    static public function getTodayTimestamp()
    {
        $last_month_first = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        return $last_month_first;

    }

}