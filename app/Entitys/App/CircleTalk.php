<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleTalk extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_talk';
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
     * 获取圈子聊天记录列表
     * @param $circle_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList($circle_id)
    {
        $res = $this->where(['circle_id' => $circle_id])->orderByDesc('created_at')->paginate(10);
        return $res;
    }

    /**
     * 拿到最近的所有聊天记录数字
     * @param $circle_id
     * @return int
     */
    public function getCountList($circle_id)
    {
        $res = $this->where(['circle_id' => $circle_id])->count();
        return $res;
    }

    /**
     * 拿到最近的聊天记录时间
     * @param $circle_id
     * @return Model|null|static
     */
    public function getOneMessage($circle_id)
    {
        $res = $this->where(['circle_id' => $circle_id])->orderByDesc('created_at')->first();
        return $res;
    }

    /**
     * 发送消息
     * @param $data
     */
    public function pushInfo($data)
    {
        $res = $this->create($data);
    }
}
