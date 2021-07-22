<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleApply extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_friend_apply';
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
     * 获取当前的所有申请
     * @param $pt_id
     * @return \Illuminate\Support\Collection
     */
    public function getALLApply($pt_id)
    {
        $res = $this->where(['to_app_id' => $pt_id])->orderByDesc('created_at')->get();
        return $res;
    }

    /**
     * 获取单个应用请求信息
     * @param $id
     * @return Model|null|static
     */
    public function getOneApply($id)
    {
        $res = $this->where(['id' => $id])->first();
        return $res;
    }

    /**
     * 获取单个应用请求信息
     * @param $from
     * @param $to
     * @return Model|null|static
     */
    public function getOneApplyByFromTo($from, $to)
    {
        $res = $this->where(['from_app_id' => $from, 'to_app_id' => $to])->first();
        return $res;
    }

    /**
     * 发起申请
     * @param $to_app_id
     * @param $from_app
     * @param $from_username
     * @param $from_ico
     * @param $content
     * @return Model
     */
    public function toApply($to_app_id, $from_app, $from_username, $from_ico, $content)
    {
        $res = $this->updateOrCreate([
            'from_app_id' => $from_app,
            'to_app_id' => $to_app_id
        ], [
            'from_app_id' => $from_app,
            'to_app_id' => $to_app_id,
            'from_username' => $from_username,
            'from_ico' => $from_ico,
            'content' => $content,
            'status' => 0,
        ]);
        return $res;
    }

    /**
     * 对申请状态进行修改
     * @param $id
     * @param $status
     * @return bool
     */
    public function changeApplyStatus($id, $status)
    {
        return $this->where(['id' => $id, 'status' => 0])->update(['status' => $status]);
    }

    /**
     * 全部通过
     * @param $to_app_id
     * @return bool
     */
    public function allPass($to_app_id)
    {
        return $this->where(['to_app_id' => $to_app_id, 'status' => 0])->update(['status' => 2]);
    }
}
