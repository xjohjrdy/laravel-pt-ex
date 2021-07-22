<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JdMoneyGetIn extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_jd_money_get_in';
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
    /*
     * 得到jd数据
     */
    public function getJdData($app_id)
    {
        return $this->where('app_id', (int)$app_id)->first();
    }
}
