<?php

namespace App\Services\Alimama;

use GuzzleHttp\Client;

class GoodWarehouse
{
    private $api_key = "Licieuh";
    private $vip_percent = 0.325;
    private $common_percent = 0.2;
    private $is_open = 0;
    private $share_vip_percent = 0.1;
    private $share_common_percent = 0.05;
    private $name = 'woxiaoli675015017';
    private $mm = 'mm_122930784_46170255_91593200288';

    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function type()
    {
        $head_classification_url = 'http://v2.api.haodanku.com/super_classify/apikey/' . $this->api_key;
        $res_head_classification = $this->client->request('get', $head_classification_url);
        $json_res_head_classification = (string)$res_head_classification->getBody();
        $head_classification = json_decode($json_res_head_classification, true);
        $new_head_classification = [];
        foreach ($head_classification['general_classify'] as $k => $item) {
            $new_head_classification[$k]['title'] = $item['main_name'];
            $new_head_classification[$k]['cid'] = $item['cid'];
            $arr_head_class = array_merge($item['data'][0]['info'], $item['data'][1]['info']);
            $head_class_t = [];
            foreach ($arr_head_class as $t => $head_class) {
                if ($t > 7) {
                    break;
                }
                $head_class_t[$t] = $head_class;
            }
            $new_head_classification[$k]['data'] = $head_class_t;
        }
        return $new_head_classification;
    }

    /**
     * @return array
     */
    public function headImg()
    {
        $head_img_url = 'http://v2.api.haodanku.com/get_subject/apikey/' . $this->api_key;
        $res_head_img = $this->client->request('get', $head_img_url);
        $json_res_head_img = (string)$res_head_img->getBody();
        $head_img = json_decode($json_res_head_img, true);
        $new_head_img = [];
        foreach ($head_img['data'] as $k => $item) {
            $new_head_img[$k]['id'] = $item['id'];
            $new_head_img[$k]['img'] = 'http://img.haodanku.com/' . $item['app_image'];
            $new_head_img[$k]['name'] = $item['name'];
        }
        return $new_head_img;
    }

    /**
     * @return mixed
     */
    public function guessLike()
    {
        $guess_like_url = 'http://v2.api.haodanku.com/get_similar_info/apikey/' . $this->api_key . '/itemid/' . $itemid;
        $res_guess_like = $this->client->request('get', $guess_like_url);
        $json_res_guess_like = (string)$res_guess_like->getBody();
        $guess_like = json_decode($json_res_guess_like, true);
        foreach ($guess_like['data'] as &$item) {
            if (empty($this->is_open)) {
                $item['tkmoney_general'] = "升级";
                $item['tkmoney_vip'] = "升级";
            } else {
                $item['tkmoney_general'] = (string)round($item['tkmoney'] * $this->common_percent, 2);
                $item['tkmoney_vip'] = (string)round($item['tkmoney'] * $this->vip_percent, 2);
            }
        }
        return $guess_like['data'];
    }


}
