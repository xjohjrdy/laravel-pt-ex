<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SUserCheck extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_s_user_check';

    private $key_wwb = 'PQWE67RTYUIOA58SDFGH34JKLZX29CVBNM';
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


    public function getUser($app_id)
    {
        return $this->where(['app_id' => $app_id])->first();
    }

    public function addUser($app_id, $info, $change)
    {
        return $this->create([
            'app_id' => $app_id,
            'app_id_change' => $change,
            'app_id_info' => $info,
        ]);
    }

    /*
    * 编码id成邀请码
    */
    public function encodeId($t_id)
    {
        $key = $this->key_wwb;
        $code_id = '0000000';
        for ($i = 6; $i >= 0; $i--) {
            $code_id[$i] = $key[$t_id % 23];
            $t_id = intval($t_id / 23);
        }
        if ($t_id) {
            return false;
        }
        return $code_id;
    }

    /*
     * 解码邀请码为id
     */
    public function decodeId($t_co)
    {
        $key = $this->key_wwb;
        $t_id = 0;
        for ($i = 5; $i >= 0; $i--) {
            $t_id += strpos($key, $t_co[$i]) * pow(23, 5 - $i);
        }
        return $t_id;
    }
}
