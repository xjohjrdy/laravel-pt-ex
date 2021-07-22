<?php

namespace App\Services\WuHang;


use App\Entitys\App\AppUserInfo;
use Illuminate\Support\Facades\DB;

class Add
{
    //
    /**
     *
     */
    public function kill($app_id, $maid)
    {
        $app_user_info = new AppUserInfo();

        $app_user = $app_user_info->getUserInfo($app_id);

        if ($app_user->order_can_apply_amount < $maid) {
            return $app_user->where(['id' => $app_id])->update(['order_can_apply_amount' => 0]);
        } else {

            $res = $app_user->order_can_apply_amount - $maid;

            return $app_user->where(['id' => $app_id])->update([
                'order_can_apply_amount' => $res
            ]);
        }
    }
}
