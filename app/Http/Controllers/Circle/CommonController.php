<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\CircleActive;
use App\Entitys\App\CircleApply;
use App\Entitys\App\CircleCommonNotify;
use App\Entitys\App\CircleNotify;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\CircleTalk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    /**
     * 获取分享链接内容内容
     */
    public function getUrlForShare(Request $request, CircleRing $circleRing, CircleActive $circleActive)
    {
        $url = "http://xin_new.36qq.com/mobile/auth/register?id=" . $request->id;
        $url_img = 'https://a119112.oss-cn-beijing.aliyuncs.com/circle/images/code.png' . $request->url_img;

        if (empty($request->circle_id)) {
            return $this->getInfoResponse('4004', '没有设定圈子');
        }

        $circle_ring = $circleRing->getById($request->circle_id);
        $circle_active = $circleActive->getLittleList($request->circle_id);

        return view('circle.circle', [
            'url' => $url,
            'url_img' => $url_img,
            'circle_ring' => $circle_ring,
            'circle_active' => $circle_active,
        ]);
    }

    /**
     * 获取链接
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUrlForCircle()
    {
        return $this->getResponse([
            [
                'title' => '本地服务',
                'ico' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E5%8A%A0%E5%85%A5%E5%9C%88%E5%AD%90/%E6%9C%AC%E5%9C%B0%E6%9C%8D%E5%8A%A1@3x.png',
                'url' => 'https://www.58.com',
            ],
            [
                'title' => '租房服务',
                'ico' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E5%8A%A0%E5%85%A5%E5%9C%88%E5%AD%90/%E7%A7%9F%E6%88%BF@3x.png',
                'url' => 'https://www.58.com',
            ],
            [
                'title' => '二手市场',
                'ico' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E5%8A%A0%E5%85%A5%E5%9C%88%E5%AD%90/%E4%BA%8C%E6%89%8B%E5%B8%82%E5%9C%BA@3x.png',
                'url' => 'https://www.58.com',
            ],
            [
                'title' => '优质房产',
                'ico' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E5%8A%A0%E5%85%A5%E5%9C%88%E5%AD%90/%E4%BE%BF%E6%B0%91%E5%B7%A5%E5%85%B7@3x.png',
                'url' => 'https://www.58.com',
            ],
        ]);
    }

    /**
     * 获取公共的数据
     */
    public function getCommonNumber(Request $request, CircleNotify $circleNotify, CircleCommonNotify $circleCommonNotify)
    {
        if (empty($request->app_id)) {
            return $this->getInfoResponse('5000', '缺少用户id');
        }

        $friend_number = $circleNotify->getSpecialMsg($request->app_id)->count();
        $push_number = $circleNotify->getOneAllCount($request->app_id, 3);
        $notify_number = $circleCommonNotify->getAllByAppIdNoRead($request->app_id)->count();
        $chat_number = $circleNotify->getOneAllCount($request->app_id, 2);

        $all = $friend_number + $push_number + $notify_number + $chat_number;

        return $this->getResponse([
            'push_number' => $push_number,
            'friend_number' => $friend_number,
            'notify_number' => $notify_number,
            'chat_number' => $chat_number,
            'all' => $all,
        ]);
    }

    /**
     * 获取单个圈子的数据
     */
    public function getCircleNumber(Request $request, CircleNotify $circleNotify)
    {
        if (empty($request->circle_id)) {
            return $this->getInfoResponse('5000', '缺少圈子id');
        }

        $circle_number = $circleNotify->getOneAllCircleCount($request->circle_id, 3);

        return $this->getResponse([
            'circle_number' => $circle_number,
        ]);
    }
}
