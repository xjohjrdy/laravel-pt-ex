<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleComment extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_active_comment';
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
     * 获取当前列表
     * @param $active_id
     * @return \Illuminate\Support\Collection
     */
    public function getListComment($active_id)
    {
        $res = $this->where(['active_id' => $active_id])->get([
            'app_id',
            'user_name',
            'comment_content'
        ]);
        return $res;
    }

    /**
     *
     * @param $data [
     * 'app_id',
     * 'user_name',
     * 'comment_content',
     * 'active_id',
     * ]
     * @return $this|Model
     */
    public function pushComment($data)
    {
        $res = $this->create($data);
        return $res;
    }
}
