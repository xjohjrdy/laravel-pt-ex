<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JdGetOneShow extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_jd_get_one_show';
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


    public function addOne($data)
    {
        return $this->create($data);
    }

    public function getOne($orderid)
    {
        return $this->where(['orderid' => $orderid])->first();
    }

    public function updateOne($orderid)
    {
        return $this->where(['orderid' => $orderid])->update([
            'is_ptb' => 1
        ]);
    }
}
