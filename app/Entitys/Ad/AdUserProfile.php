<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class AdUserProfile extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_common_member_profile';
    public $timestamps = false;

    /**
     *
     * 兼容插入表
     * @param $uid
     * @param $username
     * @param $phone
     * @return int
     */
    public function addProfile($uid, $username, $phone)
    {
        $res = $this->insert([
            'uid' => $uid,
            'realname' => $username,
            'mobile' => $phone,
            'bio' => '',
            'interest' => '',
            'field1' => '',
            'field2' => '',
            'field3' => '',
            'field4' => '',
            'field5' => '',
            'field6' => '',
            'field7' => '',
            'field8' => '',
        ]);

        return $res;
    }
}
