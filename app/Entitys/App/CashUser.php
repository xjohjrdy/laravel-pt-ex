<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashUser extends Model
{
    //
    protected $connection = 'app38';
    protected $table = 'lc_cash_user_info';
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
     * 更新创建
     * @param $app_id
     * @param $target
     * @param $type
     * @param $status
     * @return Model
     */
    public function addOrChange($app_id, $target, $type, $status)
    {
        //
        $this->where([
            'app_id' => $app_id,
            'type' => $type,
        ])->update([
            'status' => 0,
        ]);

        $res = $this->updateOrCreate([
            'target' => $target,
            'type' => $type,
        ], [
            'app_id' => $app_id,
            'target' => $target,
            'type' => $type,
            'status' => $status,
        ]);
        return $res;
    }

    /**
     * 银行卡校验
     * @param $card
     * @return string
     */
    public function bank_check($card)
    {
        $len = strlen($card);
        $all = [];
        $sum_odd = 0;
        $sum_even = 0;
        for ($i = 0; $i < $len; $i++) {
            $all[] = substr($card, $len - $i - 1, 1);
        }
        //all 里的偶数key都是我们要相加的奇数位
        for ($k = 0; $k < $len; $k++) {
            if ($k % 2 == 0) {
                $sum_odd += $all[$k];
            } else {
                //奇数key都是要相加的偶数和
                if ($all[$k] * 2 >= 10) {
                    $sum_even += $all[$k] * 2 - 9;
                } else {
                    $sum_even += $all[$k] * 2;
                }
            }
        }
        $total = $sum_odd + $sum_even;
        if ($total % 10 == 0) {
            return true;
        } else {
            return false;
        }
    }
}
