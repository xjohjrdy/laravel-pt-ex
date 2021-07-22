<?php

namespace App\Entitys\Other;

use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminUser extends Model
{
    protected $connection = 'db001';
    protected $table = 'lc_admin_user';
    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    use SoftDeletes;

    public function checkLogin($phone, $password){
        $user = $this->where(['phone' => $phone])->first(['roles', 'phone', 'user_name', 'password', 'status']);
        if(empty($user)){
            throw new ApiException('该账号不存在,请联系管理员配置！', 3001);
        }
        if($user['password'] != $password){
            throw new ApiException('密码错误，请重新输入！', 3001);
        }
        if($user['status'] == 0){
            throw new ApiException('当前用户已被禁用请联系管理员！', 3001);
        }
        unset($user['password']);
        return $user->toArray();
    }
}
