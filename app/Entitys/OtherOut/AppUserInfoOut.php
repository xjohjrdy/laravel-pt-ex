<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class AppUserInfoOut extends Model
{
    protected $connection = 'app38_out';
    protected $table = 'lc_user';
    public $timestamps = false;


    /**
     * 根据用户id获取用户信息
     * @param $user_id
     * @return Model|null|static
     */
    public function getUserById($user_id)
    {
        $model = $this->where(['id' => $user_id])->first();
        return $model;
    }
}
