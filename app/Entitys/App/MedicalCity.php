<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalCity extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_city';
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
     * 增加
     */
    public function addInfo($data)
    {
        return $this->create($data);
    }

    /**
     * 获取
     */
    public function getInfo()
    {
        return $this->paginate(10);
    }

    /**
     * 城市名
     * @param $city_name
     */
    public function getOne($city_name)
    {
        return $this->where(['city_name' => $city_name])->first();
    }
}
