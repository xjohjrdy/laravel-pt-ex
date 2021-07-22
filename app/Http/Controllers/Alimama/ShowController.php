<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\MedicalCanShow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowController extends Controller
{

    public function getRegular(MedicalCanShow $medicalCanShow)
    {
        $content = $medicalCanShow->getOneThing(99);
        return view('medical.regular', ['content' => $content->context]);
    }

    public function getRule()
    {
        return view('alimama.rule');
    }

    public function getOrderRegular()
    {
        return view('alimama.order');
    }

    public function getVideoUrl()
    {
        return $this->getResponse([
            'video' => 'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/te/%E8%A7%86%E9%A2%91/%E6%B7%98%E8%B4%AD%E7%89%A9%E6%8A%A5%E9%94%80%E8%A7%86%E9%A2%91.mp4'
        ]);
    }
}
