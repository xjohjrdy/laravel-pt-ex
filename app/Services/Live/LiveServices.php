<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/06/12
 * Time: 11:30
 */

namespace App\Services\Live;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class LiveServices
{

    private $key = '9043939b1bf741b63cd8c57b2b79949deffc3302b75604668fe13d70f2de5aa0';
    private $sdkappid = 1400380722;
    private $url = 'https://console.tim.qq.com';


    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param domain 您用来推流的域名
     *        streamName 您用来区别不同推流地址的唯一流名称
     *        key 安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url
     */
    function getPushUrl($domain, $streamName, $key = null, $time = null)
    {
        if ($key && $time) {
            $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
            //txSecret = MD5( KEY + streamName + txTime )
            $txSecret = md5($key . $streamName . $txTime);
            $ext_str = "?" . http_build_query(array(
                    "txSecret" => $txSecret,
                    "txTime" => $txTime
                ));
        }
        return "rtmp://" . $domain . "/live/" . $streamName . (isset($ext_str) ? $ext_str : "");
    }

    /**
     * 用于 url 的 base64 encode
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $string 需要编码的数据
     * @return string 编码后的base64串，失败返回false
     * @throws \Exception
     */
    private function base64_url_encode($string)
    {
        static $replace = Array('+' => '*', '/' => '-', '=' => '_');
        $base64 = base64_encode($string);
        if ($base64 === false) {
            throw new \Exception('base64_encode error');
        }
        return str_replace(array_keys($replace), array_values($replace), $base64);
    }

    /**
     * 用于 url 的 base64 decode
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $base64 需要解码的base64串
     * @return string 解码后的数据，失败返回false
     * @throws \Exception
     */
    private function base64_url_decode($base64)
    {
        static $replace = Array('+' => '*', '/' => '-', '=' => '_');
        $string = str_replace(array_values($replace), array_keys($replace), $base64);
        $result = base64_decode($string);
        if ($result == false) {
            throw new \Exception('base64_url_decode error');
        }
        return $result;
    }

    /**
     * 使用 hmac sha256 生成 sig 字段内容，经过 base64 编码
     * @param $identifier 用户名，utf-8 编码
     * @param $curr_time 当前生成 sig 的 unix 时间戳
     * @param $expire 有效期，单位秒
     * @param $base64_userbuf base64 编码后的 userbuf
     * @param $userbuf_enabled 是否开启 userbuf
     * @return string base64 后的 sig
     */
    private function hmacsha256($identifier, $curr_time, $expire, $base64_userbuf, $userbuf_enabled)
    {
        $content_to_be_signed = "TLS.identifier:" . $identifier . "\n"
            . "TLS.sdkappid:" . $this->sdkappid . "\n"
            . "TLS.time:" . $curr_time . "\n"
            . "TLS.expire:" . $expire . "\n";
        if (true == $userbuf_enabled) {
            $content_to_be_signed .= "TLS.userbuf:" . $base64_userbuf . "\n";
        }
        return base64_encode(hash_hmac('sha256', $content_to_be_signed, $this->key, true));
    }

    /**
     * 生成签名。
     *
     * @param $identifier 用户账号
     * @param int $expire 过期时间，单位秒，默认 180 天
     * @param $userbuf base64 编码后的 userbuf
     * @param $userbuf_enabled 是否开启 userbuf
     * @return string 签名字符串
     * @throws \Exception
     */
    private function __genSig($identifier, $expire, $userbuf, $userbuf_enabled)
    {
        $curr_time = time();
        $sig_array = Array(
            'TLS.ver' => '2.0',
            'TLS.identifier' => strval($identifier),
            'TLS.sdkappid' => intval($this->sdkappid),
            'TLS.expire' => intval($expire),
            'TLS.time' => intval($curr_time)
        );

        $base64_userbuf = '';
        if (true == $userbuf_enabled) {
            $base64_userbuf = base64_encode($userbuf);
            $sig_array['TLS.userbuf'] = strval($base64_userbuf);
        }

        $sig_array['TLS.sig'] = $this->hmacsha256($identifier, $curr_time, $expire, $base64_userbuf, $userbuf_enabled);
        if ($sig_array['TLS.sig'] === false) {
            throw new \Exception('base64_encode error');
        }
        $json_str_sig = json_encode($sig_array);
        if ($json_str_sig === false) {
            throw new \Exception('json_encode error');
        }
        $compressed = gzcompress($json_str_sig);
        if ($compressed === false) {
            throw new \Exception('gzcompress error');
        }
        return $this->base64_url_encode($compressed);
    }


    /**
     * 生成签名
     *
     * @param $identifier 用户账号
     * @param int $expire 过期时间，单位秒，默认 180 天
     * @return string 签名字符串
     * @throws \Exception
     */
    public function genSig($identifier, $expire = 86400 * 180)
    {
        return $this->__genSig($identifier, $expire, '', false);
    }

    /**
     * 带 userbuf 生成签名。
     * @param $identifier 用户账号
     * @param int $expire 过期时间，单位秒，默认 180 天
     * @param string $userbuf 用户数据
     * @return string 签名字符串
     * @throws \Exception
     */
    public function genSigWithUserBuf($identifier, $expire, $userbuf)
    {
        return $this->__genSig($identifier, $expire, $userbuf, true);
    }


    /**
     * 验证签名。
     *
     * @param string $sig 签名内容
     * @param string $identifier 需要验证用户名，utf-8 编码
     * @param int $init_time 返回的生成时间，unix 时间戳
     * @param int $expire_time 返回的有效期，单位秒
     * @param string $userbuf 返回的用户数据
     * @param string $error_msg 失败时的错误信息
     * @return boolean 验证是否成功
     * @throws \Exception
     */
    private function __verifySig($sig, $identifier, &$init_time, &$expire_time, &$userbuf, &$error_msg)
    {
        try {
            $error_msg = '';
            $compressed_sig = $this->base64_url_decode($sig);
            $pre_level = error_reporting(E_ERROR);
            $uncompressed_sig = gzuncompress($compressed_sig);
            error_reporting($pre_level);
            if ($uncompressed_sig === false) {
                throw new \Exception('gzuncompress error');
            }
            $sig_doc = json_decode($uncompressed_sig);
            if ($sig_doc == false) {
                throw new \Exception('json_decode error');
            }
            $sig_doc = (array)$sig_doc;
            if ($sig_doc['TLS.identifier'] !== $identifier) {
                throw new \Exception("identifier dosen't match");
            }
            if ($sig_doc['TLS.sdkappid'] != $this->sdkappid) {
                throw new \Exception("sdkappid dosen't match");
            }
            $sig = $sig_doc['TLS.sig'];
            if ($sig == false) {
                throw new \Exception('sig field is missing');
            }

            $init_time = $sig_doc['TLS.time'];
            $expire_time = $sig_doc['TLS.expire'];

            $curr_time = time();
            if ($curr_time > $init_time + $expire_time) {
                throw new \Exception('sig expired');
            }

            $userbuf_enabled = false;
            $base64_userbuf = '';
            if (isset($sig_doc['TLS.userbuf'])) {
                $base64_userbuf = $sig_doc['TLS.userbuf'];
                $userbuf = base64_decode($base64_userbuf);
                $userbuf_enabled = true;
            }
            $sigCalculated = $this->hmacsha256($identifier, $init_time, $expire_time, $base64_userbuf, $userbuf_enabled);

            if ($sig != $sigCalculated) {
                throw new \Exception('verify failed');
            }

            return true;
        } catch (\Exception $ex) {
            $error_msg = $ex->getMessage();
            return false;
        }
    }


    /**
     * 带 userbuf 验证签名。
     *
     * @param string $sig 签名内容
     * @param string $identifier 需要验证用户名，utf-8 编码
     * @param int $init_time 返回的生成时间，unix 时间戳
     * @param int $expire_time 返回的有效期，单位秒
     * @param string $error_msg 失败时的错误信息
     * @return boolean 验证是否成功
     * @throws \Exception
     */
    public function verifySig($sig, $identifier, &$init_time, &$expire_time, &$error_msg)
    {
        $userbuf = '';
        return $this->__verifySig($sig, $identifier, $init_time, $expire_time, $userbuf, $error_msg);
    }

    /**
     * 验证签名
     * @param string $sig 签名内容
     * @param string $identifier 需要验证用户名，utf-8 编码
     * @param int $init_time 返回的生成时间，unix 时间戳
     * @param int $expire_time 返回的有效期，单位秒
     * @param string $userbuf 返回的用户数据
     * @param string $error_msg 失败时的错误信息
     * @return boolean 验证是否成功
     * @throws \Exception
     */
    public function verifySigWithUserBuf($sig, $identifier, &$init_time, &$expire_time, &$userbuf, &$error_msg)
    {
        return $this->__verifySig($sig, $identifier, $init_time, $expire_time, $userbuf, $error_msg);
    }

    /*
     * 发送群组通知
     */
    public function sendGroupInform($group_id, $data = "{}")
    {
        $client = new Client();
        $round = mt_rand(10000000, 99999999);
        //拼接api
        $identifier = 'admin';
        $get_api_data = [
            'sdkappid' => $this->sdkappid,          #创建应用时即时通信 IM 控制台分配的 SDKAppID
            'identifier' => $identifier,            #必须为 App 管理员帐号
            'usersig' => $this->genSig($identifier),#App 管理员帐号生成的签名
            'random' => $round,                     #随机的32位无符号整数
            'contenttype' => 'json',                #固定值为json
        ];
        $get_data = http_build_query($get_api_data);
        $api = 'v4/group_open_http_svc/send_group_msg';
        $url = $this->url . '/' . $api . '?' . $get_data;

        //所需参数
        $post_api_data = [
            'GroupId' => $group_id,
            'Random' => $round,
            'MsgBody' =>
                [
                    [
                        "MsgType" => "TIMCustomElem", // 自定义消息
                        "MsgContent" => [
                            "Data" => $data
                        ]
                    ],
                ]
        ];

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 拉取群组信息
     */
    public function getLiveGroupInfo($arr_group_id)
    {
        $client = new Client();
        $round = mt_rand(10000000, 99999999);
        //拼接api
        $identifier = 'admin';
        $get_api_data = [
            'sdkappid' => $this->sdkappid,          #创建应用时即时通信 IM 控制台分配的 SDKAppID
            'identifier' => $identifier,            #必须为 App 管理员帐号
            'usersig' => $this->genSig($identifier),#App 管理员帐号生成的签名
            'random' => $round,                     #随机的32位无符号整数
            'contenttype' => 'json',                #固定值为json
        ];
        $get_data = http_build_query($get_api_data);
        $api = 'v4/group_open_http_svc/get_group_info';
        $url = $this->url . '/' . $api . '?' . $get_data;

        //所需参数
        $post_api_data = [
            "GroupIdList" => $arr_group_id//群组id
        ];

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 直播禁言
     */
    public function forbidSendMsg($str_group_id, $arr_member_account, $int_shut_up_time = 60)
    {
        $client = new Client();
        $round = mt_rand(10000000, 99999999);
        //拼接api
        $identifier = 'admin';
        $get_api_data = [
            'sdkappid' => $this->sdkappid,          #创建应用时即时通信 IM 控制台分配的 SDKAppID
            'identifier' => $identifier,            #必须为 App 管理员帐号
            'usersig' => $this->genSig($identifier),#App 管理员帐号生成的签名
            'random' => $round,                     #随机的32位无符号整数
            'contenttype' => 'json',                #固定值为json
        ];
        $get_data = http_build_query($get_api_data);
        $api = 'v4/group_open_http_svc/forbid_send_msg';
        $url = $this->url . '/' . $api . '?' . $get_data;

        //所需参数
        $post_api_data = [
            "GroupId" => $str_group_id,              #群组id
            "Members_Account" => $arr_member_account,#禁言的成员列表
            "ShutUpTime" => $int_shut_up_time        #禁言时间，单位为秒 0为解除禁言
        ];

        $data = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 得到直播中的群成员
     */
    public function getLiveIngMemberAccount()
    {
        //取直播中的group_id
        $group_id = Cache::get('watch_live_number_l_i_v_e', '');

        if ($group_id) {
            $live_data = $this->getLiveGroupInfo([$group_id]);
            $arr_informInfo = json_decode($live_data, true);
            if ($arr_informInfo['GroupInfo'][0]['ErrorCode'] != 0) {
                $member_account = [];
            } else {
                $arr_all_member_account = $arr_informInfo['GroupInfo'][0]['MemberList'];
                foreach ($arr_all_member_account as $v) {
                    $member_account[] = $v['Member_Account'];
                }
            }
        } else {
            $member_account = [];
        }

        return $member_account;
    }
}