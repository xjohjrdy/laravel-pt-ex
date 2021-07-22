<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PretendShopOrdersMaid extends Model
{
    //
    protected $connection = 'app38';
    protected $table = 'lc_shop_orders_pretend_maid';
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
     * 生成商城的分佣记录
     * @param $app_id
     * @param $order_id
     * @param $money
     * @return $this|Model
     */
    public function addMaidLog($app_id, $order_id, $money)
    {
        $res = $this->create([
            'app_id' => $app_id,
            'order_id' => $order_id,
            'money' => $money,
        ]);
        return $res;
    }

    /**
     * 更新假装订单的状态
     * @param $app_id
     * @param $order_id
     * @return bool
     */
    public function updateStatus($app_id, $order_id)
    {
        $res = $this->where([
            'app_id' => $app_id,
            'order_id' => $order_id,
        ])->first();

        if (!$res) {
            return true;
        }

        return $this->where([
            'app_id' => $app_id,
            'order_id' => $order_id,
        ])->update(['status' => 1]);
    }


    /**
     * 获取所有的分佣记录
     * @param $app_id
     * @param int $status （传入status代表特定的状态筛选）
     * @return \Illuminate\Support\Collection
     */
    public function getAllCreditLog($app_id, $status = 99)
    {
        if ($status == 99) {
            return $this->where(['app_id' => $app_id])
                ->get();
        }
        return $this->where(['app_id' => $app_id, 'status' => $status])
            ->get();
    }


    /**
     * 根据APP ID 以及查询条件，统计用户的 预估收入
     * @param $app_id
     * @param array $where
     * @return mixed
     */
    public function getCountMoney($app_id, $where = [])
    {
        $where['app_id'] = $app_id;
        $where['status'] = 0;
        $countMoney = $this->where($where)->sum('money');
        return $countMoney;
    }

    /**
     * 根据APP ID 以及查询条件，统计用户的 预估收入 的详细情况
     * @param $app_id
     * @param array $where
     * @return mixed
     */
    public function getCountPage($app_id, $where = [])
    {
        $where['app_id'] = $app_id;
        //$where['status'] = 0;
        $countMoney = $this->where($where)->orderBy('created_at', 'desc')->whereIn('status', [0, 2])->paginate();
        return $countMoney;
    }


    /*
     * 将一个月前的未审核订单状态全部由0变更成2
     */
    public function clearUseless()
    {
        $end_time = date("Y-m-d H:i:s", strtotime("-6 month"));
        $where = [
            ['created_at', '<', $end_time],
            ['status', '=', 0]
        ];
        return $this->where($where)->update(['status' => 2]);
    }

}
