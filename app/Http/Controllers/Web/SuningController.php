<?php

namespace App\Http\Controllers\Web;

use App\Entitys\App\SUserCheck;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SuningController extends Controller
{

    public function getIndex(Request $request, Client $client, SUserCheck $userCheck)
    {
        $app_id = 0;
        if (empty($request->app_id)) {
            dd("请关闭我的浏览器，重启以后再试！");
        } else {
            $app_id = $request->app_id;
        }

        if (empty($app_id)) {
            dd("请关闭我的浏览器，重启后再试！");
        }
        $this_url = 'https://fymoon.com.cn/app.html?wid=61a';

        $user_info = $userCheck->getUser($app_id);
        if (empty($user_info)) {
            $change_app_id = $userCheck->encodeId($app_id);

            $list_url = 'https://fymoon.com.cn/v1/taskExtend/autoUserGenerateToken?autoUserName=' . $change_app_id . '&channelCode=61a';
            $res_list = $client->request('get', $list_url, ['verify' => false]);
            $jsonRes = (string)$res_list->getBody();

            $userCheck->addUser($app_id, $jsonRes, $change_app_id);
        } else {
            $jsonRes = $user_info->app_id_info;
            $change_app_id = $user_info->app_id_change;
        }


        $this_url = $this_url . $change_app_id;

        return view('activity.suning', ['copy' => $jsonRes, 'jump' => 'https://t.suning.cn/5aEyJxg', 'this_url' => $this_url, 'app_id' => $app_id, 'change_app_id' => $change_app_id]);

    }
}
