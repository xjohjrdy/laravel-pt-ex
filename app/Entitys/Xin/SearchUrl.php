<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class SearchUrl extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_search_url';
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 按类型获得搜索地址
     */
    function getSearchUrlByType($type)
    {
        return $this->where(['type'=>$type])
            ->first(['id','title','search_url','icon']);
    }
}
