<?php

namespace App\Services\Commands;

use Illuminate\Support\Facades\DB;

class ActiveTogether
{
    public function countPassUser()
    {
        $countNumber = DB::connection('app38')
            ->table('lc_user')
            ->where('status', 1)
            ->where('level', '>', 2)
            ->count();
        return $countNumber;
    }
    public function getPassUserInfo($page, $limit)
    {
        $userInfo = DB::connection('app38')
            ->table('lc_user')
            ->where('status', 1)
            ->where('level', '>', 2)
            ->offset($page)
            ->limit($limit)
            ->get(['id', 'sign_active_value', 'append_active_value']);
        return $userInfo;
    }
    public function getSingleActive($ptId, $sign)
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
         * 1、三级以内签到数除以团队总人数（不到50人的除以50计算）（月封顶10分）
         * 2、三级以内每新增一名VIP+2分，新增一名合伙人+3分（月封顶20分）
         * 4、三级以内每购买一笔我的通讯话费+0.5分（10分封顶）
         * 5、三级以内报销每增加1元报销活跃值0.1分（月封顶20分）
         * 6、三级以内新增1人奖励1分（月封顶20分）
         * 7、我的商城消费，对应7 （月封顶20分）
         * 8、圈子购买，对应8 （月封顶20分）
         */

        $arrActiveValue = array();
        if (empty($arrActive[3])) {
            $arrActiveValue[1] = $sign;
        } else {
            $arrActive[3] = $arrActive[3] + $sign;
            $arrActiveValue[1] = $arrActive[3] > 20 ? 20 : floatval($arrActive[3]);
        }
        if (empty($arrActive[0])) {
            $arrActiveValue[2] = 0.0;
        } else {
            $arrActiveValue[2] = $arrActive[0] > 20 ? 20 : floatval($arrActive[0]);
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
            $arrActiveValue[5] = $arrActive[4] > 30 ? 30 : floatval($arrActive[4]);
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
            $arrActiveValue[8] = $arrActive[7] > 20 ? 20 : floatval($arrActive[7]);
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
