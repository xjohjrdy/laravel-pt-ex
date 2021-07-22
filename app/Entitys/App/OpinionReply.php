<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpinionReply extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_opinion_reply';
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
     *
     */
    public function addInfo($data)
    {
        return $this->create($data);
    }

    /**
     *
     */
    public function updateRead($opinion_id)
    {
        return $this->where(['opinion_id' => $opinion_id, 'type' => 1])->update([
            'status' => 1,
        ]);
    }

    public function getAllInfo($opinion_id)
    {
        return $this->where(['opinion_id' => $opinion_id])->orderBy('created_at', 'desc')->paginate(10, [
            'app_id',
            'opinion_id',
            'header',
            'name',
            'content',
            'type',
//            'status',
            'created_at',
        ]);
    }


    public function pushAll($app_id)
    {
        return $this->where(['app_id' => $app_id, 'type' => 1, 'status' => 0])->count();
    }
}
