<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ShopOrdersMaid extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_orders_maid';
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
     * 获取所有的分佣记录
     * @param $app_id
     * @param int $is_page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getAllCreditLog($app_id, $is_page = 0)
    {
        if ($is_page) {
            return $this->where(['app_id' => $app_id])->orderByDesc('updated_at')
                ->paginate();
        }
        return $this->where(['app_id' => $app_id])->orderByDesc('updated_at')
            ->get();
    }
    /*
     * 商城分佣收益
     */
    public function countMoney($app_id, $time)
    {
        $res_data = DB::connection('app38')
            ->select("SELECT SUM(money/10 ) as money FROM lc_shop_orders_maid WHERE app_id = {$app_id} and created_at > '{$time}'");
            return $res_data;
    }
}
