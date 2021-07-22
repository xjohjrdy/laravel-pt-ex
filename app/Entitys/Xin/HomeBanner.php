<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_home_banner';
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 符合版本是得到的数据
     */
    public function getHomeBannerY()
    {
        return $this->where(['id' => 31])->orderBy('order')->get();
    }
    /*
     * 不符合版本是得到的数据
     */
    public function getHomeBannerN()
    {
        return $this->orderby('order')->get();
    }
}
