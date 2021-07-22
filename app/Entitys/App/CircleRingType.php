<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleRingType extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_type';
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
     * 获取分类列表
     * @return \Illuminate\Support\Collection
     */
    public function getList()
    {
        $res = $this->get(['id', 'title']);
        return $res;
    }
}
