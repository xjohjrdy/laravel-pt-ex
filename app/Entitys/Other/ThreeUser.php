<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class ThreeUser  用户账户表
 * @package App\Entitys\Other
 */
class ThreeUser extends Model
{
    //lc_test_jd_wh
    protected $connection = 'db001';
    protected $table = 'lc_three_user';
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
     * @param $app_id 获取用户可提现金额
     * @return ThreeUser|Model|null
     */
    public function getUserMoney($app_id){
        $account = $this->where(['app_id' => $app_id])->first(['app_id', 'money', 'alipay', 'salary_account']);
        if (!$account) {
            $this->create([
                'app_id' => $app_id,
                'money' => 0,
            ]);
            $account = $this->where(['app_id' => $app_id])->first(['app_id', 'money', 'alipay', 'salary_account']);
        }
        return $account;
    }

    /**
     * 用户
     */
    public function getUser($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->first();
        if (empty($res)) {
            $this->create([
                'app_id' => $app_id,
            ]);
        }
        return $this->where(['app_id' => $app_id])->first();
    }

    /**
     * @param $app_id
     * @param $money
     */
    public function subMoney($app_id, $money)
    {
        return $this->where(['app_id' => $app_id])->update(['money' => DB::raw("money - " . $money)]);
    }

    /**
     * @param $app_id
     * @param $money
     */
    public function addMoney($app_id, $money)
    {
        return $this->where(['app_id' => $app_id])->update(['money' => DB::raw("money + " . $money)]);
    }
}
