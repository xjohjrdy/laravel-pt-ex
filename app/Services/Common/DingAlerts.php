<?php

namespace App\Services\Common;

use App\Services\Dplus\Dplus;
use App\Services\JPush\JPush;
use Illuminate\Support\Facades\Redis;

class DingAlerts
{
    // 频率 60:20
    private $robot_list_001 = [
        [
            'https://oapi.dingtalk.com/robot/send?access_token=5a32aa0bc0cfbdcf9a7afc3c4e112e9a2c367256e54abaede619792b45f4ff7f',
            'SEC160a64d9393ecd820af79436a16e6a026caff427b4c7143425bfe3f32a54ba9a'
        ],
        [
            'https://oapi.dingtalk.com/robot/send?access_token=f4f157a1c68d5f5675ab61d6b3e7ff068fbb07d9bd8d37d3d8f3b937cf942c78',
            'SEC7bcbea23154ca0cab6e0ed3ff4e3bae2a1ae47ba36b2da72b4d2bf1a0739cf55'],
        [
            'https://oapi.dingtalk.com/robot/send?access_token=8207c0b6e6f9eec0057a04c101428b81e6b9bec82dff81919e7760b740acb9fe',
            'SEC2f6f2ef535e46ce4694530ed14bf0310f36c698210871a7a3670460c4074cf75'
        ],
    ];

    private $robot_error = [
        'https://oapi.dingtalk.com/robot/send?access_token=9d64c4b46743837527fb78e908f719e87ff1069edbcd95146befdb14209b85aa',
        'SEC98ef9eccdf71440399b337c2d3e060bbef8ab02c31c0f2043f313aa93c74027d'
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
                'content' => $text
            ],
            'at' => [
                "isAtAll" => empty($mentioned) ? false : true
            ]
        ];
        return $this->send($arr_msg);
    }

    /**
     * 标题
     * # 一级标题
     * ## 二级标题
     * ### 三级标题
     * #### 四级标题
     * ##### 五级标题
     * ###### 六级标题
     *
     * 引用
     * > A man who stands for nothing will fall for anything.
     *
     * 文字加粗、斜体
     * *bold**
     * *italic*
     *
     * 链接
     * [this is a link](http://name.com)
     *
     * 图片
     * ![](http://name.com/pic.jpg)
     *
     * 无序列表
     * - item1
     * - item2
     *
     * 有序列表
     * 1. item1
     * 2. item2
     * @param $md
     * @param bool $mentioned
     * @return bool
     */
    public function sendByMd($md, $mentioned = false)
    {
        $arr_msg = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => '消息通知',
                'text' => $md,
            ],
            'at' => [
                "isAtAll" => empty($mentioned) ? false : true
            ]
        ];
        return $this->send($arr_msg);
    }

    private function send($arr_msg)
    {
        $response = '';
        try {
            list($url, $accessKey) = $this->robot_list_001[array_rand($this->robot_list_001)];
            $timestamp = time() * 1000;
            $sign = $this->sign($accessKey, $timestamp);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                @CURLOPT_URL => $url . "&timestamp={$timestamp}&sign={$sign}",
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
                @$this->sendErr(var_export($response, true));
                return false;
            }

            return true;
        } catch (\Exception $e) {
            @$this->sendErr(var_export($response, true) . $e->getMessage());
            return false;
        }
    }

    private function sendErr($msg)
    {
        $arr_msg = [
            'msgtype' => 'text',
            'text' => [
                'content' => $msg
            ],
            'at' => [
                "isAtAll" => true
            ]
        ];

        $curl = curl_init();
        list($url, $accessKey) = $this->robot_error;
        $timestamp = time() * 1000;
        $sign = $this->sign($accessKey, $timestamp);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . "&timestamp={$timestamp}&sign={$sign}",
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
//        echo $response;

        return true;
    }

    private function sign($accessKey, $timestamp)
    {
        $s = hash_hmac('sha256', $timestamp . chr(10) . $accessKey, $accessKey, true);
        return urlEncode(base64_encode($s));
    }

}