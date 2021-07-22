<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleMaid extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_add_order_maid';
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
     * 返回某个用户单个种类的总和
     * @param $app_id
     * @param $type
     * @return mixed
     */
    public function getByUserTypeSum($app_id, $type)
    {
        $res = $this->where('created_at','>','2020-01-01 00:00:00')->where([
            'app_id' => $app_id,
            'type' => $type,
        ])->sum('money');
        return $res;
    }

    /**
     * 获取某个类型的分佣列表
     * @param $app_id
     * @param $type
     * @return \Illuminate\Support\Collection
     */
    public function getByUserType($app_id, $type)
    {
        $res = $this->where([
            'app_id' => $app_id,
            'type' => $type,
        ])->orderByDesc('created_at')->get([
            'app_id',
            'from_user_name',
            'from_user_phone',
            'from_user_img',
            'from_circle_name',
            'from_circle_img',
            'order_money',
            'money',
            'created_at',
        ]);
        return $res;
    }

    /**
     * 创建 分佣 记录
     * @param $data
     * @return $this|Model
     */
    public function createMaidForTwo($data)
    {
        $res = $this->create($data);
        return $res;
    }
}
