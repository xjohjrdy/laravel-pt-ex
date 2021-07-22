<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialTeacherTopic extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_material_teacher_topic';
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

    public function getOne()
    {
        return $this->where(['index' => '1'])->first(['title', 'id']);
    }
}
