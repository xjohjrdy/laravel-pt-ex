<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class AdUserUcenterFields extends Model
{

    protected $connection = 'a1191125678';
    protected $table = 'pre_ucenter_memberfields';
    public $timestamps = false;

    /**
     * 增加记录兼容旧版
     * @param $uid
     * @return int
     */
    public function addNewUserFields($uid)
    {
        $res = $this->insert([
            'uid'=>$uid,
            'blacklist'=>'',
        ]);
        return $res;
    }
}
