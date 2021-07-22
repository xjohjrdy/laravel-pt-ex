<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaobaoIndex extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_index';
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
     * 获取淘主页的信息
     */
    public function getTaoIndex($type)
    {
        return $this->where(['type' => $type])->get();
    }
}
