<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleIndexUpNo extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_index_up_no';
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
     * 获取圈子禁止到期时间
     * @param $circle_id
     * @return Model|null|static
     */
    public function getCircleNo($circle_id)
    {
        return $this->where(['circle_id' => $circle_id])->first();
    }
}
