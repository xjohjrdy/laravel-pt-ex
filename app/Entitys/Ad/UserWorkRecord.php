<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;
class UserWorkRecord extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_orange_work_record';
    public $timestamps = false;

    /**
     *
     * 兼容旧用户
     * 使用订单id去找到
     * @param $order_id
     * @return Model|null|static
     */
    public function getRecordByOrderId($order_id)
    {
        $res = $this->where(['orderid' => $order_id])->orderByDesc('add_time')->first();
        return $res;
    }

    /**
     * 通过id查找用户
     * @param $id
     * @return Model|null|static
     */
    public function getById($id)
    {
        $res = $this->where(['id' => $id])->orderByDesc('add_time')->first();
        return $res;
    }

    /**
     * 更新用户的图片
     * @param $id
     * @param $pics
     * @return bool
     */
    public function updateById($id, $pics)
    {
        $res = $this->where(['id' => $id])->update(['pics' => $pics]);
        return $res;
    }

    /**
     *
     * 增加记录
     * @param $uid
     * @param $work_id
     * @param $deal_uid
     * @return int
     */
    public function addRecord($uid, $work_id, $deal_uid)
    {
        $id = $this->insertGetId([
            'uid' => $uid,
            'orderid' => $work_id,
            'deal_uid' => $deal_uid,
            'add_time' => time(),
        ]);
        return $id;
    }

    /**
     *
     * 通过生成的合同号来查找是否重复
     * @param $id
     * @return Model|null|static
     */
    public function getRecordByDeal($id)
    {
        $res = $this->where(['deal_uid' => $id])->first();
        return $res;
    }
}
