<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopVoipOrders extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_voip_orders';
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
     * 获取充值卡信息
     * @param $card_name
     * @return Model|null|static
     */
    public function getByCardName($card_name)
    {
        $res = $this->where(['card_name' => $card_name, 'type' => '1', 'status' => '0'])->first();
        return $res;
    }

    /**
     * 把卡用了
     * @param $id
     * @return bool
     */
    public function useCard($id, $phone = 1)
    {
        $res = $this->where(['id' => $id])->update(['status' => '1', 'phone' => $phone]);
        return $res;
    }
}
