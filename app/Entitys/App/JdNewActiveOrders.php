<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JdNewActiveOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_jd_orders_new_active';
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
     * 插
     * @param $data
     * @return $this|Model
     */
    public function addInfo($data)
    {
        return $this->create($data);
    }
}
