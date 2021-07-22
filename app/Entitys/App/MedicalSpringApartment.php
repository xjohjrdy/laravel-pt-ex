<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalSpringApartment extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_spring_rain_department';
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
     * get apartment
     */
    public function getApartment()
    {
        $one_apartment = $this->where(['type' => 1])->get();

        foreach ($one_apartment as $k => $item) {
            $one_apartment[$k]['two_apartment'] = $this->where(['type' => 2, 'last_key' => $item->key])->get();
        }

        return $one_apartment;
    }
}
