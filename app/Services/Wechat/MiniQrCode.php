<?php


namespace App\Services\Wechat;


use Illuminate\Support\Facades\Cache;

class MiniQrCode
{
    //葡萄优选
//    private $app_id = 'wx8e681805fc9c3e02';
//    private $app_secret = '2b4af62085ce03a6ea7f3029a50dd0b8';

    private $app_id = 'wx34989a331407111a';
    private $app_secret = 'c6a7c0feb7113d3112481490bfe7cbac';

    private $auth_url = 'https://api.weixin.qq.com/sns/jscode2session';

    private $access_token = '';
    private $get_token_url = 'https://api.weixin.qq.com/cgi-bin/token';

    //&appid=' . $this->app_id . '&secret=c6a7c0feb7113d3112481490bfe7cbac'

    //记录用于小程序交换的token 心选购
    const B_WX_XXG_TOKEN = 'b_c_wx_xxg_token';

    public function __construct()
    {
        if (!Cache::has(self::B_WX_XXG_TOKEN)) {
            $access_token = $this->getAccessToken();
            Cache::put(self::B_WX_XXG_TOKEN, $access_token, 100); //这里缓存100分钟，微信那边是120分钟
        }

        $this->access_token = Cache::get(self::B_WX_XXG_TOKEN);
    }

    public function getQrCode($scene, $page, $width = '300px', $is_hyaline = true)
    {

        $post_json = json_encode([
            'scene' => $scene,
            'page' => $page,
            'width' => $width,
            'is_hyaline' => $is_hyaline,
        ]);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $this->access_token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_json,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;

    }


    private function getAccessToken()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->get_token_url . "?grant_type=client_credential&appid=" . $this->app_id . "&secret=" . $this->app_secret,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $res_arr = json_decode($response, true);

        return @$res_arr['access_token'];

    }

}