<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaobaoClassification extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_classification';
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
     * 获取对应的值的内容
     */
    public function getByFather($father_id)
    {
        return $this->where(['father_id' => $father_id])->get();
    }
}
