<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class UserWork extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_orange_work_order';
    public $timestamps = false;

    /**
     * 获取用户所有的工单信息
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getWorkByUid($uid)
    {
        $res = $this->where(['uid' => $uid])->get(['add_time', 'content', 'id']);
        return $res->toArray();
    }

    /**
     *
     * @param $uid
     * @param $user_name
     * @param $arr
     * @return int
     */
    public function addNewWork($uid,$user_name,$arr)
    {

        $id = $this->insertGetId([
            'wid'=>2,
            'uid'=>$uid,
            'user_name'=>$user_name,
            'content'=>serialize($arr),
            'deal_name'=>'',
            'add_time'=>time(),
        ]);
        return $id;
    }
}
