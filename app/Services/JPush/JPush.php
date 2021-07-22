<?php

namespace App\Services\JPush;

class JPush
{

    static function request_post($url = "", $param = "", $header = "")
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);

        curl_close($ch);
        return $data;
    }

    /**
     * 极光推送 - 发送
     * @state 1.公告  2.工作提醒  3.共享提醒
     * @param string $inform_sign 标记 0忽略 1原生 2网页
     * @param string $inform_url 1=公告 2工单 3新增粉丝 http=网页
     * @param string $inform_data {key，value}
     */
    static function push_user($title, $app_id, $state, $inform_sign = "0", $inform_url = "", $inform_data = "")
    {
        $url = 'https://api.jpush.cn/v3/push';
        $base64 = base64_encode(config('jpush.app_key') . ":" . config('jpush.master_secret'));
        $header = array(
            "Authorization:Basic $base64",
            "Content-Type:application/json"
        );
        $param = [
            'platform' => "all",
            'audience' => [
                'tag' => [$app_id]
            ],
            'notification' => [
                'alert' => $title,
                'android' => [
                    'extras' => ['type' => $state, 'all' => 0, "inform_sign" => $inform_sign, "inform_url" => $inform_url, "inform_data" => $inform_data],
                    'uri_activity' => 'com.qwh.grapebrowser.activity.WelcomeActivity'
                ],
                'ios' => [
                    'extras' => ['type' => $state, 'all' => 0, "inform_sign" => $inform_sign, "inform_url" => $inform_url, "inform_data" => $inform_data],
                ],
            ],
            'options' => [
                'time_to_live' => 60,
                'apns_production' => true,
            ]
        ];
        $param = json_encode($param, true);
        $res = self::request_post($url, $param, $header);
        $res_arr = json_decode($res, true);

    }


    static function push_user_all($title, $state)
    {
        $url = 'https://api.jpush.cn/v3/push';
        $base64 = base64_encode(config('jpush.app_key') . ":" . config('jpush.master_secret'));
        $header = array(
            "Authorization:Basic $base64",
            "Content-Type:application/json"
        );
        $param = '{
				"platform" : "all",
   				"audience" : "all",
				"notification":{
					"android":{
					    "alert":"' . $title . '",
						"extras":{"type":"' . $state . '","all":"' . 1 . '"}
					},
					"ios":{
		                "alert":"' . $title . '",
						"extras":{"type":"' . $state . '","all":"' . 1 . '"}
					}
				},
				"options" : {
					"time_to_live" : 60,"apns_production":true
			 	}
			  }';
        $res = self::request_post($url, $param, $header);
        $res_arr = json_decode($res, true);
    }
}
