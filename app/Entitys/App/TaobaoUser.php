<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TaobaoUser extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_user';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

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
     * 获取用户金额
     */
    public function getUserMoney($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->first();
        if (empty($res)) {
            $this->create([
                'app_id' => $app_id,
            ]);
            return 0;
        }
        return $res['money'];
    }

    /**
     * @param $app_id
     * @param $money
     */
    public function subMoney($app_id, $money)
    {
        return $this->where(['app_id' => $app_id])->update(['money' => DB::raw("money - " . $money)]);
    }
}
