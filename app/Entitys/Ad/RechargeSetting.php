<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class RechargeSetting extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_aljbgp_groupsetting';

    /**
     * 获取购买列表(all)
     * @param $user_type
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getRechargeSetting($user_type)
    {
        $setting = $this->orderBy('displayorder', 'asc')->get();
        if ($user_type == 3) {
            $setting = $this->where(['price' => 10])->orWhere(['price' => 2700])->orderBy('displayorder', 'asc')->get();
        }
        if ($user_type == 8) {
            $setting = $this->where(['price' => 10])->orWhere(['price' => 2200])->orderBy('displayorder', 'asc')->get();
        }
        if ($user_type == 0) {
            $setting = $this->where(['price' => 10])->orWhere(['price' => 800])->orWhere(['price' => 3000])->orderBy('displayorder', 'asc')->get();
        }
        if ($user_type == 1) {
            $setting = $this->where(['price' => 10])->orderBy('displayorder', 'asc')->get();
        }

        foreach ($setting as $k => $v) {
            if ($v->id == 74 || $v->id == 75 || $v->id == 76 || $v->id == 77) {
                unset($setting[$k]);
            }
        }

        return $setting;
    }
}
