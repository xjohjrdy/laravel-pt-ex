<?php

namespace App\Http\Controllers\Tools;

use App\Services\Common\DingAlerts;
use App\Services\Common\QyWxAlerts;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class ToolController extends Controller
{
    public function send(Request $request, QyWxAlerts $qyWxAlerts)
    {
        $cache_key = 'qywx:';

        if ($request->isMethod('get')) {
            $msg = $request->input('msg');
            $to = $request->input('to');
            if (empty($msg)) {
                return $this->getInfoResponse('1001', '消息输入错误');
            }
            $mdkey = md5($msg);

            if (Cache::has($cache_key . $mdkey)) {
                return $this->getInfoResponse('1005', '重复推送');
            }

            $qyWxAlerts->sendByText($msg, $to);

        } else {
            $msg = $request->getContent();
            if (empty($msg)) {
                return $this->getInfoResponse('1001', '消息输入错误');
            }
            $mdkey = md5($msg);

            if (Cache::has($cache_key . $mdkey)) {
                return $this->getInfoResponse('1005', '重复推送');
            }

            $qyWxAlerts->sendByMd($msg);
        }

        Cache::put($cache_key . $mdkey, 1, 30);//重复消息 30分钟只推送一次

        return $this->getResponse('推送完毕');
    }

    public function dingSend(Request $request, DingAlerts $dingAlerts)
    {
        $cache_key = 'dding:';
        $to = $request->input('to');
        $user_agent = $request->userAgent();
//        dd($user_agent);
        if ($request->isMethod('get')) {
            $msg = $request->input('msg');

            if (empty($msg)) {
                return $this->getInfoResponse('1001', '消息输入错误');
            }
            $mdkey = md5($msg);

            if (Cache::has($cache_key . $mdkey)) {
                return $this->getInfoResponse('1005', '重复推送');
            }

            $dingAlerts->sendByText($msg, $to);

        } else {
            $msg = $request->getContent();
            if (empty($msg)) {
                return $this->getInfoResponse('1001', '消息输入错误');
            }
            $mdkey = md5($msg);

            if (Cache::has($cache_key . $mdkey)) {
                return $this->getInfoResponse('1005', '重复推送');
            }

            if ($user_agent == 'Go-http-client/1.1') {
                $arr_msg = json_decode($msg, true);
                if (!empty($arr_msg)) {
                    $msg = '';
                    foreach ($arr_msg as $key => $item) {
                        $msg .= $key . ' : ' . $item . chr(10) . chr(10);
                    }
                }
            }

            $dingAlerts->sendByMd($msg, $to);
        }

        Cache::put($cache_key . $mdkey, 1, 10);//重复消息 10分钟只推送一次

        return $this->getResponse('推送完毕');
    }
}
