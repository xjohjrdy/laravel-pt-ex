<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_config';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 得到海报背景图
     */
    public function getConfig()
    {
        return $this->whereIn('name',['url_background','poster_background'])->pluck('value','name');
    }
    /*
     * 得到统一返利比例
     */
    public function getConfigValue($name)
    {
        return $this->where(['name'=>$name])->value('value');
    }
    /*
     * 隐藏二维码数据
     */
    public function getHideConfigValue($name)
    {
        return $this->where(['name'=>$name])->value('value');
    }
}
