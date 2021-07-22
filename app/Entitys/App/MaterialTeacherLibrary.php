<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class MaterialTeacherLibrary extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_material_teacher_library';
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
     * get topic info
     */
    public function getByTopic($topic_id)
    {
        return $this->where(['topic' => $topic_id])->first(['id', 'circle_img', 'title', 'click_number']);
    }

    /**
     * get hot info
     */
    public function getByHot()
    {
        $time = time();
        $res = $this->where(['hot' => 1])->where('start_time', '<', $time)->where('end_time', '>', $time)
            ->orderByDesc('order')
            ->paginate(10, ['id', 'order', 'square_img', 'start_time', 'title', 'type_title', 'created_at', 'click_number', 'good', 'jump_no']);

        $res->map(function ($model) {
            $model->created_at_v = date('Y-m-d H:i', $model->start_time);
        });

        return $res;
    }

    /**
     * get by type
     */
    public function getByType($type)
    {
        $res = $this->where(['type' => $type])->orderByDesc('order')
            ->paginate(10, ['id', 'mp3', 'order', 'mp4', 'square_img', 'start_time', 'title', 'type_title', 'created_at', 'click_number', 'good', 'jump_no']);

        $res->map(function ($model) {
            $model->created_at_v = date('Y-m-d H:i', $model->start_time);
        });

        return $res;
    }

    /**
     * get by like
     */
    public function getBySearch($title)
    {
        $res = $this->where('title', 'like', '%' . $title . '%')->orderByDesc('order')
            ->paginate(10, ['id', 'mp3', 'mp4', 'order', 'square_img', 'start_time', 'title', 'type_title', 'created_at', 'click_number', 'good', 'jump_no']);

        $res->map(function ($model) {
            $model->created_at_v = date('Y-m-d H:i', $model->start_time);
        });

        return $res;
    }

    /**
     * get by topic
     */
    public function getByAllTopic($topic_id)
    {
        $res = $this->where(['topic' => $topic_id])->orderByDesc('order')
            ->paginate(10, ['id', 'mp3', 'mp4', 'order', 'square_img', 'start_time', 'title', 'type_title', 'created_at', 'click_number', 'good', 'jump_no']);
        $res->map(function ($model) {
            $model->created_at_v = date('Y-m-d H:i', $model->start_time);
        });

        return $res;
    }

    /**
     * get one
     */
    public function getOne($id)
    {

        $data = $this->where(['id' => $id])->first(['id', 'title', 'context', 'mp3', 'mp4', 'circle_img', 'type_title', 'created_at', 'click_number', 'good', 'jump_no']);
        return $data;
    }

    /**
     * good
     * @param $id
     * @return bool
     */
    public function good($id)
    {
        return $this->where(['id' => $id])->update(['good' => DB::raw("good + " . 1)]);
    }

    /**
     * click
     * @param $id
     * @return bool
     */
    public function click($id)
    {
        return $this->where(['id' => $id])->update(['click_number' => DB::raw("click_number + " . 1)]);
    }
}
