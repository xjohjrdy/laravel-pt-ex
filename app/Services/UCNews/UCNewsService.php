<?php


namespace App\Services\UCNews;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * UC头条对接
 * Class UCNewsService
 * @package App\Services\Headline
 */
class UCNewsService
{
    protected $appId = '1570514109';
    protected $appName = 'grapebrowser-iflow';
    protected $appSecret = '23084d51fc284404b6eb1b31bc8fa273';
    protected $accessKey = 'PU_TAO_UC_ACCESS_TOKEN';
    protected $url_v3 = 'https://open.uczzd.cn/openiflow/openapi/v3/';

    /**
     * UC请求更新token
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateToken()
    {
        $client = new Client();
        $uri = 'https://open.uczzd.cn/openiflow/auth/token';
        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'app_id' => $this->appId,
                'app_secret' => $this->appSecret
            ],
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $uri, $header_data);
        $this->log('请求token: ' . (string)$res_login_data->getBody());
        return (string)$res_login_data->getBody();
    }

    /**
     * 获取token
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken()
    {
        if (Cache::has($this->accessKey)) {
            return Cache::get($this->accessKey);
        } else {
            $res = json_decode($this->updateToken(), true);
            if ($res['code'] == 0) { # token更新成功
                $this->log('token更新成功:' . Cache::get($this->accessKey));
                return Cache::get($this->accessKey);
            } else {
                return "";
            }
        }
    }

    /**
     * 拼接公共参数，返回最终请求地址
     * @param array $params 公共请求参数数组
     * @param $end_url 请求url 尾部
     * @return string
     */
    protected function getRequestQueryUrl($params = [], $end_url)
    {
        $params['app'] = $this->appName;
        $params['access_token'] = $this->getToken();
        $common_keys = ['access_token', 'app', 'dn', 'fr', 've', 'imei', 'oaid', 'nt', 'client_ip', 'utdid', 'city_code', 'city_name'];
        $str = '?';
        foreach ($common_keys as $key) {
            $str = empty($params[$key]) ? $str : $str . '&' . $key . '=' . $params[$key];
        }
        return $this->url_v3 . $end_url . $str;
    }

    /**
     * 获取UC头条新闻的频道列表
     * @param array $params
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getChannels($params = [])
    {

        $client = new Client();
        $uri = $this->getRequestQueryUrl($params, 'channels');
        $header_data = [
            'verify' => false
        ];
        $res_login_data = $client->request('GET', $uri, $header_data);
        return (string)$res_login_data->getBody();
    }

    /**
     * 获取UC头条默认第一个新闻频道
     * @param array $params
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFirstChannelId($params = [])
    {

        $client = new Client();
        $uri = $this->getRequestQueryUrl($params, 'channels');
        $header_data = [
            'verify' => false
        ];
        $res_login_data = $client->request('GET', $uri, $header_data);
        $res = json_decode((string)$res_login_data->getBody(), true);
        if (@$res['status'] == 0) {
            $cid = $res['data']['channel'][0]['id'];
            return $cid;
        } else {
            return 100;
        }
    }

    public function getChannelDetails($params = [])
    {
        $client = new Client();
        $uri = $this->getRequestQueryUrl($params, 'channel/' . @$params['cid']);
        $header_data = [
            'verify' => false
        ];
        $res_login_data = $client->request('GET', $uri, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 记录日志
     */
    public function log($msg)
    {
        $msg = date('Y-m-d H:m:s', time()) . ' :' . var_export($msg, true);
        Storage::disk('local')->append('callback_document/uc_access_token_notify.txt', $msg);
    }

}