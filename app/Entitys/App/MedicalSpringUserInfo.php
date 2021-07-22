<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalSpringUserInfo extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_spring_user_info';
    use SoftDeletes;

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

    /**
     * add user
     */
    public function addInfo($data)
    {
        return $this->create($data);
    }

    public function getUserInfo($app_id)
    {
        return $this->where(['app_id' => $app_id])->first();
    }
}
