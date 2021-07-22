<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopIndexSlideImage extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_index_slide_image';
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
     * 拉出列表
     */
    public function getPage()
    {
        return $this->get();
    }
}
