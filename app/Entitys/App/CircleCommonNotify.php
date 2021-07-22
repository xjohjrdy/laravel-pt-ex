<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleCommonNotify extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_common_notify';
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
     * 拿到用户的通知列表
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getAllByAppId($app_id)
    {
        return $this->where(['app_id' => $app_id])->orderByDesc('created_at')->get();
    }

    /**
     * 拿到用户的未读通知列表
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getAllByAppIdNoRead($app_id)
    {
        return $this->where([
            'app_id' => $app_id,
            'status' => 0,
        ])->orderByDesc('created_at')->get();
    }

    /**
     * 已读掉当前所有的消息
     */
    public function read($app_id)
    {
        return $this->where(['app_id' => $app_id])->update([
            'status' => 1,
        ]);
    }

    /*
     * 添加一条系统消息
     */
    public function addNotify($data)
    {
        return $this->create($data);
    }

    /*
     * 设置器
     * 通知数量截取 word_content
     */
    public function setWordContentAttribute($value)
    {
        $this->attributes['word_content'] = substr($value, 0, 90);
    }
}
