<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class WebShow extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_web_show';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 得到关于我们的数据
     */
    public function aboutInfo()
    {
        return $this->where('id',1)->first();
    }

}
