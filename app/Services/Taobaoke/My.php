<?php

namespace App\Services\Taobaoke;


use Illuminate\Support\Facades\DB;

class My
{
    function teamNextMonthCash($user_id)
    {
        $current_month = [
            mktime(0, 0, 0, date('m'), 1, date('Y')),
            mktime(23, 59, 59, date('m'), date('t'), date('Y'))
        ];
        $sql = "
        select sum(uo.cashback_amount) as res from `lc_user_order` as uo
        INNER JOIN (
        SELECT
                        *
        FROM
                        lc_user
        WHERE
                        id = {$user_id}
        UNION
        SELECT
                        t2.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
        WHERE
                        t1.id = {$user_id}
        UNION
        SELECT
                        t3.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
        WHERE
                        t1.id = {$user_id}
        UNION
        SELECT
                        t4.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                        INNER JOIN lc_user t4 ON t3.id = t4.parent_id
        WHERE
                        t1.id = {$user_id}
        ) as tt2
        on uo.user_id = tt2.id
and uo.status in (3,4) and uo.confirm_time between {$current_month[0]} and {$current_month[1]}
        ";
        $res = DB::connection("app38")->select($sql);
        return (float)$res[0]->res;
    }
    function teamLastMonthOrderAmount($user_id)
    {

        $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
        $end = mktime(23, 59, 59, date('m') - 1, date('t', $begin), date('Y'));

        $last_month = [$begin, $end];

        $sql = "
        select sum(uo.cashback_amount) as res from `lc_user_order` as uo
        INNER JOIN (
        SELECT
                        *
        FROM
                        lc_user
        WHERE
                        id = {$user_id}
        UNION
        SELECT
                        t2.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
        WHERE
                        t1.id = {$user_id}
        UNION
        SELECT
                        t3.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
        WHERE
                        t1.id = {$user_id}
        UNION
        SELECT
                        t4.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                        INNER JOIN lc_user t4 ON t3.id = t4.parent_id
        WHERE
                        t1.id = {$user_id}
        ) as tt2
        on uo.user_id = tt2.id
and uo.status in (3,4) and uo.confirm_time between {$last_month[0]} and {$last_month[1]}
        ";
        $res = DB::connection("app38")->select($sql);
        return (float)$res[0]->res;
    }

}
