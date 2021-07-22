<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\Collection;
use App\Entitys\App\GrowthUserValueConfig;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NewController extends Controller
{
    private $api_key = "Licieuh";
    private $vip_percent = 0.325;
    private $common_percent = 0.2;
    private $is_open = 0;
    private $share_vip_percent = 0.1;
    private $share_common_percent = 0.05;
    private $name = 'woxiaoli675015017';
    private $mm = 'mm_122930784_46170255_91593200288';
    private $arr_list = [
        'woxiaoli675015017' => 'mm_122930784_46170255_91593200288',
        'xxh4353' => 'mm_123348922_46184097_98173200486',
    ];

    private $new_can_change_auth_info = [
        '109375250125' => [
            'api_key' => 'Licieuh',
            'name' => 'woxiaoli675015017',
            'mm' => 'mm_122930784_46170255_109375250125',
        ],
        '109469450037' => [ # 卢淑清13799401629
            'api_key' => 'putaolushuqing',
            'name' => '卢淑清13799401629',
            'mm' => 'mm_123640184_378350331_109469450037',
        ],
        '109467850460' => [ # 徐丽华15959875125
            'api_key' => 'putaoxulihua',
            'name' => 'xxh4353',
            'mm' => 'mm_123348922_46184097_109467850460',
        ],
        '109467900491' => [ # 黄雪惠
            'api_key' => 'putaohuangxh',
            'name' => '雪惠000',
            'mm' => 'mm_105946111_379150017_109467900491',
        ],
    ];

    public function getIndex(Client $client)
    {
        $redis_key_new_head_classification = 'Alimama_New_new_head_classification';
        $redis_key_new_head_img = 'Alimama_New_new_head_img';

        if (Cache::has($redis_key_new_head_classification)) {
            $new_head_classification = Cache::get($redis_key_new_head_classification);
        } else {
            $head_classification_url = 'http://v2.api.haodanku.com/super_classify/apikey/' . $this->api_key;

            $res_head_classification = $client->request('get', $head_classification_url);


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
            Cache::put($redis_key_new_head_classification, $new_head_classification, 2);
        }

        if (Cache::has($redis_key_new_head_img)) {
            $new_head_img = Cache::get($redis_key_new_head_img);
        } else {
            $head_img_url = 'http://v2.api.haodanku.com/get_subject/apikey/' . $this->api_key;
            $res_head_img = $client->request('get', $head_img_url);
            $json_res_head_img = (string)$res_head_img->getBody();
            $head_img = json_decode($json_res_head_img, true);
            $new_head_img = [];
            foreach ($head_img['data'] as $k => $item) {
                $new_head_img[$k]['id'] = $item['id'];
                $new_head_img[$k]['img'] = 'http://img.haodanku.com/' . $item['app_image'];
                $new_head_img[$k]['name'] = $item['name'];
            }
            Cache::put($redis_key_new_head_img, $new_head_img, 2);
        }


        return $this->getResponse([
            'url_root' => 'http://img.haodanku.com/',
            'common_percent' => $this->common_percent,
            'vip_percent' => $this->vip_percent,
            'head_classification' => $new_head_classification,
            'head_img' => $new_head_img,
            'middle_classification' => [
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_1.png',
                    'text' => '人气',
                    'show_text' => '人气宝贝'
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_2.png',
                    'text' => '9.9包邮',
                    'show_text' => '9.9包邮'
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_3.png',
                    'text' => '视频',
                    'show_text' => '人气视频',
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_4.png',
                    'text' => '淘抢购',
                    'show_text' => '淘抢购',
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_5.png',
                    'text' => '划算',
                    'show_text' => '聚划算',
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_6.png',
                    'text' => '天猫超市',
                    'show_text' => '天猫超市',
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_7.png',
                    'text' => '天猫国际',
                    'show_text' => '天猫国际',
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_8.png',
                    'text' => '生鲜',
                    'show_text' => '天猫生鲜',
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_9.png',
                    'text' => '品牌',
                    'show_text' => '品牌购物',
                ],
                [
                    'img' => 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/index_10.png',
                    'text' => '优选',
                    'show_text' => '量贩优选',
                ],
            ],
            'three_img' => [
                'big' => [
                    'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/shop/%E7%96%AF%E6%8A%A2%E6%A6%9C%E5%8D%95@2x.png',
                    'text' => '疯抢',
                ],
                'small' => [
                    [
                        'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/shop/%E6%8A%96%E9%9F%B3@2x.png',
                        'text' => '抖音同款',
                    ],
                    [
                        'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/shop/9.9@2x.png',
                        'text' => '9.9包邮',
                    ]
                ]
            ],
            'right_img' => $new_head_img,
        ]);
    }

    /**
     * 获取抢购信息
     */
    public function getBuying(Request $request, Client $client)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'hour_type' => 'required',
            'min_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $buying_img_url = 'http://v2.api.haodanku.com/fastbuy/apikey/' . $this->api_key . '/hour_type/' . $arrRequest['hour_type'] . '/min_id/' . $arrRequest['min_id'];

        $res_buying = $client->request('get', $buying_img_url);

        $json_res_buying = (string)$res_buying->getBody();

        $buying = json_decode($json_res_buying, true);

        $new_buy = [];
        foreach ($buying['data'] as $k => $i) {
            $new_buy[$k] = $i;
            if (empty($this->is_open)) {
                $new_buy[$k]['tkmoney_general'] = "升级";
                $new_buy[$k]['tkmoney_vip'] = "升级";
            } else {
                $new_buy[$k]['tkmoney_general'] = (string)round($i['tkmoney'] * $this->common_percent, 2);
                $new_buy[$k]['tkmoney_vip'] = (string)round($i['tkmoney'] * $this->vip_percent, 2);
            }
        }
        return $this->getResponse($new_buy);
    }

    /**
     * 获取超级搜索
     */
    public function getBigSearch(Request $request, Client $client)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'keyword' => 'required',
            'min_id' => 'integer',
            'sort' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]),
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $buying_img_url = 'http://v2.api.haodanku.com/supersearch/apikey/' . $this->api_key . '/keyword/' . urlencode(urlencode($arrRequest['keyword'])) . '/back/10/min_id/' . $arrRequest['min_id'] . '/tb_p/1/sort/' . $arrRequest['sort'] . '/is_tmall/0/is_coupon/0/limitrate/0';

        $res_buying = $client->request('get', $buying_img_url);

        $json_res_buying = (string)$res_buying->getBody();

        $buying = json_decode($json_res_buying, true);

        $new_buy = [];
        foreach ($buying['data'] as $k => $i) {
            $new_buy[$k] = $i;
            if (empty($this->is_open)) {
                $new_buy[$k]['tkmoney_general'] = "升级";
                $new_buy[$k]['tkmoney_vip'] = "升级";
            } else {
                $new_buy[$k]['tkmoney_general'] = (string)round((($i['itemendprice'] * $i['tkrates']) / 100) * $this->common_percent, 2);
                $new_buy[$k]['tkmoney_vip'] = (string)round((($i['itemendprice'] * $i['tkrates']) / 100) * $this->vip_percent, 2);
            }
        }
        return $this->getResponse($new_buy);
    }

    /*
     * 订单详情
     */
    public function commodityDetails(Request $request, Collection $collection)
    {
        $client = new Client();
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'itemid' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $itemid = $arrRequest['itemid'];
            $app_id = empty($arrRequest['app_id']) ? 0 : $arrRequest['app_id'];
            /***********************************/
            $commodity_details_url = 'http://v2.api.haodanku.com/item_detail/apikey/' . $this->api_key . '/itemid/' . $itemid;
            $guess_like_url = 'http://v2.api.haodanku.com/get_similar_info/apikey/' . $this->api_key . '/itemid/' . $itemid;
            $high_commission_url = 'http://v2.api.haodanku.com/ratesurl';
            $post_api_data = [
                'apikey' => $this->api_key,
                'pid' => $this->mm,
                'tb_name' => $this->name,
                'itemid' => $itemid,
            ];
            $high_commission_data = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => $post_api_data
            ];
            $res_high_commission_data = $client->request('POST', $high_commission_url, $high_commission_data);
            $json_res_high_commission_data = (string)$res_high_commission_data->getBody();
            $arr_high_commission = json_decode($json_res_high_commission_data, true);
            $item_url = $arr_high_commission['data']['item_url'];
            $coupon_click_url = $arr_high_commission['data']['coupon_click_url'];
            $res_commodity_details = $client->request('get', $commodity_details_url);
            $res_guess_like = $client->request('get', $guess_like_url);
            $json_res_commodity_details = (string)$res_commodity_details->getBody();
            $json_res_guess_like = (string)$res_guess_like->getBody();
            $commodity_details = json_decode($json_res_commodity_details, true);
            if ($commodity_details['code'] == 0) {
                return $this->getInfoResponse('1003', '该宝贝详情不存在！');
            }
            $guess_like = json_decode($json_res_guess_like, true);
            if (empty($commodity_details['data']['couponmoney'])) {
                $commodity_details['data']['couponurl'] = $item_url;
            } else {
                $commodity_details['data']['couponurl'] = $coupon_click_url;
            }
            $commodity_details['data']['guess_like'] = $guess_like['data'];
            if (empty($this->is_open)) {
                $commodity_details['data']['tkmoney_general'] = "升级";
                $commodity_details['data']['tkmoney_vip'] = "升级";
            } else {
                $commodity_details['data']['tkmoney_general'] = (string)round($commodity_details['data']['tkmoney'] * $this->common_percent, 2);
                $commodity_details['data']['tkmoney_vip'] = (string)round($commodity_details['data']['tkmoney'] * $this->vip_percent, 2);
            }

            if (empty($this->is_open)) {
                $commodity_details['data']['share_tkmoney_general'] = "升级";
                $commodity_details['data']['share_tkmoney_vip'] = "升级";
            } else {
                $commodity_details['data']['share_tkmoney_general'] = (string)round($commodity_details['data']['tkmoney'] * $this->share_common_percent, 2);
                $commodity_details['data']['share_tkmoney_vip'] = (string)round($commodity_details['data']['tkmoney'] * $this->share_vip_percent, 2);
            }

            foreach ($commodity_details['data']['guess_like'] as &$item) {
                if (empty($this->is_open)) {
                    $item['tkmoney_general'] = "升级";
                    $item['tkmoney_vip'] = "升级";
                } else {
                    $item['tkmoney_general'] = (string)round($item['tkmoney'] * $this->common_percent, 2);
                    $item['tkmoney_vip'] = (string)round($item['tkmoney'] * $this->vip_percent, 2);
                }
            }
            $res = $collection->where(['app_id' => $app_id, 'itemid' => $itemid])->first();
            if (empty($res)) {
                $commodity_details['data']['is_collection'] = 0;
            } else {
                $commodity_details['data']['is_collection'] = $res->id;
            }
            if (empty($commodity_details['data']['couponstarttime'])) {
                $commodity_details['data']['couponstarttime'] = 0;
            }
            return $this->getResponse($commodity_details['data']);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 视频详情
     */
    public function videoCommodityDetails(Request $request, Collection $collection, AlimamaInfo $alimamaInfo)
    {
        $client = new Client();
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'itemid' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $itemid = $arrRequest['itemid'];
            $app_id = empty($arrRequest['app_id']) ? 0 : $arrRequest['app_id'];

            if (!empty($app_id)) {
                $rid = $alimamaInfo->where('app_id', $app_id)->first();
                if (empty($rid)) {
                    return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
                }
                if (!empty($rid->adzone_id)) {
                    $new_api_key = $this->new_can_change_auth_info[$rid->adzone_id]['api_key'];
                    $new_name = $this->new_can_change_auth_info[$rid->adzone_id]['name'];
                    $new_mm = $this->new_can_change_auth_info[$rid->adzone_id]['mm'];
                }
                if (empty($new_api_key)) {
                    return $this->getInfoResponse('1002', '账号错误!请重试！');
                }

                if (empty($new_name)) {
                    return $this->getInfoResponse('1002', '账号错误!请重试！');
                }

                if (empty($new_mm)) {
                    return $this->getInfoResponse('1002', '账号错误!请重试！');
                }
                $post_api_data = [
                    'apikey' => $new_api_key,
                    'pid' => $new_mm,
                    'tb_name' => $new_name,
                    'itemid' => $itemid,
                    'relation_id' => $rid->relation_id,
                    'get_taoword' => 1,
                    'title' => $itemid,
                ];
            } else {
                $new_api_key = $this->api_key;
                $new_name = $this->name;
                $new_mm = $this->mm;
                $post_api_data = [
                    'apikey' => $new_api_key,
                    'pid' => $new_mm,
                    'tb_name' => $new_name,
                    'itemid' => $itemid,
                ];
            }
            /***********************************/
            $commodity_details_url = 'http://v2.api.haodanku.com/item_detail/apikey/' . $this->api_key . '/itemid/' . $itemid;
            $guess_like_url = 'http://v2.api.haodanku.com/get_similar_info/apikey/' . $this->api_key . '/itemid/' . $itemid;
            $high_commission_url = 'http://v2.api.haodanku.com/ratesurl';

            $high_commission_data = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => $post_api_data
            ];
            $res_commodity_details = $client->request('get', $commodity_details_url);
            $res_guess_like = $client->request('get', $guess_like_url);
            $json_res_commodity_details = (string)$res_commodity_details->getBody();
            $json_res_guess_like = (string)$res_guess_like->getBody();
            $commodity_details = json_decode($json_res_commodity_details, true);
            $res_high_commission_data = $client->request('POST', $high_commission_url, $high_commission_data);
            $json_res_high_commission_data = (string)$res_high_commission_data->getBody();
            $arr_high_commission = json_decode($json_res_high_commission_data, true);
            $item_url = $arr_high_commission['data']['item_url'];
            $coupon_click_url = $arr_high_commission['data']['coupon_click_url'];
            if (empty($app_id)) {
                $tao_word = 0;
            } else {
                $tao_word = $arr_high_commission['data']['taoword'];
            }
            if ($commodity_details['code'] == 0) {
                return $this->getInfoResponse('1003', '该宝贝详情不存在！');
            }
            $guess_like = json_decode($json_res_guess_like, true);
            if (empty($commodity_details['data']['couponmoney'])) {
                $commodity_details['data']['couponurl'] = $item_url;
            } else {
                $commodity_details['data']['couponurl'] = $coupon_click_url;
            }
            $commodity_details['data']['guess_like'] = $guess_like['data'];
            $commodity_details['data']['tkmoney_general'] = (string)round($commodity_details['data']['tkmoney'] * $this->common_percent, 2);
            $commodity_details['data']['tkmoney_vip'] = (string)round($commodity_details['data']['tkmoney'] * $this->vip_percent, 2);
            $commodity_details['data']['share_tkmoney_general'] = (string)round($commodity_details['data']['tkmoney'] * $this->share_common_percent, 2);
            $commodity_details['data']['share_tkmoney_vip'] = (string)round($commodity_details['data']['tkmoney'] * $this->share_vip_percent, 2);
            $guess_like = [];
            foreach ($commodity_details['data']['guess_like'] as $item) {
                $guess_like_item['coupon'] = $item['couponmoney'];
                $guess_like_item['coupon_price'] = $item['itemendprice'];
                $guess_like_item['good_id'] = $item['itemid'];
                $guess_like_item['img'] = $item['itempic'];
                $guess_like_item['price'] = $item['itemprice'];
                $guess_like_item['sale_number'] = $item['itemsale'];
                $guess_like_item['share_tkmoney_general'] = $item['couponmoney'];
                $guess_like_item['share_tkmoney_vip'] = $item['couponmoney'];
                $guess_like_item['store'] = $item['shopname'];
                $guess_like_item['store_from'] = $item['shoptype'];
                $guess_like_item['title'] = $item['itemtitle'];
                $guess_like_item['share_tkmoney_general'] = (string)round($item['tkmoney'] * $this->share_common_percent, 2);
                $guess_like_item['share_tkmoney_vip'] = (string)round($item['tkmoney'] * $this->share_vip_percent, 2);
                $guess_like_item['tkmoney_general'] = (string)round($item['tkmoney'] * $this->common_percent, 2);
                $guess_like_item['tkmoney_vip'] = (string)round($item['tkmoney'] * $this->vip_percent, 2);
                $guess_like[] = $guess_like_item;
            }
            $res = $collection->where(['app_id' => $app_id, 'itemid' => $itemid])->first();
            if (empty($res)) {
                $commodity_details['data']['is_collection'] = 0;
            } else {
                $commodity_details['data']['is_collection'] = $res->id;
            }
            if (empty($commodity_details['data']['couponstarttime'])) {
                $commodity_details['data']['couponstarttime'] = 0;
            }
            $video_commodity_details = [
                'title' => $commodity_details['data']['itemtitle'],                                           #标题
                'many_img' => [                                                                               #图片
                    $commodity_details['data']['itempic'],
                ],
                'store_from' => $commodity_details['data']['shoptype'],                                       #天猫还是淘宝
                'sale_number' => $commodity_details['data']['itemsale'],                                      #销量
                'coupon_price' => $commodity_details['data']['itemendprice'],                                 #券后价
                'price' => $commodity_details['data']['itemprice'],                                           #普通价
                'tkmoney_general' => $commodity_details['data']['tkmoney_general'],                           #预估报销
                'tkmoney_vip' => $commodity_details['data']['tkmoney_vip'],                                   #vip预估报销
                'coupon' => $commodity_details['data']['couponmoney'],                                        #优惠券金额
                'coupon_start_time' => $commodity_details['data']['couponstarttime'],                         #优惠券开始时间
                'coupon_end_time' => $commodity_details['data']['couponendtime'],                             #优惠券结束时间
                'detail_img' => "http://img.haodanku.com/" . $commodity_details['data']['itempic_copy'] . "-600", #详情图
                'brand_name' => $commodity_details['data']['is_brand'],                                       #是否品牌
                'good_id' => $commodity_details['data']['itemid'],                                            #宝贝id
                'is_collection' => $commodity_details['data']['is_collection'],                               #是否为收藏
                'share_tkmoney_general' => $commodity_details['data']['share_tkmoney_general'],
                'share_tkmoney_vip' => $commodity_details['data']['share_tkmoney_vip'],
                'guess_like' => $guess_like,
                'share_api_url' => 'http://ax.k5it.com/h5_share/#/',
                'tao_word' => $tao_word,
            ];

            $msg_user_date = @$app_id . "\t" . $video_commodity_details['title'];
            @Storage::disk('local')->append('callback_document/w_video.txt', date('"Y-m-d H:i:s"') . '#### ' . var_export($msg_user_date, true) . ' ####');


            $obj_growth_user_value_Config = new GrowthUserValueConfig();
            $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
            $video_commodity_details['growth_value_new_vip'] = round($video_commodity_details['tkmoney_vip'] / $num_growth_value, 2);
            $video_commodity_details['growth_value_new_normal'] = round($video_commodity_details['tkmoney_general'] / $num_growth_value, 2);
            return $this->getResponse($video_commodity_details);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 根据关键词获取商品详情
     * 0全部，1女装，2男装，3内衣，4美妆，5配饰，6鞋品，7箱包，8儿童，9母婴，10居家，11美食，12数码，13家电，14其他，15车品，16文体
     * 0.综合（最新），1.券后价(低到高)，2.券后价（高到低），3.券面额，4.销量，5.佣金比例，6.券面额（低到高），7.月销量（低到高），8.佣金比例（低到高），9.全天销量（高到低），10全天销量（低到高），11.近2小时销量（高到低），12.近2小时销量（低到高），13.优惠券领取量（高到低），14.好单库指数（高到低）
     */
    public function getKeyWord(Request $request, Client $client)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'min_id' => 'integer',
                'cid' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16]),
                'sort' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]),
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $cid = $arrRequest['cid'];
            $sort = $arrRequest['sort'];
            $min_id = $arrRequest['min_id'];

            /***********************************/

            $url_keyword_items = 'http://v2.api.haodanku.com/get_keyword_items';
            $arr_cid_val = [
                '全部',
                '女装',
                '男装',
                '内衣',
                '美妆',
                '配饰',
                '鞋品',
                '箱包',
                '儿童',
                '母婴',
                '居家',
                '美食',
                '数码',
                '家电',
                '其他',
                '车品',
                '文体',
            ];
            $params = [
                'apikey' => $this->api_key,
                'keyword' => urlencode(urlencode($arr_cid_val[$cid])),
                'back' => 20,
                'sort' => $sort,
                'cid' => $cid,
                'min_id' => $min_id,
            ];

            foreach ($params as $key => $param) {
                $url_keyword_items .= "/{$key}/{$param}";
            }

            $res_buying = $client->request('get', $url_keyword_items);

            $json_res_buying = (string)$res_buying->getBody();

            $buying = json_decode($json_res_buying, true);

            $v_data = @$buying['data'];

            foreach ($v_data as &$item) {
                if (empty($this->is_open)) {
                    $item['tkmoney_general'] = "升级";
                    $item['tkmoney_vip'] = "升级";
                } else {
                    $item['tkmoney_general'] = (string)round($item['tkmoney'] * $this->common_percent, 2);
                    $item['tkmoney_vip'] = (string)round($item['tkmoney'] * $this->vip_percent, 2);
                }
            }
            /***********************************/

            return $this->getResponse($v_data);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取超级搜索
     */
    public function getSearch(Request $request, Client $client)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'keyword' => 'required',
            'min_id' => 'integer',
            'sort' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]),
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $buying_img_url = 'http://v2.api.haodanku.com/supersearch/apikey/' . $this->api_key . '/keyword/' . urlencode(urlencode($arrRequest['keyword'])) . '/back/10/min_id/' . $arrRequest['min_id'] . '/tb_p/1/sort/' . $arrRequest['sort'] . '/is_tmall/0/is_coupon/0/limitrate/0';

        $res_buying = $client->request('get', $buying_img_url);

        $json_res_buying = (string)$res_buying->getBody();

        $buying = json_decode($json_res_buying, true);

        $new_buy = [];
        foreach ($buying['data'] as $k => $i) {
            $new_buy[$k] = $i;
            if (empty($this->is_open)) {
                $new_buy[$k]['tkmoney_general'] = "升级";
                $new_buy[$k]['tkmoney_vip'] = "升级";
            } else {
                $new_buy[$k]['tkmoney_general'] = (string)round((($i['itemendprice'] * $i['tkrates']) / 100) * $this->common_percent, 2);
                $new_buy[$k]['tkmoney_vip'] = (string)round((($i['itemendprice'] * $i['tkrates']) / 100) * $this->vip_percent, 2);
            }
        }
        return $this->getResponse($new_buy);
    }

    /*
     * 搜索
     */
    public function getSearchV1(Request $request, Client $client)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'min_id' => 'integer',
                'keyword' => 'required',
                'sort' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]),
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $keyword = $arrRequest['keyword'];
            $sort = $arrRequest['sort'];
            $min_id = $arrRequest['min_id'];

            /***********************************/

            $url_keyword_items = 'http://v2.api.haodanku.com/get_keyword_items';

            $params = [
                'apikey' => $this->api_key,
                'keyword' => urlencode(urlencode($keyword)),
                'back' => 20,
                'sort' => $sort,
                'min_id' => $min_id,
            ];

            foreach ($params as $key => $param) {
                $url_keyword_items .= "/{$key}/{$param}";
            }

            $res_buying = $client->request('get', $url_keyword_items);

            $json_res_buying = (string)$res_buying->getBody();

            $buying = json_decode($json_res_buying, true);

            $v_data = @$buying['data'];

            foreach ($v_data as &$item) {
                if (empty($this->is_open)) {
                    $item['tkmoney_general'] = "升级";
                    $item['tkmoney_vip'] = "升级";
                } else {
                    $item['tkmoney_general'] = (string)round($item['tkmoney'] * $this->common_percent, 2);
                    $item['tkmoney_vip'] = (string)round($item['tkmoney'] * $this->vip_percent, 2);
                }
            }

            /***********************************/

            return $this->getResponse($v_data);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /*
     * 根据关键词获取商品详情
     * 0全部，1女装，2男装，3内衣，4美妆，5配饰，6鞋品，7箱包，8儿童，9母婴，10居家，11美食，12数码，13家电，14其他，15车品，16文体
     * 0.综合（最新），1.券后价(低到高)，2.券后价（高到低），3.券面额，4.销量，5.佣金比例，6.券面额（低到高），7.月销量（低到高），8.佣金比例（低到高），9.全天销量（高到低），10全天销量（低到高），11.近2小时销量（高到低），12.近2小时销量（低到高），13.优惠券领取量（高到低），14.好单库指数（高到低）
     */
    public function getClassify(Request $request, Client $client)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'min_id' => 'integer',
                'keyword' => 'required',
                'cid' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16]),
                'sort' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]),
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $cid = $arrRequest['cid'];
            $sort = $arrRequest['sort'];
            $min_id = $arrRequest['min_id'];
            $keyword = $arrRequest['keyword'];

            /***********************************/

            $url_keyword_items = 'http://v2.api.haodanku.com/get_keyword_items';

            $params = [
                'apikey' => $this->api_key,
                'keyword' => urlencode(urlencode($keyword)),
                'back' => 20,
                'sort' => $sort,
                'cid' => $cid,
                'min_id' => $min_id,
            ];

            foreach ($params as $key => $param) {
                $url_keyword_items .= "/{$key}/{$param}";
            }

            $res_buying = $client->request('get', $url_keyword_items);

            $json_res_buying = (string)$res_buying->getBody();

            $buying = json_decode($json_res_buying, true);

            $v_data = @$buying['data'];

            foreach ($v_data as &$item) {
                if (empty($this->is_open)) {
                    $item['tkmoney_general'] = "升级";
                    $item['tkmoney_vip'] = "升级";
                } else {
                    $item['tkmoney_general'] = (string)round($item['tkmoney'] * $this->common_percent, 2);
                    $item['tkmoney_vip'] = (string)round($item['tkmoney'] * $this->vip_percent, 2);
                }
            }

            /***********************************/

            return $this->getResponse($v_data);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 精选专题商品API
     */
    public function getSubject(Request $request, Client $client)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $id = $arrRequest['id'];

            /***********************************/

            $url_keyword_items = 'http://v2.api.haodanku.com/get_subject_item';

            $params = [
                'apikey' => $this->api_key,
                'id' => $id,
            ];

            foreach ($params as $key => $param) {
                $url_keyword_items .= "/{$key}/{$param}";
            }

            $res_buying = $client->request('get', $url_keyword_items);

            $json_res_buying = (string)$res_buying->getBody();

            $buying = json_decode($json_res_buying, true);

            $v_data = @$buying['data'];

            foreach ($v_data as &$item) {
                if (empty($this->is_open)) {
                    $item['tkmoney_general'] = "升级";
                    $item['tkmoney_vip'] = "升级";
                } else {
                    $item['tkmoney_general'] = (string)round($item['tkmoney'] * $this->common_percent, 2);
                    $item['tkmoney_vip'] = (string)round($item['tkmoney'] * $this->vip_percent, 2);
                }
            }

            /***********************************/

            return $this->getResponse($v_data);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /*
     * 高佣直转
     */
    public function getRatesUrl(Request $request, Client $client)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }
        $high_commission_url = 'http://v2.api.haodanku.com/ratesurl';

        $post_api_data = [
            'apikey' => $this->api_key,
            'pid' => $this->mm,
            'tb_name' => $this->name,
            'itemid' => $itemid,
        ];
        $high_commission_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data
        ];
        $res_high_commission_data = $client->request('POST', $high_commission_url, $high_commission_data);
        $json_res_high_commission_data = (string)$res_high_commission_data->getBody();
        $arr_high_commission = json_decode($json_res_high_commission_data, true);
        if ($arr_high_commission['code'] != 1) {
            return $this->getInfoResponse('1001', '该商品不存在');
        }

        if (@$arr_high_commission['data']['couponmoney'] == 0) {
            $good_url = @$arr_high_commission['data']['item_url'];
        } else {
            $good_url = @$arr_high_commission['data']['coupon_click_url'];
        }

        return $this->getResponse($good_url);
    }

}
