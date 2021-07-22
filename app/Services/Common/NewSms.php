<?php

namespace App\Services\Common;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class NewSms
{
    /**
     * 发送验证码
     * @return mixed
     */
    public function SendSms($phone, $code, $sms_code = 86)
    {

        if ($sms_code == 86) {
            $url_send_sms = "https://smsapp.wlwx.com/sendSms";
            $cust_code = "176001";
            $cust_pwd = "BPGGVDMO6W";
            $content = "【我的浏览器】" . "您手机号验证码是" . $code . "。如不是您本人申请，请忽略此短信。";
            $destMobiles = $phone;
            $uid = "";
            $sp_code = "";
            $need_report = "yes";
            $sign = $content . $cust_pwd;
            $sign = md5($sign);
            $ch = curl_init();
            /* 设置验证方式 */
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8'));
            /* 设置返回结果为流 */
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            /* 设置超时时间*/
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            /* 设置通信方式 */
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $data = array('cust_code' => $cust_code, 'sp_code' => $sp_code, 'content' => $content, 'destMobiles' => $destMobiles, 'uid' => $uid, 'need_report' => $need_report, 'sign' => $sign);
            $json_data = json_encode($data);

            curl_setopt($ch, CURLOPT_URL, $url_send_sms);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

            $resp_data = curl_exec($ch);
            curl_close($ch);

            $date = date('Ym');
            Storage::disk('local')->append('callback_document/send_sms/temp_' . $date . '.txt', date("Y-m-d H:i:s") . '#### ' . var_export($phone . '-' . $code . '-' . $resp_data, true) . ' ####');

            return $resp_data;
        } else {
            $time = time();

            $sdkappid = config('sms_config.qq_sdkappid');
            $appkey = config('sms_config.qq_appkey');
            $random = time() . rand(1, 100);

            $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid={$sdkappid}&random={$random}";
            $param = [
                'tel' => [
                    'nationcode' => $sms_code,
                    'mobile' => $phone,
                ],
                'type' => 0,
                'msg' => "您的手机验证码是：{$code}，6分钟内有效。",
                'sig' => hash("sha256", "appkey=$appkey&random=$random&time=$time&mobile=$phone"),
                'time' => $time,
                'extend' => '',
                'ext' => '',
            ];
            $param = json_encode($param, true);
            $data = CurlTool::request_post($url, $param);
            $data = json_decode($data, true);
            return $data['result'] == 0 ? true : false;
        }
    }


    public function SendMsg($phone, $msg)
    {
        $url_send_sms = "https://smsapp.wlwx.com/sendSms";
        $cust_code = "176001";
        $cust_pwd = "BPGGVDMO6W";
        $content = "【开发者通知】🍇🍇\r\n" . $msg . "\r\n" . date("Y-m-d H:i:s");
        $destMobiles = $phone;
        $uid = "";
        $sp_code = "";
        $need_report = "yes";
        $sign = $content . $cust_pwd;
        $sign = md5($sign);
        $ch = curl_init();
        /* 设置验证方式 */
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8'));
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = array('cust_code' => $cust_code, 'sp_code' => $sp_code, 'content' => $content, 'destMobiles' => $destMobiles, 'uid' => $uid, 'need_report' => $need_report, 'sign' => $sign);
        $json_data = json_encode($data);

        curl_setopt($ch, CURLOPT_URL, $url_send_sms);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        $resp_data = curl_exec($ch);
        curl_close($ch);

        return $resp_data;
    }

}
