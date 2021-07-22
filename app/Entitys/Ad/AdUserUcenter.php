<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class AdUserUcenter extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_ucenter_members';
    public $timestamps = false;

    /**
     */
    public function addUcenterUser($username,$ip = '127.0.0.1')
    {
        $salt = substr(uniqid(rand()), -6);
        $password = md5(md5('123456789').$salt);

        $res = $this->insertGetId([
            'username'=>$username,
            'password'=>$password,
            'email'=>time().rand(1, 100000).'@rapp.com',
            'regip'=>$ip,
            'regdate'=>time(),
            'lastloginip'=>0,
            'lastlogintime'=>time(),
            'salt'=>$salt,
        ]);

        return $res;
    }
}
