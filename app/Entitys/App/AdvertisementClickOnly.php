<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvertisementClickOnly extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_advertisement_click_only';
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
     * add record
     * @param $data
     * @return $this|Model
     */
    public function addRecord($data)
    {
        return $this->create($data);
    }

    /**
     * ip count
     * @param $ip
     * @return int
     */
    public function getIpSum($ip, $time)
    {
        return $this->where(['ip' => $ip, 'time' => (string)$time])->count();
    }

    /**
     * mac sum
     * @param $mac_ip
     * @param $time
     * @return int
     */
    public function getMacSum($mac_ip, $time)
    {
        return $this->where(['mac_ip' => $mac_ip, 'time' => (string)$time])->count();
    }
}
