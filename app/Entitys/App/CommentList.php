<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommentList extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_comment_list';
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
    public function updateStatus($id, $status)
    {
        return $this->where(['id' => $id])->update([
            'status' => $status,
        ]);
    }

    public function updateNow($id, $ok_time, $start)
    {
        return $this->where(['id' => $id])->update([
            'ok_time' => $ok_time,
            'start' => $start,
        ]);
    }

    /**
     *
     */
    public function getFirst($id)
    {
        return $this->where(['id' => $id])->first();
    }


    public function getAllInfo($app_id)
    {
        return $this->where(['app_id' => $app_id])->orderByDesc('created_at')->paginate(10, [
            'id',
            'app_id',
            'all',
            'phone',
            'wechat',
            'img',
            'status',
            'type',
            'from',
            'ok_time',
            'start',
            'created_at',
        ]);
    }

    /**
     *
     */
    public function updateEnd($app_id)
    {
        $end_time = time() - 86400;
        return $this->where(['app_id' => $app_id, 'start' => 0])
            ->where('status', '<>', '2')
            ->where('status', '<>', '3')
            ->where('ok_time', '<', $end_time)
            ->update([
                'status' => 3
            ]);
    }
}
