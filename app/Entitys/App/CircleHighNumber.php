<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleHighNumber extends Model
{

    protected $connection = 'app38';
    protected $table = 'lc_circle_high_number';
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
     * 后台手动导入优质转正的用户，进行优质转正的记录查询获取
     */
    public function getCanGetNumber($app_id)
    {
        $high_number = $this->where(['app_id' => $app_id])->first();
        if (empty($high_number)) {
            return 0;
        }

        if ($high_number->number > 3) {
            $high_number->number = 3;
        }

        return $high_number->number;
    }
}
