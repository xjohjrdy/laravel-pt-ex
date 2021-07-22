<?php

namespace App\Services\Advertising;

use App\Entitys\Ad\AdUserFieldForum;
use App\Entitys\Ad\AdUserFieldHome;
use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\AdUserProfile;
use App\Entitys\Ad\AdUserStatus;
use App\Entitys\Ad\AdUserUcenter;
use App\Entitys\Ad\AdUserUcenterFields;
use App\Entitys\Ad\AdVipOrder;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAccount;
use App\Exceptions\ApiException;

class AdvertisingUser
{
    protected $adUserProfile;
    protected $adUserInfo;
    protected $userAccount;
    protected $adUserUcenter;
    protected $adUserUcenterFields;
    protected $adUserStatus;
    protected $adUserFieldForum;
    protected $adUserFieldHome;
    protected $rechargeOrder;


    public function __construct(AdUserInfo $adUserInfo, UserAccount $userAccount, RechargeOrder $rechargeOrder, AdUserProfile $adUserProfile, AdUserUcenter $adUserUcenter, AdUserUcenterFields $adUserUcenterFields, AdUserStatus $adUserStatus, AdUserFieldForum $adUserFieldForum, AdUserFieldHome $adUserFieldHome)
    {
        $this->adUserInfo = $adUserInfo;
        $this->userAccount = $userAccount;
        $this->adUserProfile = $adUserProfile;
        $this->adUserUcenter = $adUserUcenter;
        $this->adUserUcenterFields = $adUserUcenterFields;
        $this->adUserStatus = $adUserStatus;
        $this->adUserFieldForum = $adUserFieldForum;
        $this->adUserFieldHome = $adUserFieldHome;
        $this->rechargeOrder = $rechargeOrder;
    }

    /**
     * 以兼容的形式添加新用户
     */
    public function compatibleAddUsers($username, $app_id, $parent_id, $ip)
    {
        /**
         * 旧参数
         *    $uid = $param_arr['uid']? $param_arr['uid']:$_GET['uid'];
         * $parentid = $param_arr['parentid']?$param_arr['parentid']:$_GET['parentid'];
         * $username = $param_arr['username']?$param_arr['username']:$_GET['username'];
         * $key = $param_arr['key']? $param_arr['key']:$_GET['key'];
         * $time = $param_arr['time']?$param_arr['time']:$_GET['time'];
         * $url = $param_arr['url']?$param_arr['url']:$_GET['url'];
         */
        $arr = $this->adUserInfo->where(['pt_id' => $app_id, 'pt_username' => $username])->first(['pt_username', 'pt_pid', 'pt_id', 'check_code', 'is_bind', 'uid', 'groupid']);
        if (empty($arr) && $app_id > 0) {
            $check_username = $this->adUserInfo->getUserByUsername($username);
            if ($check_username) {
                $insert_username = $username . '_' . $app_id;
            } else {
                $insert_username = $username;
            }
            $ucenter_member = $this->adUserUcenter->where(['username' => $username])->first(['uid']);
            if (!$ucenter_member) {
                $ucenter_member_uid = $this->adUserUcenter->addUcenterUser($username, $ip);
            } else {
                $ucenter_member_uid = $ucenter_member->uid;
            }
            if ($this->checkIsInsert($ucenter_member_uid)) {
                throw new ApiException('您的账号出现异常！请联系客服解决！错误信息：'.$ucenter_member_uid, '5005');
            }
            /**
             * 详情可见/public_html/source/class/table/table_common_member.php/321行插入方法
             * common_member---重要
             * common_member_count -重要
             * common_member_profile --重要
             * ucenter_memberfields --需要插入
             * common_member_log ---线上看是空的，暂时不插入
             * common_member_status --需要插入
             * common_member_field_forum --需要插入
             * common_member_field_home --需要插入
             */
            $res_add_user = $this->adUserInfo->newUser($ucenter_member_uid, $insert_username, $username, $app_id, $parent_id, $this->randomKeys(36));
            $res_add_account = $this->userAccount->getUserAccount($ucenter_member_uid)->uid;
            if ($res_add_user <> $res_add_account) {
                throw new ApiException('账号和账号余额不匹配！请联系客服反馈！', '5004');
            }
            $res_add_profile = $this->adUserProfile->addProfile($ucenter_member_uid, $insert_username, $username);
            $res_add_fields = $this->adUserUcenterFields->addNewUserFields($ucenter_member_uid);
            $res_add_status = $this->adUserStatus->addUserStatus($ucenter_member_uid, $ip);
            $res_add_forum = $this->adUserFieldForum->addForum($ucenter_member_uid);
            $res_add_home = $this->adUserFieldHome->addHome($ucenter_member_uid);
            if (!$res_add_profile || !$res_add_fields || !$res_add_status || !$res_add_forum || !$res_add_home) {
                throw new ApiException('您的账号出现数据空缺！请联系客服反馈！', '5003');
            }
        }
        $res = $this->adUserInfo->where(['pt_id' => $app_id, 'pt_username' => $username])->first(['pt_username', 'pt_pid', 'pt_id', 'check_code', 'is_bind', 'uid', 'groupid', 'email', 'username']);
        return $res;
    }

    /**
     * 兼容登录用户
     */
    public function compatibleLoginUsers()
    {

    }

    /**
     * @param $length
     * @return string
     */
    public function randomKeys($length)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 35)};
        }
        return $key;
    }

    /**
     * 这种情况几乎不可能，但是旧数据库表内容复杂，不排除有部分用户有这种情况
     * 校验是否能进行接下来的插入操作
     * @param $uid
     * @return bool
     */
    public function checkIsInsert($uid)
    {
        $res = false;

        $res_forum = $this->adUserFieldForum->where('uid', $uid)->first(['uid']);
        $res_home = $this->adUserFieldHome->where('uid', $uid)->first(['uid']);
        $res_status = $this->adUserStatus->where('uid', $uid)->first(['uid']);
        $res_ucenter = $this->adUserUcenterFields->where('uid', $uid)->first(['uid']);
        $res_user = $this->adUserInfo->where('uid', $uid)->first(['uid']);
        $res_profile = $this->adUserProfile->where('uid', $uid)->first(['uid']);
        $res_account = $this->userAccount->where('uid', $uid)->first(['uid']);

        if ($res_forum || $res_home || $res_status || $res_ucenter || $res_user || $res_profile || $res_account) {
            $res = true;
        }

        return $res;
    }

    /**
     * 旧版经常会有降级的情况，如果出现需要根据订单升回去
     */
    public function isNeedLevel($app_id)
    {
        $ad_user_info = $this->adUserInfo->appToAdUserId($app_id);
        if (!$ad_user_info) {
            return 1;
        }
        if ($ad_user_info->groupid <> 10) {
            return 1;
        }
        $uid = $ad_user_info->uid;
        $orders = $this->rechargeOrder->getUserOrders($uid);
        $is_vip = 0;
        $group_id = 10;
        foreach ($orders as $k => $v) {
            if ($v->price == 300 && $v->status == 2) {
                $is_vip = $is_vip + 1;
            }
            if ($v->price == 800 && $v->status == 2) {
                $is_vip = $is_vip + 1;
            }
            if ($v->price == 3000 && $v->status == 2) {
                $is_vip = $is_vip + 2;
            }
            if ($v->price == 2200 && $v->status == 2) {
                $is_vip = $is_vip + 1;
            }
            if ($v->price == 2700 && $v->status == 2) {
                $is_vip = $is_vip + 1;
            }
        }
        if ($is_vip == 1) {
            $group_id = 23;
        }
        if ($is_vip > 1) {
            $group_id = 24;
        }
        if ($ad_user_info->groupid <> $group_id) {
            $this->adUserInfo->updateGroupId($group_id, $uid);
        }
        return $group_id;
    }
}
