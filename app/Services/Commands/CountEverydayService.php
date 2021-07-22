<?php

namespace App\Services\Commands;

use App\Entitys\App\ActiveCount;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\WechatInfo;
use Illuminate\Support\Facades\DB;

class CountEverydayService
{
    /*
     * 如果是一号，返回上月完整时间，如果不是一号，返回本月完整时间   list($begin,$end)
     * 返回格式为时间戳
     */
    public function getLeadTimeStamp()
    {
        if (date('j') == 1) {
            $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
            $end = mktime(23, 59, 59, date('m') - 1, date('t', $begin), date('Y'));
        } else {
            $begin = mktime(0, 0, 0, date('m'), 1, date('Y'));
            $end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        }
        return [$begin, $end];
    }

    /*
     * 如果是一号，返回上月完整时间，如果不是一号，返回本月完整时间   list($begin,$end)
     * 返回格式为字符串时间
     */
    public function getLeadTimeString()
    {
        if (date('j') == 1) {
            $last = strtotime('-1 month');
            $begin = date('Y-m-01 00:00:00', $last);
            $end = date('Y-m-t 23:59:59', $last);
        } else {
            $begin = date("Y-m-01 00:00:00");
            $end = date('Y-m-t 23:59:59');
        }
        return [$begin, $end];
    }
    public function countPassUser()
    {
        $model_user = new AppUserInfo();
        $countNumber = $model_user
            ->where('status', 1)
            ->where('level', '>=', 2)
            ->count();
        return $countNumber;
    }
    public function countPassUserL1()
    {

        $countNumber = DB::connection('app38')
            ->select('
SELECT
	count(id) as num
FROM
	lc_user tt1
INNER JOIN (SELECT parent_id FROM lc_user WHERE parent_id <> 0 AND status = 1 GROUP BY parent_id HAVING COUNT(*) >=10) AS tt2
ON 
	tt1.id = tt2.parent_id
WHERE
	tt1.level = 1
	AND
tt1.STATUS = 1');

        if (empty($countNumber)) {
            return 0;
        }

        return $countNumber[0]->num;
    }
    public function getPassUserInfo($page, $limit)
    {
        $model_user = new AppUserInfo();
        $userInfo = $model_user
            ->where('status', 1)
            ->where('level', '>=', 2)
            ->orderBy('id')
            ->offset($page)
            ->limit($limit)
            ->get(['id', 'sign_active_value', 'append_active_value']);
        return $userInfo;
    }
    public function getPassUserInfoL1($page, $limit)
    {
        $userInfo = DB::connection('app38')
            ->select('
SELECT
	id, sign_active_value, append_active_value
FROM
	lc_user tt1
INNER JOIN (SELECT parent_id FROM lc_user WHERE parent_id <> 0 AND status = 1 GROUP BY parent_id HAVING COUNT(*) >=10) AS tt2
ON 
	tt1.id = tt2.parent_id
WHERE
	tt1.level = 1
	AND
tt1.STATUS = 1
LIMIT ?,?;', [$page, $limit]);
        return $userInfo;
    }
    public function isValid($super_id)
    {
        $obj_user = new AppUserInfo();
        $arr_id = $obj_user->where('parent_id', $super_id)->pluck('id');
        $obj_wechat_info = new WechatInfo();
        $count_wechat = $obj_wechat_info->whereIn('app_id', $arr_id)->count();
        return $count_wechat >= 10 ? false : true;
    }

    /*
     * 清空指定类型的库积分
     */
    public function clearActive($type)
    {
        $model_active_count = new ActiveCount();
        try {
            $res = $model_active_count
                ->where('type', $type)
                ->update(['value' => 0]);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /*
     * 添加一条用户积分数据
     */
    public function addUserActive($app_id, $type, $active_value)
    {
        $update_time = time();
        try {
            DB::connection('app38')
                ->select("
                    INSERT INTO lc_active_count ( pt_id, type,update_time, value )
                    VALUES
                        ({$app_id},{$type},{$update_time},{$active_value})
                    ON DUPLICATE KEY UPDATE 
                    VALUE =  {$active_value},
                    update_time = {$update_time}
                    ");
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /*
     * 统计团队人数，（直推+自己）
     * 必须是激活用户才纳入团队
     */
    public function getGroupNumber($app_id)
    {

        $model_user = new AppUserInfo();
        $count_user_number = $model_user
            ->where('status', 1)
            ->where('parent_id', $app_id)
            ->count();

        return $count_user_number + 1;
    }

    /*
     * 统计昨日签到过的人数(包括自己)
     */
    public function getSignNumber($app_id, $yesterday)
    {
        $count_sign_obj = DB::connection('app38')
            ->select("
                SELECT
                        count( * )  as ct1
                FROM
                        lc_sign_log tt1
                        INNER JOIN (
                            SELECT 
                                    *
                            FROM
                                    lc_user
                            WHERE
                                    id = {$app_id}
                            UNION
                            SELECT
                                    * 
                            FROM
                                    lc_user
                            WHERE
                                    parent_id = {$app_id}
                            AND
                                    status = 1 	
                        ) AS tt2 ON tt1.user_id = tt2.id 
                WHERE
                        tt1.date = '{$yesterday}'
            ");

        if (empty($count_sign_obj)) {
            return false;
        }
        return $count_sign_obj[0]->ct1;
    }

    /*
     * 统计用户团队的报销金额
     */
    public function getGroupOrderAccount($app_id, $begin_time_stamp, $end_time_stamp)
    {
        $group_order_account = DB::connection('app38')
            ->select("
                        SELECT SUM(tt1.cashback_amount)as amount
                        FROM lc_user_order as tt1
                        INNER JOIN (
                            SELECT 
                                    *
                            FROM
                                    lc_user
                            WHERE
                                    id = {$app_id}
                            UNION ALL 
                            SELECT
                                    * 
                            FROM
                                    lc_user
                            WHERE
                                    parent_id = {$app_id}
                        ) as tt2
                        on tt1.user_id = tt2.id 
                        WHERE tt1.status in (3,4,9)
                        AND tt2.status = 1
                        and tt1.confirm_time between {$begin_time_stamp} and {$end_time_stamp} 
                    ");

        if (empty($group_order_account)) {
            return false;
        }
        return $group_order_account[0]->amount;
    }

    /*
     * 统计用户团队的报销金额(新表)
     */
    public function getGroupOrderAccountNew($app_id, $begin_time_stamp, $end_time_stamp)
    {
        $group_order_account = DB::connection('app38')
            ->select("
                        SELECT SUM(tt1.cashback_amount)as amount
                        FROM lc_user_order_new as tt1
                        INNER JOIN (
                            SELECT 
                                    *
                            FROM
                                    lc_user
                            WHERE
                                    id = {$app_id}
                            UNION ALL 
                            SELECT
                                    * 
                            FROM
                                    lc_user
                            WHERE
                                    parent_id = {$app_id}
                        ) as tt2
                        on tt1.user_id = tt2.id 
                        WHERE tt1.status in (3,4,9)
                        AND tt2.status = 1
                        and tt1.confirm_time between {$begin_time_stamp} and {$end_time_stamp} 
                    ");

        if (empty($group_order_account)) {
            return false;
        }
        return $group_order_account[0]->amount;
    }

    /*
     * 统计用户团队拼多多的报销金额
     */
    public function getGroupOrderAccountPdd($app_id, $begin_time_stamp, $end_time_stamp)
    {
        $group_order_account = DB::connection('app38')
            ->select("
                        SELECT SUM(tt1.promotion_amount)as amount
                        FROM lc_pdd_enter_orders as tt1
                        INNER JOIN (
                            SELECT 
                                    *
                            FROM
                                    lc_user
                            WHERE
                                    id = {$app_id}
                            UNION ALL 
                            SELECT
                                    * 
                            FROM
                                    lc_user
                            WHERE
                                    parent_id = {$app_id}
                        ) as tt2
                        on tt1.app_id = tt2.id 
                        WHERE tt1.order_status in (2,3,5)
                        AND tt2.status = 1
                        and tt1.created_at between \"{$begin_time_stamp}\" and \"{$end_time_stamp}\" 
                    ");

        if (empty($group_order_account)) {
            return false;
        }
        return $group_order_account[0]->amount;
    }

    /*
     * 统计用户团队京东的报销金额
     */
    public function getGroupOrderAccountJd($app_id, $begin_time_stamp, $end_time_stamp)
    {
        $group_order_account = DB::connection('app38')
            ->select("
                        SELECT SUM(tt1.actualFee)as amount
                        FROM lc_jd_enter_orders as tt1
                        INNER JOIN (
                            SELECT 
                                    *
                            FROM
                                    lc_user
                            WHERE
                                    id = {$app_id}
                            UNION ALL 
                            SELECT
                                    * 
                            FROM
                                    lc_user
                            WHERE
                                    parent_id = {$app_id}
                        ) as tt2
                        on tt1.app_id = tt2.id 
                        WHERE tt1.validCode in (17,18)
                        AND tt2.status = 1
                        and tt1.finishTime between {$begin_time_stamp} and {$end_time_stamp} 
                    ");

        if (empty($group_order_account)) {
            return false;
        }
        return $group_order_account[0]->amount;
    }



    /*
     * 指定时间区间，直推新用户人数
     */
    public function getRegisterNumber($app_id, $begin_time_stamp, $end_time_stamp)
    {
        $model_user = new AppUserInfo();
        $count_register_number = $model_user
            ->where('parent_id', $app_id)
            ->whereBetween('create_time', [$begin_time_stamp, $end_time_stamp])
            ->count();
        return $count_register_number;
    }

    /*
     * 得到团队（自己+直属下级）当月的购物订单数
     */
    public function getMonetary($app_id, $begin_time_string, $end_time_string)
    {

        $count_register_number = DB::connection('app38')
            ->select("
                        SELECT  SUM(tt1.ptb_number/10+tt1.real_price) as money
                        FROM lc_shop_orders as tt1
                        INNER JOIN (
                                SELECT 
                                        *
                                FROM
                                        lc_user
                                WHERE
                                        id = {$app_id}
                                UNION
                                SELECT
                                        * 
                                FROM
                                        lc_user
                                WHERE
                                        parent_id = {$app_id}
                        ) as tt2
                        on tt1.app_id = tt2.id 
                        WHERE tt1.status = 3
                        AND tt2.status = 1
						AND tt1.price <> 800
                        AND tt1.updated_at between \"{$begin_time_string}\" AND \"{$end_time_string}\"
                        ");

        if (empty($count_register_number)) {
            return false;
        }
        return $count_register_number[0]->money;
    }

    /*
     * 通过app_id 查询 指定时间，开通代理商人数，
     */
    public function getOrderActive($app_id, $begin_time_stamp, $end_time_stamp)
    {
        $count_order_number = DB::connection('a1191125678')
            ->select("
            SELECT COUNT(*) as total
            FROM pre_aljbgp_order as tt1
            INNER JOIN (
                            SELECT 
                                        *
                            FROM
                                        pre_common_member
                            WHERE
                                        pt_id = {$app_id}
                            UNION
                            SELECT
                                        * 
                            FROM
                                        pre_common_member
                            WHERE
                                        pt_pid = {$app_id}
            ) as tt2
            on tt1.uid = tt2.uid 
            WHERE tt1.status = 2
            AND	tt1.groupid = 23
            AND tt1.submitdate between {$begin_time_stamp} AND {$end_time_stamp}
            ");

        if (empty($count_order_number)) {
            return false;
        }
        return $count_order_number[0]->total;
    }

    /*
     * 通过app_id 查询 指定时间，葡萄通讯购买订单数量
     */
    public function getVoIpNumber($app_id, $begin_time_string, $end_time_string)
    {
        $count_voip_number = DB::connection('a1191125678')
            ->select("
            SELECT COUNT(*) as total
            FROM pre_voip_money_order as tt1
            INNER JOIN (
                            SELECT 
                                        *
                            FROM
                                        pre_common_member
                            WHERE
                                        pt_id = {$app_id}
                            UNION
                            SELECT
                                        * 
                            FROM
                                        pre_common_member
                            WHERE
                                        pt_pid = {$app_id}
            ) as tt2
            on tt1.app_id = tt2.pt_id 
            WHERE tt1.status = 1
            AND tt1.created_at between \"{$begin_time_string}\" AND \"{$end_time_string}\"
            ");

        if (empty($count_voip_number)) {
            return false;
        }
        return $count_voip_number[0]->total;
    }


    /*
     * 通过app_id 查询 指定时间，圈子购买订单数量（不包括竞价）
     */
    public function getCircleNumber($app_id, $begin_time_string, $end_time_string)
    {
        $count_circle_number = DB::connection('app38')
            ->select("
                        SELECT COUNT(*) as total
                        FROM lc_circle_ring_add_order as tt1
                        INNER JOIN (
                                SELECT 
                                        *
                                FROM
                                        lc_user
                                WHERE
                                        id = {$app_id}
                                UNION
                                SELECT
                                        * 
                                FROM
                                        lc_user
                                WHERE
                                        parent_id = {$app_id}
                        ) as tt2
                        on tt1.app_id = tt2.id 
                        WHERE tt1.status = 1
                        AND tt2.status = 1
						AND tt1.money = 600 
                        AND tt1.created_at between \"{$begin_time_string}\" AND \"{$end_time_string}\"
                        ");

        if (empty($count_circle_number)) {
            return false;
        }
        return $count_circle_number[0]->total;

    }


    /*
     * 查询某类型的活跃值，用户当天是否已经被统计
     */
    public function isProcessed($app_id, $type)
    {
        $obj_active_count = new ActiveCount();
        $obj_active_info = $obj_active_count->where(['pt_id' => $app_id, 'type' => $type])->first();
        if (empty($obj_active_info)) {
            return false;
        }

        $day_time = strtotime(date('Y-m-d'));
        if ($obj_active_info->update_time > $day_time) {
            return true;
        }

        return false;
    }


    /**
     * @param $app_id
     * @return bool
     */
    public function again($app_id)
    {
        //初始化一些参数
        list($begin_time_stamp, $end_time_stamp) = $this->getLeadTimeStamp();//得到当月的最大日期间隔，如果是一号则得到上月的日期间隔(时间戳类型)
        list($begin_time_string, $end_time_string) = $this->getLeadTimeString();//得到当月的最大日期间隔，如果是一号则得到上月的日期间隔(字符串时间类型)

        $yesterday = date('Y-m-d', strtotime('yesterday'));//昨天的日期 字符串时间类型

        $single_id = AppUserInfo::find($app_id);
        if (empty($single_id) || $single_id->status != 1) {
            return false;
        }

        $num_app_id = $single_id->id;
        $num_sign_active_value = $single_id->sign_active_value;//得到用户本月签到累计活跃值
        $num_append_active_value = $single_id->append_active_value;//得到本月附加活跃值

        # 测试1694511用户，除了该用户其他用户先全部跳过
//                if ($num_app_id > 1000) {
//                    die;
//                    continue;
//                }


        /**********= 统计团队签到活跃度 type:3 =**********/
        //开始测试
        //测试通过
        $count_group_number = $this->getGroupNumber($num_app_id);//统计团队人数(自己和直推的人数) 最低 1
        $count_sign_number = $this->getSignNumber($num_app_id, $yesterday);//得到团队已经签到的用户人数 最低0
        $headcount = $count_group_number > 50 ? $count_group_number : 50;//团队总人数如果小于50
        $active_value = round($count_sign_number / $headcount, 2);
        $active_value += $num_sign_active_value;//用户当前活跃度+用户累加活跃度
        $this->addUserActive($num_app_id, 3, $active_value);//插入类型为3(团队签到活跃度)的活跃度记录


        /**********= 统计团队报销活跃度 type:4 =**********/
        //开始测试
        //测试通过
        $group_order_account_number = $this->getGroupOrderAccount($num_app_id, $begin_time_stamp, $end_time_stamp);//得到团队全部报销金额，时间区间为时间戳
        $active_value = round($group_order_account_number / 10, 2);

        //增加新活跃度统计功能。
        $group_order_account_number_new = $this->getGroupOrderAccountNew($num_app_id, $begin_time_stamp, $end_time_stamp);//得到团队全部报销金额，时间区间为时间戳，新表6.1新加入的
        $active_value_new = round($group_order_account_number_new / 10, 2);

        //拼多多活跃度统计
        $pdd_group_order_account_number = $this->getGroupOrderAccountPdd($num_app_id, $begin_time_string, $end_time_string);//得到团队全部拼多多报销金额，时间区间为时间
        $pdd_active_value = round($pdd_group_order_account_number / 1000, 2);

        //京东活跃度统计
        $jd_group_order_account_number = $this->getGroupOrderAccountJd($num_app_id, $begin_time_stamp . '000', $end_time_stamp . '000');//得到团队全部京东报销金额，时间区间为时间戳
        $jd_active_value = round($jd_group_order_account_number / 10, 2);

        $this->addUserActive($num_app_id, 4, $active_value + $active_value_new + $pdd_active_value + $jd_active_value);


        /**********= 统计团队拉人活跃度 type:5 =**********/
        //开始测试
        //测试通过
        $register_number = $this->getRegisterNumber($num_app_id, $begin_time_stamp, $end_time_stamp);//得到当月推广注册的用户个数，时间区间为时间戳
        $active_value = $register_number;
        $this->addUserActive($num_app_id, 5, $active_value);

        /**********= 统计葡萄商城活跃度 type:6 =**********/
        //开始测试
        //测试通过 //修改成分后未测试
        $monetary = $this->getMonetary($num_app_id, $begin_time_string, $end_time_string);//得到团队当月消费的金额，时间区间为字符串时间
//                $active_value = $monetary;
        $active_value = round($monetary * 0.05, 2);//每消费一元增加0.05分活跃度
        $this->addUserActive($num_app_id, 6, $active_value);

        /**********= 统计开通代理商订单 type:0 (此处是根据广告联盟的上下级关系) =**********/
        //开始测试
        //测试通过
        $agent_order_number = $this->getOrderActive($num_app_id, $begin_time_stamp, $end_time_stamp);//得到团队当月开通代理商的人数，时间区间为时间戳
        $active_value = $agent_order_number * 2;
        $this->addUserActive($num_app_id, 0, $active_value);

        /**********= 统计葡萄通讯活跃度 type:2 =**********/
        //开始测试
        //测试通过
//                $voip_number = $obj_order->getVoIpNumber($num_app_id, $begin_time_string, $end_time_string);//得到团队葡萄通讯购买订单数，时间区间为字符串时间
//                $active_value = $voip_number;
        $active_value = 0;
        $this->addUserActive($num_app_id, 2, $active_value);

        /**********= 统计圈子购买活跃度 type:7 (不包括竞价) =**********/
        //开始测试
        //测试通过
        $circle_number = $this->getCircleNumber($num_app_id, $begin_time_string, $end_time_string);//得到团队圈子购买订单数量，时间区间为字符串时间
        $active_value = $circle_number * 2;
        $this->addUserActive($num_app_id, 7, $active_value);

        /**********= 统计用户附加活跃值 type:99 =**********/
        //开始测试
        //测试通过
        $active_value = $num_append_active_value;
        $this->addUserActive($num_app_id, 99, $active_value);
        return true;
    }
}
