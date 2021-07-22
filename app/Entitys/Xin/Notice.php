<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_notice';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /*
     * 得到公告列表
     */
    public function getList()
    {
        return $this->orderByDesc('create_time')->paginate(10, ['id', 'title', 'profile', 'icon', 'create_time']);
//        return $this->orderByDesc('create_time')->limit(10)->get(['id', 'title', 'profile', 'icon', 'create_time']);
    }
}
