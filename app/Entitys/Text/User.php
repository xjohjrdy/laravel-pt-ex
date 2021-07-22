<?php

namespace App\Entitys\Text;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_user';

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
     * 获得某天的活跃度详情
     * @param $uid
     * @param $time
     * @return Model|null|static
     */
    public function getUser()
    {
        $res = $this->where('id',1)->get();
        return $res;
    }
}
