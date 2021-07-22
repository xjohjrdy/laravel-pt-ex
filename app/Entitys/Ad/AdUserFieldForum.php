<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class AdUserFieldForum extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_common_member_field_forum';
    public $timestamps = false;

    /**
     * 兼容
     * @param $uid
     * @return bool
     */
    public function addForum($uid)
    {
        $res = $this->insert([
            'uid'=>$uid,
            'medals'=>'',
            'sightml'=>'',
            'groupterms'=>'',
            'groups'=>'',
        ]);

        return $res;
    }
}
