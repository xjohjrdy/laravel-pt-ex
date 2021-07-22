<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JdTestWh extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_test_jd_wh';
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
     * 拿到所有的订单
     * @param $positionId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getMyOrders($positionId)
    {
        return $this->where(['positionId' => $positionId])->paginate(20, ['orderTime', 'skuName', 'order_remark']);
    }
}
