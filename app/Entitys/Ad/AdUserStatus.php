<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class AdUserStatus extends Model
{

    protected $connection = 'a1191125678';
    protected $table = 'pre_common_member_status';
    public $timestamps = false;

    /**
     *
     * 增加兼容
     * @param $uid
     * @param $ip
     * @return int
     */
    public function addUserStatus($uid,$ip)
    {
        $res = $this->insert([
            'uid' => $uid,
            'regip' => (string)$ip,
            'lastip' => (string)$ip,
            'lastvisit' => time(),
            'lastactivity' => time(),
            'lastpost' => 0,
            'lastsendmail' => 0
        ]);

        return $res;
    }
}
