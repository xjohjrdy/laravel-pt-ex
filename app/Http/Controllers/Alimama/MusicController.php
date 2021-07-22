<?php

namespace App\Http\Controllers\Alimama;

use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class MusicController extends Controller
{

    private $api_key = "Licieuh";

    private $vip_percent = 0.325;
    private $common_percent = 0.2;

    /**
     *
     */
    public function getList(Client $client, Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'min_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $cat_id = rand(10, 11);
            if (!empty($arrRequest['cat_id'])) {
                $cat_id = $arrRequest['cat_id'];
            }

            $head_img_url = 'http://v2.api.haodanku.com/get_trill_data/apikey/' . $this->api_key . '/min_id/' . $arrRequest['min_id'] . '/back/10/cat_id/' . $cat_id;
            $res_head_img = $client->request('get', $head_img_url);
            $json_res_head_img = (string)$res_head_img->getBody();
            $head_img = json_decode($json_res_head_img, true);

            $arr = [
                'min_id' => $head_img['min_id'],
            ];

            if (!empty($head_img['data'])) {
                foreach ($head_img['data'] as $datum) {
                    $tkmoney_general = (string)round($datum['tkmoney'] * $this->common_percent, 2);
                    $tkmoney_vip = (string)round($datum['tkmoney'] * $this->vip_percent, 2);
                    $arr['list'][] = [
                        'itemid' => $datum['itemid'],
                        'title' => $datum['itemtitle'],
                        'img' => $datum['itempic'] . '_310x310.jpg',
                        'coupon' => $datum['couponmoney'],
                        'coupon_price' => $datum['itemendprice'],
                        'sale_number' => $datum['itemsale'],
                        'tkmoney_general' => $tkmoney_general,
                        'tkmoney_vip' => $tkmoney_vip,
                        'store' => $datum['shopname'],
                        'price' => $datum['itemprice'],
                        'store_from' => $datum['shoptype'],
                        'video_url' => $datum['dy_video_url'],
                        'introduce' => $datum['itemdesc'],
                        'play_number' => (int)($datum['dy_video_like_count'] + 9900),
                        'share_api_url' => 'http://ax.k5it.com/h5_share/#/',
                    ];
                }
            }

            $redis_key = 'alimama_music_video_' . $arrRequest['min_id'] . '_' . $cat_id;
            if (Cache::has($redis_key)) {
                return $this->getResponse(Cache::get($redis_key));
            } else {
                Cache::put($redis_key, $arr, 10);
            }

            return $this->getResponse($arr);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * index
     */
    public function getIndex(Client $client, Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $cat_id = 0;
            $min_id = 0;

            $head_img_url = 'http://v2.api.haodanku.com/get_trill_data/apikey/' . $this->api_key . '/min_id/' . $min_id . '/back/10/cat_id/' . $cat_id;
            $res_head_img = $client->request('get', $head_img_url);
            $json_res_head_img = (string)$res_head_img->getBody();
            $head_img = json_decode($json_res_head_img, true);

            $arr = [];
            if (!empty($head_img['data'])) {
                $i = 1;
                foreach ($head_img['data'] as $datum) {
                    if ($i > 6) {
                        break;
                    }
                    $tkmoney_general = (string)round($datum['tkmoney'] * $this->common_percent, 2);
                    $tkmoney_vip = (string)round($datum['tkmoney'] * $this->vip_percent, 2);
                    $arr[] = [
                        'itemid' => $datum['itemid'],
                        'title' => $datum['itemtitle'],
                        'img' => $datum['itempic'] . '_310x310.jpg',
                        'coupon' => $datum['couponmoney'],
                        'coupon_price' => $datum['itemendprice'],
                        'sale_number' => $datum['itemsale'],
                        'tkmoney_general' => $tkmoney_general,
                        'tkmoney_vip' => $tkmoney_vip,
                        'store' => $datum['shopname'],
                        'price' => $datum['itemprice'],
                        'store_from' => $datum['shoptype'],
                        'video_url' => $datum['dy_video_url'],
                        'introduce' => $datum['itemdesc'],
                        'play_number' => (int)($datum['dy_video_like_count'] + 9900),
                        'share_api_url' => 'http://ax.k5it.com/h5_share/#/',
                    ];
                    $i++;
                }
            }

            return $this->getResponse($arr);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

}
