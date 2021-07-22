<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class SignLog extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_sign_log';

    public $timestamps = false;

    public function check($app_id, $ip2long = 0)
    {
        $m = new SignLog();
        $m->user_id = $app_id;
        $m->date = date('Y-m-d');
        $m->ip = $ip2long;
        $m->create_time = date('Y-m-d H:i:s');
        $m->save();
        return $m->id;
    }
    public function isCheck($app_id)
    {
        return $this->where(['user_id' => $app_id, 'date' => date('Y-m-d', time())])->exists();
    }
    public function isIp($ip2long = 0)
    {
        if (empty($ip2long)) {
            return false;
        }

        $obj_sign = $this->where('ip', $ip2long)->orderByDesc('id')->first();

        if (!empty($obj_sign)) {
            $create_time = $obj_sign->create_time;
            $difference = time() - strtotime($create_time);
            if ($difference < (60 * 30)) {
                return true;
            } else {
                return false;

            }
        } else {
            return false;
        }

    }

    /*
     * 查询该用户是否签到
     */
    public function isSign($app_id)
    {
        $date = date('Y-m-d');
        $int_sign_id = $this->where(['user_id' => $app_id, 'date' => $date])->value('id');
        return $int_sign_id > 0 ? 1 : 0;
    }


}
