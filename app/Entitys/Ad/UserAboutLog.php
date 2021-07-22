<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class UserAboutLog extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_common_about_log';

    /**
     * 插入用户变化前后的值记录
     * @param $credit_id 插入值得变化id
     * @param $uid 用户uid
     * @param $username 用户username/phone
     * @param $pt_id 用户pt_id、appid
     * @param $before_context 传之前的值记录
     * @param $after_context 传之后的值记录
     * @return int 插入记录id
     */
    public function addLog($credit_id,$uid,$username,$pt_id,$before_context,$after_context)
    {
        $insert_id = $this->insertGetId([
            'username' =>$username,
            'credit_id'=>$credit_id,
            'pt_id' => $pt_id,
            'uid' =>$uid,
            'update_time'=>time(),
            'before_context'=>json_encode($before_context),
            'after_context'=>json_encode($after_context)
        ]);

        return $insert_id;
    }
}
