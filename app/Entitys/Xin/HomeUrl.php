<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class HomeUrl extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_home_url';
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 过滤指定的数据
     */
    public function getHomeUrl($where)
    {
        return $this->whereNotIn('title',$where)->orderBy('url_order')->get();
    }
}
