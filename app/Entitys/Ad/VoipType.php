<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoipType extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_voip_type';

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
     * @return \Illuminate\Support\Collection
     */
    public function getAllType()
    {
        $res = $this->get();
        return $res;
    }

    /**
     * 获取内容
     * @param $id
     * @return Model|null|static
     */
    public function getById($id)
    {
        $res = $this->where(['id' => $id])->first();
        return $res;
    }
}
