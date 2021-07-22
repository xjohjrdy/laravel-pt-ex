<?php

namespace App\Entitys\App;

use App\Services\ZhongKang\ZhongKangServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalSpringRainChat extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_spring_rain_chat';
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

    /*
     * 增加医生回复
     */
    public function doctorReply($data){
        return $this->create($data);
    }
}
