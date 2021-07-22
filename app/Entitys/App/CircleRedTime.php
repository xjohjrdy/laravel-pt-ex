<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleRedTime extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_red_time';
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
     * 获取红包列表
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getAllByAppId($app_id, $type = 0)
    {
        $res = $this->where(['to_app_id' => $app_id])->orderByDesc('created_at')->get(['have', 'from_app_id', 'to_app_id', 'to_app_username', 'to_app_img', 'created_at']);
        if ($type) {
            $res = $this->where(['to_app_id' => $app_id, 'type' => $type])->orderByDesc('created_at')->get(['have', 'from_app_id', 'to_app_id', 'to_app_username', 'to_app_img', 'created_at']);
        }
        return $res;
    }

    /**
     * 获取红包总的我的币
     * @param $app_id
     * @return mixed
     */
    public function getAllSum($app_id, $type = 0)
    {
        $res = $this->where(['to_app_id' => $app_id])->sum('have');
        if ($type) {
            $res = $this->where(['to_app_id' => $app_id, 'type' => $type])->where('created_at','>','2020-01-01 00:00:00')->sum('have');
        }
        return $res;
    }
}
