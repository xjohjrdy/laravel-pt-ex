<?php

namespace App\Http\Controllers\Material;

use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\AlimamaInfoNew;
use App\Entitys\App\GrowthUserValueConfig;
use App\Exceptions\ApiException;
use App\Services\Alimama\BigWashUser;
use App\Services\TbkCashCreate\TbkCashCreateServices;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class FriendController extends Controller
{
    private $api_key = "Licieuh";

    private $vip_percent = 0.325;
    private $common_percent = 0.2;
    private $share_vip_percent = 0.1;
    private $share_common_percent = 0.05;

    /**
     *
     */
    public function getList(Client $client, Request $request, AlimamaInfo $alimamaInfo, AlimamaInfoNew $alimamaInfoNew, TbkCashCreateServices $cashCreateServices)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'min_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            if (empty($arrRequest['min_id'])) {
                $arrRequest['min_id'] = 1;
            }

            if ($arrRequest['min_id'] > 12) {
                return $this->getResponse(['data' => [], "min_id" => 13]);
            }

            $head_img_url = 'http://v2.api.haodanku.com/selected_item/apikey/' . $this->api_key . '/min_id/' . $arrRequest['min_id'];
            $res_head_img = $client->request('get', $head_img_url);
            $json_res_head_img = (string)$res_head_img->getBody();
            $head_img = json_decode($json_res_head_img, true);

            $arr = [
                'min_id' => $head_img['min_id'],
            ];

            $service_dataoke = new BigWashUser();
            if (!empty($head_img['data'])) {
                foreach ($head_img['data'] as $datum) {

                    $params = [
                        'goodsId' => $datum['itemid'],
                    ];

                    $rid = $alimamaInfo->where('app_id', $arrRequest['app_id'])->value('relation_id');

                    if (empty($rid)) {
                        $rid = $alimamaInfoNew->where('app_id', $arrRequest['app_id'])->value('relation_id');
                    }

                    if ($rid) {
                        $share_url_change = $service_dataoke->newUrlChange($params);
                        $joint_share_url_change = $share_url_change . '&relationId=' . $rid;
                        $tbk_command = $cashCreateServices->getTpwdCreate($datum['itemtitle'], $joint_share_url_change);
                        $tbk_command = @$tbk_command['data']['model'];
                        $datum['copy_comment'] = $datum['copy_comment'] . $tbk_command;
                    } else {
                        $datum['copy_comment'] = $datum['copy_comment'] . '[无]';
                    }

                    $tkmoney_general = (string)round((($datum['itemendprice'] * $datum['tkrates']) / 100) * $this->common_percent, 2);
                    $tkmoney_vip = (string)round((($datum['itemendprice'] * $datum['tkrates']) / 100) * $this->vip_percent, 2);

                    $share_tkmoney_general = round((($datum['itemendprice'] * $datum['tkrates']) / 100) * $this->share_common_percent, 2); //分享预估报销
                    $share_tkmoney_vip = round((($datum['itemendprice'] * $datum['tkrates']) / 100) * $this->share_vip_percent, 2); //vip分享预估报销

                    $obj_growth_user_value_Config = new GrowthUserValueConfig();
                    $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
                    $growth_value_new_vip = round($tkmoney_vip / $num_growth_value, 2);
                    $growth_value_new_normal = round($tkmoney_general / $num_growth_value, 2);

                    if (!empty($datum['itempic'])) {
                        $pic = implode(',', $datum['itempic']);
                    }

                    $reg_tao = "/\x{ffe5}([a-zA-Z0-9]{11})\x{ffe5}/isu";

                    if (preg_match($reg_tao, $datum['copy_comment'], $m_tao) !== false) {
                        $datum['copy_comment'] = '复制这条淘口令，进入【Tao宝】即可抢购，$淘口令$' . @$m_tao[0];
                    }


                    $arr['data'][] = [
                        'created_at_v' => date('Y-m-d H:i:s', $datum['show_time']),
                        'context_img' => $pic,
                        'forward' => $datum['dummy_click_statistics'],
                        'context_key' => $datum['copy_comment'],
                        'context' => $datum['copy_content'],
                        'good_id' => $datum['itemid'],
                        'tkmoney_general' => $tkmoney_general,
                        'tkmoney_vip' => $tkmoney_vip,
                        'share_tkmoney_general' => $share_tkmoney_general,
                        'share_tkmoney_vip' => $share_tkmoney_vip,
                        'coupon_price' => $datum['itemendprice'],
                        'coupon' => $datum['couponmoney'],
                        'price' => $datum['itemprice'],
                        'sale_number' => $datum['dummy_click_statistics'],
                        'img' => 'http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com//img%2F158268324690142.png',
                        'name' => '葡萄小助手',
                        'title' => $datum['itemtitle'],
                        'from_type' => 1,
                        'growth_value_new_normal' => $growth_value_new_normal,
                        'growth_value_new_vip' => $growth_value_new_vip,
                        'is_hidden' => 1,
                    ];
                }
            }

            $redis_key = $arrRequest['app_id'] . 'alimama_friends_list_' . $arrRequest['min_id'];
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


    public function getPublic()
    {
//        $img = "https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/u3001.png";
        $img = "http://cdn01.36qq.com/CDN/5b0cf3374eb7b854ae42bcbf631b654.png";
        return $this->getResponse([
            'is_hidden' => 1,
            'img' => $img,
        ]);
    }

    public function getShow()
    {
        return $this->getResponse([
            'is_hidden' => 1,
        ]);
    }
}
