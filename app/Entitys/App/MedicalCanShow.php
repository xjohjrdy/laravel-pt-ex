<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalCanShow extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_can_show';
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
     * @param $id
     */
    public function getOneThing($id)
    {
        return $this->where(['id' => $id])->first();
    }
}
