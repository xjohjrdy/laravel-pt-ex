<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinTurntableGetLog extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_coin_turntable_get_log';
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

    /*
     * 中奖记录查询
     */
    public function getTurntablePrizeLog($app_id)
    {
        return $this->leftJoin('lc_coin_turntable_prize', 'lc_coin_turntable_get_log.award_id', '=', 'lc_coin_turntable_prize.id')
            ->where(['lc_coin_turntable_get_log.app_id' => $app_id])
            ->where('lc_coin_turntable_prize.type', '>', 1)
            ->orderByDesc('lc_coin_turntable_get_log.get_time')
            ->paginate(20, ['lc_coin_turntable_get_log.id', 'lc_coin_turntable_prize.title',
                'lc_coin_turntable_prize.img', 'lc_coin_turntable_prize.desc',
                'lc_coin_turntable_get_log.get_time', 'lc_coin_turntable_get_log.award_id']);
    }

    public function getRealityGoodInfo($app_id, $type)
    {
        return $this->leftJoin('lc_coin_turntable_prize', 'lc_coin_turntable_get_log.award_id', '=', 'lc_coin_turntable_prize.id')
            ->leftJoin('lc_coin_turntable_orders', 'lc_coin_turntable_get_log.id', '=', 'lc_coin_turntable_orders.get_log_id')
            ->where(['lc_coin_turntable_get_log.app_id' => $app_id])
            ->where(['lc_coin_turntable_prize.type' => $type])
            ->orderByDesc('lc_coin_turntable_get_log.get_time')
            ->paginate(20, [
                'lc_coin_turntable_get_log.id', 'lc_coin_turntable_prize.title',
                'lc_coin_turntable_prize.img', 'lc_coin_turntable_prize.desc',
                'lc_coin_turntable_get_log.get_time', 'lc_coin_turntable_get_log.award_id',
                'lc_coin_turntable_orders.status', 'lc_coin_turntable_orders.created_at'
            ]);
    }

    /*
     * 根据实物中奖记录id得到订单信息
     */
    public function getOrderInfoById($app_id, $win_prize_id)
    {
        return $this->leftJoin('lc_coin_turntable_prize', 'lc_coin_turntable_get_log.award_id', '=', 'lc_coin_turntable_prize.id')
            ->leftJoin('lc_coin_turntable_orders', 'lc_coin_turntable_get_log.id', '=', 'lc_coin_turntable_orders.get_log_id')
            ->where(['lc_coin_turntable_get_log.app_id' => $app_id, 'lc_coin_turntable_get_log.id' => $win_prize_id, 'lc_coin_turntable_prize.type' => 1])
            ->first(['lc_coin_turntable_orders.id', 'lc_coin_turntable_get_log.app_id', 'lc_coin_turntable_prize.title', 'lc_coin_turntable_prize.desc',
                'lc_coin_turntable_prize.img', 'lc_coin_turntable_prize.luck_draw_get', 'lc_coin_turntable_orders.status',
                'lc_coin_turntable_orders.order_no', 'lc_coin_turntable_orders.created_at', 'lc_coin_turntable_orders.track_no'
                , 'lc_coin_turntable_orders.receiving_address', 'lc_coin_turntable_orders.address_phone', 'lc_coin_turntable_orders.address_user']);
    }
}
