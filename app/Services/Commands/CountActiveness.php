<?php

namespace App\Services\Commands;

use Illuminate\Support\Facades\DB;

class CountActiveness
{
    /**
     * 如果是一号，返回上月完整时间，如果不是一号，返回本月完整时间   list($begin,$end)
     * @return array
     */
    public function getLeadTime()
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

    /**
     * 如果是一号，返回上月完整时间，如果不是一号，返回本月完整时间   list($begin,$end)
     * @return array
     */
    public function getLeadTimeWuHang()
    {
        if (date('j') == 1) {
            $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
            $end = mktime(23, 59, 59, date('m') - 1, date('t', $begin), date('Y'));
        } else {
            $begin = mktime(0, 0, 0, date('m'), 1, date('Y'));
            $end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        }

        $begin = date('Y-m-01 00:00:00', $begin);
        $end = date('Y-m-t 23:59:59', $end);

        return [$begin, $end];
    }

    /**
     * 统计订单表所有用户的订单，以及对应的活跃值
     * @return mixed
     */
    public function countActive()
    {
        $current = $this->getLeadTime();
        $countActive = DB::connection('a1191125678')
            ->table('pre_aljbgp_order')
            ->select(DB::raw('uid ,if(max(groupid) = 24,3,if(max(groupid) = 23 ,1,0)) as active'))
            ->whereBetween('submitdate', $current)
            ->where('status', '=', 2)
            ->where(
                function ($query) {
                    $query->where('groupid', '=', 23)
                        ->orWhere('groupid', '=', 24);
                }
            )
            ->groupBy('uid')
            ->get();

        return $countActive;
    }

    /**
     * 清空指定类型的库积分
     * @param $type
     * @return bool
     */
    public function clearActive($type)
    {
        try {
            DB::connection('app38')
                ->table('lc_active_count')
                ->where('type', $type)
                ->update(['value' => 0]);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 通过uid查询该用户的pt_id以及他所有上级的pt_id
     * @param $uid
     * @return bool
     */
    public function getSuperior($uid)
    {
        try {
            $superiorPtId = DB::connection('a1191125678')
                ->select("
        SELECT
            pt_id 
        FROM
            pre_common_member 
        WHERE
            pt_id = (SELECT pt_pid FROM pre_common_member WHERE pt_id = ( SELECT pt_pid FROM pre_common_member WHERE pt_id = ( SELECT pt_pid FROM pre_common_member WHERE uid = {$uid} AND pt_pid != 0 ) AND pt_pid != 0 ) AND pt_pid != 0) 
        OR 
            pt_id = ( SELECT pt_pid FROM pre_common_member WHERE pt_id = ( SELECT pt_pid FROM pre_common_member WHERE uid = {$uid} AND pt_pid != 0 ) AND pt_pid != 0 ) 
        OR 
            pt_id = ( SELECT pt_pid FROM pre_common_member WHERE uid = {$uid} AND pt_pid != 0 ) 
        OR 
            uid = {$uid} 
                        ");
        } catch (\Exception $e) {
            return false;
        }

        return $superiorPtId;
    }

    /**
     * 根据传入的uid添加以及type添加对应的积分
     * @param $superiorPtId
     * @param $activeValue
     * @param int $type
     */
    public function countOrderActive($superiorPtId, $activeValue, $type = 0)
    {
        try {
            $insertValue = "";
            $updateTime = time();
            foreach ($superiorPtId as $singleUid) {
                $insertValue .= "({$singleUid->pt_id},{$type},{$updateTime},{$activeValue}),";
            }
            $insertValue = substr($insertValue, 0, -1);

            DB::connection('app38')
                ->select("
            INSERT INTO lc_active_count ( pt_id, type,update_time, value )
            VALUES
                {$insertValue}
            ON DUPLICATE KEY UPDATE 
            VALUE = VALUE +  {$activeValue},
            update_time = {$updateTime}
                            ");
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }


    public function countPtbActive()
    {

        $current = $this->getLeadTime();
        $countActive = DB::connection('app38')
            ->table('lc_voip_orders')
            ->select(DB::raw('user_id,count(*) * 0.5 as active'))
            ->whereBetween('finish_time', $current)
            ->groupBy('user_id')
            ->get();

        return $countActive;
    }

    /**
     * 统计新版我的通讯所有充值订单
     * @return \Illuminate\Support\Collection
     */
    public function countNewPtbActive()
    {


        $current = $this->getLeadTimeWuHang();
        $countActive = DB::connection('a1191125678')
            ->table('pre_voip_money_order')
            ->select(DB::raw('app_id,count(*) * 0.5 as active'))
            ->whereBetween('created_at', $current)
            ->where('status', 1)
            ->groupBy('app_id')
            ->get();

        return $countActive;
    }

    public function getPtSuperior($id)
    {
        try {
            $superiorPtId = DB::connection('app38')
                ->select("
        SELECT id as pt_id  FROM lc_user WHERE 
            id = (SELECT parent_id  FROM lc_user WHERE id = ( SELECT parent_id FROM lc_user WHERE id = ( SELECT parent_id FROM lc_user WHERE id = {$id}))) 
        OR 
            id = (SELECT parent_id FROM lc_user WHERE id = ( SELECT parent_id FROM lc_user WHERE id = {$id})) 
        OR 
            id = ( SELECT parent_id FROM lc_user WHERE id = {$id} ) 
        OR 
            id = {$id}
                        ");
        } catch (\Exception $e) {
            return false;
        }

        return $superiorPtId;
    }
    public function countPassUser()
    {
        $countNumber = DB::connection('app38')
            ->table('lc_user')
            ->where('status', 1)
            ->where('level', '>=', 2)
            ->count();
        return $countNumber;
    }
    public function getPassUserInfo($page, $limit)
    {
        $userInfo = DB::connection('app38')
            ->table('lc_user')
            ->where('status', 1)
            ->where('level', '>=', 2)
            ->offset($page)
            ->limit($limit)
            ->get(['id']);
        return $userInfo;
    }
    public function getGroupNumber($userId)
    {
        $allUserInfo = DB::connection('app38')
            ->select(
                "
            SELECT
			((
			SELECT
				count(*)
			FROM
				lc_user t1
				RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
				RIGHT JOIN lc_user t3 ON t2.id = t3.parent_id
				RIGHT JOIN lc_user t4 ON t3.id = t4.parent_id
			WHERE
				t1.id = {$userId}
			AND
				t4.status = 1
			)+(
			SELECT
				count(*)
			FROM
				lc_user t1
				RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
				RIGHT JOIN lc_user t3 ON t2.id = t3.parent_id
			WHERE
				t1.id = {$userId}
			AND
				t3.status = 1
			)+(
			SELECT
				count(*)
			FROM
				lc_user t1
				RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
			WHERE
				t1.id = {$userId}
			AND
				t2.status = 1
				)
			) as ct1
	                ");
        if (empty($allUserInfo)) {
            return false;
        }
        return $allUserInfo[0]->ct1 + 1;
    }
    public function getSignNumber($userId, $timeData)
    {
        $countSignObj = DB::connection('app38')
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
                            id = {$userId}
                        UNION
                        SELECT
                                t2.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id 
                        WHERE
                                t1.id = {$userId}
                                and
                                t2.status = 1	
                        UNION
                        SELECT
                                t3.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                INNER JOIN lc_user t3 ON t2.id = t3.parent_id 
                        WHERE
                                t1.id = {$userId}
                                and
                                t3.status = 1
                        UNION
                        SELECT
                                t4.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                                INNER JOIN lc_user t4 ON t3.id = t4.parent_id 
                        WHERE
                                t1.id = {$userId}
                                and
                                t4.status = 1	
                        ) AS tt2 ON tt1.user_id = tt2.id 
                WHERE
                        tt1.date = '{$timeData}'
            "
            );

        if (empty($countSignObj)) {
            return false;
        }
        return $countSignObj[0]->ct1;
    }
    public function getGroupOrderAccount($numUserId, $beginTime, $endTime)
    {
        $groupOrderAccount = DB::connection('app38')
            ->select("
                        SELECT SUM(tt1.cashback_amount)as amount
                        FROM lc_user_order as tt1
                        INNER JOIN (
                            SELECT 
                                *
                            FROM
                                lc_user
                            WHERE
                                id = {$numUserId}
                            UNION
                            SELECT
                                t2.* 
                            FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id 
                            WHERE
                                t1.id = {$numUserId}
                            UNION
                            SELECT
                                t3.* 
                            FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                INNER JOIN lc_user t3 ON t2.id = t3.parent_id 
                            WHERE
                                t1.id = {$numUserId}
                            UNION
                            SELECT
                                t4.* 
                            FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                                INNER JOIN lc_user t4 ON t3.id = t4.parent_id 
                            WHERE
                                t1.id = {$numUserId}
                        ) as tt2
                        on tt1.user_id = tt2.id 
                        WHERE tt1.status in (3,4,9)
                        AND tt2.status = 1
                        and tt1.confirm_time between {$beginTime} and {$endTime} 
                    ");

        if (empty($groupOrderAccount)) {
            return false;
        }
        return $groupOrderAccount[0]->amount;
    }
    public function getRegisterNumber($numUserId, $beginTime, $endTime)
    {
        $countRegisterNumber = DB::connection('app38')
            ->select("
                    SELECT
                    (
                        (
                        SELECT
                            COUNT(*)
                        FROM
                            lc_user t1
                            INNER JOIN lc_user t2 ON t1.id = t2.parent_id 
                        WHERE
                            t1.id = {$numUserId}
                            AND
                            t2.create_time between {$beginTime} AND {$endTime} 
                            and 
                            t2.status = 1
                        )+(
                        SELECT
                            COUNT(*) 
                        FROM
                            lc_user t1
                            INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                            INNER JOIN lc_user t3 ON t2.id = t3.parent_id 
                        WHERE
                            t1.id = {$numUserId}
                            AND
                            t3.create_time between {$beginTime} AND {$endTime} 
                            and 
                            t3.status = 1
                        )+(
                        SELECT
                            COUNT(*)
                        FROM
                            lc_user t1
                            INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                            INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                            INNER JOIN lc_user t4 ON t3.id = t4.parent_id 
                        WHERE
                            t1.id = {$numUserId}
                            AND
                            t4.create_time between {$beginTime} AND {$endTime} 
                            and 
                            t4.status = 1
                            )	
                    ) as ct
                    ");
        if (empty($countRegisterNumber)) {
            return false;
        }
        return $countRegisterNumber[0]->ct;
    }
    public function getMonetary($numUserId, $strBeginTime, $strEndTime)
    {
        $countRegisterNumber = DB::connection('app38')
            ->select("
                        SELECT SUM(tt1.ptb_number/10+tt1.real_price) as money
                        FROM lc_shop_orders as tt1
                        INNER JOIN (
                                SELECT 
                                        *
                                FROM
                                        lc_user
                                WHERE
                                        id = {$numUserId}
                                UNION
                                SELECT
                                        t2.* 
                                FROM
                                        lc_user t1
                                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id 
                                WHERE
                                        t1.id = {$numUserId}
                                UNION
                                SELECT
                                        t3.* 
                                FROM
                                        lc_user t1
                                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id 
                                WHERE
                                        t1.id = {$numUserId}
                                UNION
                                SELECT
                                        t4.* 
                                FROM
                                        lc_user t1
                                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                                        INNER JOIN lc_user t4 ON t3.id = t4.parent_id 
                                WHERE
                                        t1.id = {$numUserId}
                        ) as tt2
                        on tt1.app_id = tt2.id 
                        WHERE tt1.status = 3
                        AND tt2.status = 1
						AND tt1.price <> 800
                        AND tt1.updated_at between \"{$strBeginTime}\" AND \"{$strEndTime}\"
                        ");

        if (empty($countRegisterNumber)) {
            return false;
        }
        return $countRegisterNumber[0]->money;
    }
    public function addUserActive($userId, $type, $activeValue)
    {
        $updateTime = time();
        try {
            DB::connection('app38')
                ->select("
                    INSERT INTO lc_active_count ( pt_id, type,update_time, value )
                    VALUES
                        ({$userId},{$type},{$updateTime},{$activeValue})
                    ON DUPLICATE KEY UPDATE 
                    VALUE =  {$activeValue},
                    update_time = {$updateTime}
                    ");
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }


    /*
     * 统计圈子购买订单
     * 统计所有圈子购买为600的订单
     */
    public function countCircleActive()
    {
        $current = $this->getLeadTimeWuHang();
        $countActive = DB::connection('app38')
            ->table('lc_circle_ring_add_order')
            ->select(DB::raw('app_id,count(*) as active'))
            ->whereBetween('created_at', $current)
            ->where('money', 600)
            ->where('status', 1)
            ->groupBy('app_id')
            ->get();

        return $countActive;
    }

    /*
     * 获取自己和直属上级id
     */
    public function getDirectly($id)
    {
        try {
            $superiorPtId = DB::connection('app38')
                ->select("
        SELECT id as pt_id  FROM lc_user WHERE 
            id = ( SELECT parent_id FROM lc_user WHERE id = {$id} ) 
        OR 
            id = {$id}
                        ");
        } catch (\Exception $e) {
            return false;
        }

        return $superiorPtId;
    }

}
 