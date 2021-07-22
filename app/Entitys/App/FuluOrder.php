<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuluOrder extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_fulu_order';
    use SoftDeletes;

    const NO_PAY = '101'; // 待支付
    const PAY_WAIT = '102'; // 支付成功等待下单回调
    const PAY_SUCCESS = '201'; // 支付成功下单成功
    const PAY_FAIL = '202'; // 支付成功下单失败
    const REFUND_WAIT = '301'; // 退款申请
    const REFUND_SUCCESS = '302'; // 退款成功
    const REFUND_FAIL = '303'; // 退款失败
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
}
