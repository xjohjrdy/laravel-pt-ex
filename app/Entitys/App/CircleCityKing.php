<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleCityKing extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_city_king';
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
     * 获得地区的城主信息
     * @param $area
     * @return Model|null|static
     */
    public function getByArea($area)
    {
        $res = $this->where(['area' => $area])->first([
            'id', 'app_id', 'area', 'number_circle'
        ]);
        return $res;
    }

    /**
     * 拿到城市信息，用id
     * @param $id
     * @return Model|null|static
     */
    public function getById($id)
    {
        $res = $this->where(['id' => $id])->first();
        return $res;
    }

    /**
     * 更新新的城主
     * @param $king_id
     * @param $app_id
     * @return bool
     */
    public function updateNewKing($king_id, $app_id)
    {
        $res = $this->where(['id' => $king_id])->update([
            'app_id' => $app_id
        ]);
        return $res;
    }

    /*
     * 通过地域搜索有无创建该地域的圈子，
     * 有则记录加一，没有则创建，并返回该圈子信息
     */
    public function createOrAdd($city)
    {
        $obj_info = $this->where('area', $city);
        if ($obj_info->exists()) {
            $obj_info->increment('number_circle');
            return $obj_info->first();
        } else {
            return $this->create([
                'app_id' => 0,
                'area' => $city,
                'number_circle' => 1,
            ]);
        }
        return $obj_info;
    }
}
