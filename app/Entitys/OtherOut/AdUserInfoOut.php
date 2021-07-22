<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class AdUserInfoOut extends Model
{
    protected $connection = 'a1191125678_out';
    protected $table = 'pre_common_member';
    public $timestamps = false;

   /*
    * 通过用户id得到用户uid
    */
    public function getUidById($app_id)
    {
        return $this->where('pt_id', $app_id)->value('uid');
    }

    /**
     * 1:是三级用户，0：不是三级用户
     * 校验前一个用户，下三级是否有第二个用户
     * @param $uid
     * @param $check_id
     * @return int
     */
    public function checkUserThreeFloor($uid, $check_id)
    {
        $user_check = $this->getUserById($check_id);
        if ($user_check) {
            $user_one = $this->appToAdUserId($user_check->pt_pid);
            if ($user_one) {
                if ($user_one->uid == $uid) {
                    return 1;
                }
                $user_two = $this->appToAdUserId($user_one->pt_pid);
                if ($user_two) {
                    if ($user_two->uid == $uid) {
                        return 1;
                    }
                    $user_three = $this->appToAdUserId($user_two->pt_pid);
                    if ($user_three) {
                        if ($user_three->uid == $uid) {
                            return 1;
                        }
                    }
                }
            }
        }

        return 0;
    }

    /**
     * app数据库id转广告联盟用户
     * @param $app_id
     * @return Model|null|static
     */
    public function appToAdUserId($app_id)
    {
        $user = $this->where('pt_id', $app_id)->first(['pt_username', 'pt_pid', 'pt_id', 'check_code', 'is_bind', 'uid', 'groupid', 'email', 'username']);
        return $user;
    }

    /**
     * 通过用户id拿到用户
     * @param $id
     * @return Model|null|static
     */
    public function getUserById($id)
    {
        $user = $this->where('uid', $id)->first();
        return $user;
    }
}
