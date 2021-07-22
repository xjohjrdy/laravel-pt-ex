<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class ExchangeGrapeOrder extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_xigua_t_tixian';

    /**
     * @param $uid
     * @return bool
     */
    public function getList($uid)
    {
        $listOrder = $this->where('uid', $uid)->orderByDesc('crts')->paginate(10);
        if (empty($listOrder)) {
            return false;
        }
        return $listOrder->toArray();
    }

    /**
     * @param $uid
     * @return bool
     */
    public function getCount($uid)
    {
        $listOrder = $this->where(['uid' => $uid, 'status' => 1])->sum('amount');
        if (empty($listOrder)) {
            return 0;
        }
        return $listOrder;
    }
}
