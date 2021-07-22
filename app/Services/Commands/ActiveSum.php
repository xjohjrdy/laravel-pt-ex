<?php

namespace App\Services\Commands;

use Illuminate\Support\Facades\DB;

class ActiveSum
{
    public function countPassUser()
    {
        $countNumber = DB::connection('app38')
            ->table('lc_user')
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
        $userInfo = DB::connection('app38')
            ->table('lc_user')
            ->where('status', 1)
            ->where('level', '>=', 2)
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

    public function getSingleActive($ptId)
    {
        $active = DB::connection('app38')
            ->table('lc_active_count')
            ->where('pt_id', $ptId)
            ->get(['value', 'type']);

        $arrActive = array();
        foreach ($active as $singleValue) {
            $arrActive[$singleValue->type] = $singleValue->value;
        }

        /**
         * lc_active_count $arrActive数组
         * 0的活跃度 统计开通代理商订单
         * 2的活跃度 统计我的通讯活跃度
         * 3的活跃度 统计签到率活跃度
         * 4的活跃度 统计团队报销活跃度
         * 5的活跃度 统计团队拉人活跃度
         * 6的活跃度 统计我的商城活跃度
         * 7的活跃度 统计圈子购买活跃度
         * 99 附加活跃度
         */
        /**
         * 客户端展示内容，对应id
         * 1、本人及直推用户的签到总数除以团队总人数（团队总人数=本人+直推的总数）（团队总人数不足50人，按50人算）（每天累加，月封顶20分）
         * * 1.规则改版 本人签到活跃值&直推签到活跃值每次都加0.015，每个用户签到模块每天活跃值累加上限0.75分，每个用户每个月签到活跃值累加上限20分
         * 2、本人成为代理商、及每直推一名代理商+2分（月封顶30分）改成月封顶40分
         * 3、xxx 不统计
         * 4、本人及直推用户每购买一笔我的通讯话费+1分（月封顶20分）
         * 5、本人及直推用户报销每增加1元报销+0.1分（月封顶20分）
         * 6、直推用户新增1人奖励1分（月封顶20分）
         * 7、本人及直推用户购买我的爆款商城购物1笔+1分（月封顶10分）改成月封顶20分
         * 8、本人或直推用户首次购买圈子（即600购买，不包括后续竞价购买、免费赠送）。1个圈子活跃值奖励2分（月封顶30分）
         * 99、附加分
         */

        $arrActiveValue = array();
        if (empty($arrActive[3])) {
            $arrActiveValue[1] = 0.0;
        } else {
            $arrActiveValue[1] = $arrActive[3] > 20 ? 20 : floatval($arrActive[3]);
        }
        if (empty($arrActive[0])) {
            $arrActiveValue[2] = 0.0;
        } else {
            $arrActiveValue[2] = $arrActive[0] > 40 ? 40 : floatval($arrActive[0]);
        }

        $arrActiveValue[3] = 0.0;
        if (empty($arrActive[2])) {
            $arrActiveValue[4] = 0.0;
        } else {
            $arrActiveValue[4] = $arrActive[2] > 20 ? 20 : floatval($arrActive[2]);
        }
        if (empty($arrActive[4])) {
            $arrActiveValue[5] = 0.0;
        } else {
            $arrActiveValue[5] = $arrActive[4] > 20 ? 20 : floatval($arrActive[4]);
        }
        if (empty($arrActive[5])) {
            $arrActiveValue[6] = 0.0;
        } else {
            $arrActiveValue[6] = $arrActive[5] > 20 ? 20 : floatval($arrActive[5]);
        }
        if (empty($arrActive[6])) {
            $arrActiveValue[7] = 0.0;
        } else {
            $arrActiveValue[7] = $arrActive[6] > 20 ? 20 : floatval($arrActive[6]);
        }
        if (empty($arrActive[7])) {
            $arrActiveValue[8] = 0.0;
        } else {
            $arrActiveValue[8] = $arrActive[7] > 30 ? 30 : floatval($arrActive[7]);
        }
        if (empty($arrActive[99])) {
            $arrActiveValue[99] = 0.0;
        } else {
            $arrActiveValue[99] = $arrActive[99];
        }

        return $arrActiveValue;

    }

    public function setActiveLog($ptId, $arrTotalActive)
    {
        $uid = $ptId;

        $yesterday = strtotime('yesterday');

        $isVal = DB::connection('app38')
            ->table('lc_active_every_days')
            ->where(['uid' => $uid, 'time' => $yesterday])
            ->exists();

        try {
            if ($isVal) {
                DB::connection('app38')
                    ->table('lc_active_every_days')
                    ->where(['uid' => $uid, 'time' => $yesterday])
                    ->update(['context' => json_encode($arrTotalActive)]);
            } else {
                DB::connection('app38')
                    ->table('lc_active_every_days')
                    ->insert([
                        'uid' => $uid,
                        'time' => $yesterday,
                        'context' => json_encode($arrTotalActive)
                    ]);
            }

        } catch (\Exception $e) {
            return false;
        }

        return true;

    }

    public function setUserActive($ptId, $numTotalActive, $signActive)
    {

        try {
            DB::connection('app38')
                ->table('lc_user')
                ->where('id', $ptId)
                ->update([
                    'active_value' => $numTotalActive,
                    'sign_active_value' => $signActive
                ]);
        } catch (\Exception $e) {
            return false;
        }

        return true;

    }


}
