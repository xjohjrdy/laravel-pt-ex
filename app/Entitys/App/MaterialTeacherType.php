<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialTeacherType extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_material_teacher_type';
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
     * 获取所有分类
     * @return \Illuminate\Support\Collection
     */
    public function getAll()
    {
        return $this->orderBy('order')->limit(6)->get(['title', 'img', 'id']);
    }
}
