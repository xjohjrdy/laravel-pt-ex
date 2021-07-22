<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class AlwaysOnline extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_always_online';

    /**
     * 获得排队人数最少的客服id
     */
    public function getLeastOnline()
    {
        $res = $this->orderBy('connect_number')->first(['id', 'customer_name', 'customer_id', 'connect_number']);
        return $res;
    }

    /**
     * 根据id获取客服id
     * @param $id
     * @return Model|null|static
     */
    public function getOnlineById($id)
    {
       $res = $this->where(['id'=>$id])->first();
       return $res;
    }

    /**
     * 连接数增加、减少
     * @param int $type
     * @return int
     */
    public function connectNumber($type = 1)
    {
        $res = 0;
        if ($type == 1) {
            $this->increment("connect_number", 1);
            $res = $this->increment("all_number", 1);
        } elseif ($type == 2) {
            $this->decrement("connect_number", 1);
        }
        return $res;
    }
}
