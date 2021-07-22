<?php

namespace App\Services\Common;

use App\Services\Dplus\Dplus;
use App\Services\JPush\JPush;
use Illuminate\Support\Facades\Redis;

class QyWxAlerts
{
    // 频率 60:20
    private $robot_list_001 = [
        'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=f915a6b5-aeaa-4a78-8d80-7688d3f5ef0d',
        'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=245e6b66-cd76-47ee-a9a5-0410e1d90769',
        'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=36f43a76-e332-4739-b085-5c88fca6ea8f',
        'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=5b630ea3-43b3-4f32-b1a8-bfc9ce258697',
        'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=d10e6764-a6e8-41c6-a6b1-82bd72ec0a9a',
        'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=15f36062-fc46-45c5-9e74-65f7d8f2c2db',
        'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=7bfbd50c-b0c0-4773-a4b4-167febc3c133',
        'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=615f9c63-1c47-4af4-a5cd-996966a477c9'
    ];


    /**
     * @param $text
     * @param array $mentioned ['@all']
     * @return bool
     */
    public function sendByText($text, $mentioned = false)
    {
        $arr_msg = [
            'msgtype' => 'text',
            'text' => [
                'content' => $text,
                "mentioned_mobile_list" => $mentioned ? ['@all']:[]
            ]
        ];
        return $this->send($arr_msg);
    }

    /**
     * # 标题一
     * ## 标题二
     * ### 标题三
     * #### 标题四
     * ##### 标题五
     * ###### 标题六
     * **bold**
     * [这是一个链接](http://work.weixin.qq.com/api/doc)
     * `code`
     * > 引用文字
     * <font color="info">绿色</font>
     * <font color="comment">灰色</font>
     * <font color="warning">橙红色</font>
     * @param $md
     * @return bool
     */
    public function sendByMd($md)
    {
        $arr_msg = [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $md,
            ]
        ];
        return $this->send($arr_msg);
    }

    private function send($arr_msg)
    {
        $response = '';
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                @CURLOPT_URL => $this->robot_list_001[array_rand($this->robot_list_001)],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($arr_msg),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $res_data = json_decode($response, true);
            if ($res_data['errcode'] != 0) {
                @$this->sendErr(var_export($response));
                return false;
            }

            return true;
        } catch (\Exception $e) {
            @$this->sendErr(var_export($response) . $e->getMessage());
            return false;
        }
    }

    private function sendErr($msg)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=30f025f2-5584-4fa4-8e03-1c8ece11331c",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n    \"msgtype\": \"text\",\n    \"{$msg}\": {\n        \"content\": \"test\",\n        \"mentioned_mobile_list\": [\n            \"@all\"\n        ]\n    }\n}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
//        echo $response;

        return true;
    }


}