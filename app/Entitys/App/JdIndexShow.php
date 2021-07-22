<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class JdIndexShow
 * @package App\Entitys\App
 * 京东及拼多多首页banner及分类数据model
 */
class JdIndexShow extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_jd_index_show';
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
     * 根据类别加载数据
     * @param $type 类别： 1：京东首页滑动图，2：京东首页分类，3：拼多多首页滑动图，4：拼多多首页分类
     * @return JdIndexShow[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getListByType($type)
    {
        return $this->where('type', $type)->orderByDesc('order_by')->get(['jump', 'show_info']);
    }
}
