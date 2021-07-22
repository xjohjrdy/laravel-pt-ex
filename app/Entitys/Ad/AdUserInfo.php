<?php

namespace App\Entitys\Ad;

use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdUserInfo extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_common_member';
    public $timestamps = false;


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
     * 关系重置
     * @param $pt_pid
     * @param $pt_id
     */
    public function relationshipReset($pt_pid, $pt_id, $phone)
    {
        $user = $this->where('pt_id', $pt_id)->first(['pt_pid']);
        if ($pt_pid <> $user->pt_pid) {
            $this->where('pt_id', $pt_id)->update([
                'pt_username' => $phone,
                'pt_id' => $pt_id,
                'pt_pid' => $pt_pid,
            ]);
        }
    }

    /**
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
     * @param $uid
     * @param $check_id
     * @return int
     */
    public function checkUserThreeFloorInfo($uid, $check_id)
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
                        return 2;
                    }
                    $user_three = $this->appToAdUserId($user_two->pt_pid);
                    if ($user_three) {
                        if ($user_three->uid == $uid) {
                            return 3;
                        }
                    }
                }
            }
        }

        return 0;
    }

    /**
     * 更新、重置二级密码
     * @return bool
     */
    public function resetTwoPassword($secret = '')
    {
        if ($secret != '') {
            $secret = md5($this->uid . $secret);
        }
        $res = $this->where('uid', $this->uid)->update(['secret' => $secret]);
        return $res;
    }

    /**
     * 通过用户username拿到用户
     * @param $username
     * @return Model|null|static
     */
    public function getUserByUsername($username)
    {
        $user = $this->where('username', (string)$username)->first();
        if (!$user) {
            $user = $this->where('username', 'like', $username . '_%')->first();
        }
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

    /**
     *
     * 当新用户新增//兼容旧版
     * @param $uid
     * @param $username
     * @param $phone
     * @param $app_id
     * @param $parent_id
     * @param $check_code
     * @return int
     */
    public function newUser($uid, $username, $phone, $app_id, $parent_id, $check_code)
    {

        $res = $this->insertGetId([
            'uid' => $uid,
            'username' => (string)$username,
            'password' => (string)md5('123456789'),
            'email' => (string)time() . rand(1, 100000) . '@rapp.com',
            'adminid' => intval(0),
            'groupid' => intval(10),
            'regdate' => time(),
            'emailstatus' => intval(0),
            'credits' => intval(0),
            'timeoffset' => 9999,
            'is_bind' => 2,
            'pt_id' => $app_id,
            'pt_username' => $phone,
            'check_code' => $check_code,
            'pt_pid' => $parent_id,
            'secret' => '',
        ]);
        return $res;
    }

    /**
     * @param $pt_id
     * @param $type
     * @return array
     * @throws ApiException
     */
    public function getUserThreeFloor($pt_id, $type)
    {
        $sql = '';
        $user = [];
        if ($type == 1) {
            $sql = '
        SELECT 
b.username as `username`,b.groupid as `groupid` ,b.pt_pid as `pt_pid`,b.pt_id as `pt_id`
FROM pre_common_member as a ,pre_common_member as b WHERE 
a.pt_id = b.pt_pid AND a.pt_id = ' . $pt_id;
        } elseif ($type == 2) {
            $sql = '
       SELECT 
c.username as `username`,c.groupid as `groupid`,c.pt_pid as `pt_pid`,c.pt_id as `pt_id`
FROM pre_common_member as a ,pre_common_member as b ,pre_common_member as c WHERE 
a.pt_id = b.pt_pid AND
b.pt_id = c.pt_pid
AND a.pt_id = ' . $pt_id;
        } elseif ($type == 3) {
            $sql = '
          SELECT 
d.username as `username`,d.groupid as `groupid`,d.pt_pid as `pt_pid`,d.pt_id as `pt_id`
FROM pre_common_member as a ,pre_common_member as b ,pre_common_member as c , pre_common_member as d WHERE 
a.pt_id = b.pt_pid AND
b.pt_id = c.pt_pid AND
c.pt_id = d.pt_pid AND
a.pt_id = ' . $pt_id;
        }
        if ($sql) {
            $user = DB::connection('a1191125678')->select($sql);
        } else {
            throw new ApiException('不符合正常类型！', '5005');
        }

        return $user;

    }

    /**
     * 更新用户等级
     * @param int $value
     * @return int
     */
    public function updateGroupId($value, $uid)
    {
        return $this->where(['uid' => $uid])->update(['groupid' => $value]);
    }
    /*
     * 通过用户id得到用户uid
     */
    public function getUidById($app_id)
    {
        return $this->where('pt_id', $app_id)->value('uid');
    }
    
    public function getNewVoipMoney($app_id, $time)
    {

        $res_data = DB::connection('a1191125678')
            ->select("
        SELECT SUM(tt1.real_price) as money
        FROM pre_voip_money_order as tt1
        INNER JOIN (
        SELECT 
                                        *
        FROM
                                        pre_common_member
        WHERE
                                        pt_id ={$app_id}
        UNION
        SELECT
                                        t2.* 
        FROM
                                        pre_common_member t1
                                        INNER JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid 
        WHERE
                                        t1.pt_id ={$app_id}
        UNION
        SELECT
                                        t3.* 
        FROM
                                        pre_common_member t1
                                        INNER JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                                        INNER JOIN pre_common_member t3 ON t2.pt_id = t3.pt_pid 
        WHERE
                                        t1.pt_id ={$app_id}
        UNION
        SELECT
                                        t4.* 
        FROM
                                        pre_common_member t1
                                        INNER JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                                        INNER JOIN pre_common_member t3 ON t2.pt_id = t3.pt_pid
                                        INNER JOIN pre_common_member t4 ON t3.pt_id = t4.pt_pid 
        WHERE
                                        t1.pt_id ={$app_id}
        ) as tt2  
        on tt1.app_id = tt2.pt_id 
        WHERE tt1.status = 1
         AND tt1.created_at > '{$time}'
        ");

        if (empty($res_data[0]->money)) {
            return 0;
        }
        return $res_data[0]->money;
    }

    public function getVipCount($pt_id)
    {
        $res_data = DB::connection('a1191125678')
            ->select("
                SELECT
                    ((
                    SELECT
                        count(*)
                    FROM
                        pre_common_member t1
                        RIGHT JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                        RIGHT JOIN pre_common_member t3 ON t2.pt_id = t3.pt_pid
                        RIGHT JOIN pre_common_member t4 ON t3.pt_id = t4.pt_pid
                    WHERE
                        t1.pt_id = {$pt_id}
                    AND 
                        t4.groupid in (23,24)
                    )+(
                    SELECT
                        count(*)
                    FROM
                        pre_common_member t1
                        RIGHT JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                        RIGHT JOIN pre_common_member t3 ON t2.pt_id = t3.pt_pid
                    WHERE
                        t1.pt_id = {$pt_id}
                    AND
                      t3.groupid in (23,24)
                    )+(
                    SELECT
                        count(*)
                    FROM
                        pre_common_member t1
                        RIGHT JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                    WHERE
                        t1.pt_id = {$pt_id}
                    AND 
                            t2.groupid in (23,24)
                        )
                    ) as ct
            ");
        if (empty($res_data[0]->ct)) {
            return 0;
        }
        return $res_data[0]->ct;
    }
    /*
     * 创建偶像时同步创建联盟上级
     */
    public function updateSyncPtPid($pt_id, $pt_pid)
    {
        return $this->where(['pt_id' => $pt_id])->update(['pt_pid' => $pt_pid]);
    }

}
