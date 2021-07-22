<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JdPhonePutIn extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_jd_phone_put_in';
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
     * 传入数据
     * @param $data
     * @return $this|Model
     */
    public function pushIn($data)
    {
        return $this->create($data);
    }

    /**
     * app_id获取信息
     */
    public function getInfo($app_id)
    {
        return $this->where(['app_id' => $app_id])->first();
    }

    /**
     * 电话获取id
     */
    public function getInfoByPhone($phone)
    {
        return $this->where(['phone' => $phone])->first();
    }
}
