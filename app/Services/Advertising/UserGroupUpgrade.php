<?php

namespace App\Services\Advertising;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use Illuminate\Support\Facades\DB;

class UserGroupUpgrade
{
    protected $adUserInfo;
    protected $userAccount;
    protected $userCreditLog;
    protected $aboutLog;

    protected $tempUserInfo;

    public function __construct(AdUserInfo $adUserInfo, UserAccount $userAccount, UserCreditLog $userCreditLog, UserAboutLog $aboutLog)
    {
        $this->adUserInfo = $adUserInfo;
        $this->userAccount = $userAccount;
        $this->userCreditLog = $userCreditLog;
        $this->aboutLog = $aboutLog;
    }

    /**
     * 根据用户uid判断用户是否有直推十个代理商或者合伙人用户
     * @param $uid
     * @return bool
     */
    public function isUpgrade($uid)
    {
        $res = DB::connection('a1191125678')
            ->select("
            SELECT
                * 
            FROM
                pre_common_member AS t1 
            WHERE
                groupid = 23 
                AND (
            SELECT
                COUNT( * ) 
            FROM
                pre_common_member AS t2 
            WHERE
                t2.pt_pid = t1.pt_id 
                AND t2.groupid IN ( 23, 24 ) 
                AND t1.pt_id > 0 
                ) >= 10 
                AND uid = ?
            ", [$uid]);
        if (empty($res)) {
            return true;
        }
        $this->tempUserInfo = $res[0];
        return false;
    }
    public function addPTB()
    {
        try {
            $uid = $this->tempUserInfo->uid;
            $username = $this->tempUserInfo->username;
            $ptId = $this->tempUserInfo->pt_id;
            $userAccount = $this->userAccount->getUserAccount($uid);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
    public function updateGroupId()
    {
        try {
            $uid = $this->tempUserInfo->uid;
            $this->adUserInfo->where('uid', $uid)->update([
                'groupid' => 24,
                'groupexpiry' => time()
            ]);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
