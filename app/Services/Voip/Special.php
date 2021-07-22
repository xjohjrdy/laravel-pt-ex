<?php

namespace App\Services\Voip;


class Special
{

    public function pushD($message)
    {
        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=b86b71545e345ac60a86d76fcdcabe74967deda77911f669742da8b99ad0a437";
        $data = array('msgtype' => 'text', 'text' => array('content' => $message));
        $data_string = json_encode($data);

        $result = $this->request_by_curl($webhook, $data_string);
        return $result;
    }

    public function request_by_curl($remote_server, $post_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
