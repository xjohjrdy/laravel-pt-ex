<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class HomeName extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_home_name';
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 得到首页名字表的所有数据
     */
    public function getHomeName()
    {
        return $this->get();
    }
}
