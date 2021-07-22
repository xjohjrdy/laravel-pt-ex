<?php

namespace App\Http\Controllers\Recharge;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DisplayController extends Controller
{
    /**
     * 展示注意
     */
    public function getNotice()
    {
        return view('recharge.notice');
    }

    /**
     * 展示注意
     */
    public function getRecharge()
    {
        $json = [
            [
                "title"=>"充值金额购买",
                "icon"=>"https://a119112.oss-cn-beijing.aliyuncs.com/recharge/icon_putaobi%402x.png",
                "type"=>"1",
            ],
        ];
        return $this->getResponse($json);
    }
}
