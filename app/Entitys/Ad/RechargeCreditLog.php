<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RechargeCreditLog extends Model
{
    //
    protected $connection = 'a1191125678';
    protected $table = 'pre_aljbgp_credit_log';

    /**
     *
     * 获取用户分佣记录，默认为全部
     * @param $uid
     * @param $start
     * @param $end
     * @param int $limit
     * @return array
     */
    public function getCreditLogById($uid, $start, $end, $limit = 20)
    {
        $credit = $this->where([
            'uid' => $uid
        ])->Where('dateline', '>', '1577808000')->whereBetween('dateline', [$start, $end])->orderBy('logid', 'desc')->paginate($limit);
        return $credit->items();
    }

    /**
     *
     * @param $uid
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getCreditLogBySum($uid, $start, $end)
    {
        $credit = $this->where([
            'uid' => $uid
        ])->Where('dateline', '>', '1577808000')->whereBetween('dateline', [$start, $end])->get();
        return $credit->sum('money');
    }

    /**
     * 获取分佣总值
     * @param $uid
     * @return mixed
     */
    public function getCreditLogByIdSum($uid)
    {
        $sum = $this->where([
            'uid' => $uid
        ])->Where('dateline', '>', '1577808000')->sum('money');
        return $sum;
    }
    /*
     * 通过uid得到收益
     */
    public function getCreditMoney($uid, $time)
    {
        $res_data = DB::connection('a1191125678')
            ->select("SELECT SUM(money/10 ) as money FROM pre_aljbgp_credit_log WHERE uid = {$uid} AND dateline > {$time}");
        return $res_data;
    }
}
