<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class Adminer extends Model
{
    //
    protected $connection = 'app38';
    protected $table = 'lc_adminer';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    public function checkLogin($login, $pass){
        //参数异常,返回false
        if (empty($login)||empty($pass))
            return false;
        //根据账号获取管理员对象
        $adminer = self::where(['login'=>$login])->first();
        if (!$adminer)
            return false;
        //hash密码比对验证
        if (password_verify($pass, $adminer['pass'])) {
            return $adminer;
        }

        return false;
    }
}
