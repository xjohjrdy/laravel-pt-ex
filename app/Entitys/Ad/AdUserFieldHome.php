<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class AdUserFieldHome extends Model
{

    protected $connection = 'a1191125678';
    protected $table = 'pre_common_member_field_home';
    public $timestamps = false;

    public function addHome($uid)
    {
        $res = $this->insert([
            'uid'=>$uid,
            'spacecss'=>'',
            'blockposition'=>'',
            'recentnote'=>'',
            'spacenote'=>'',
            'privacy'=>'',
            'feedfriend'=>'',
            'acceptemail'=>'',
            'magicgift'=>'',
            'stickblogs'=>'',
        ]);

        return $res;
    }
}
