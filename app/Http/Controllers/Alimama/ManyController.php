<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\AlimamaInfoNew;
use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\JsonConfig;
use App\Entitys\App\NewNewCollection;
use App\Entitys\App\OneGoAlimamaInfo;
use App\Entitys\App\UserOrderNew;
use App\Entitys\App\UserOrderTao;
use App\Exceptions\ApiException;
use App\Services\Alimama\AlimamaService;
use App\Services\Alimama\BigWashUser;
use App\Entitys\App\TaobaoBanner;
use App\Entitys\App\TaobaoZeroBuy;
use App\Services\Alimama\GoodWarehouse;
use App\Services\TbkCashCreate\TbkCashCreateServices;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class ManyController extends Controller
{

    private $api_key = "Licieuh";

    /**
     * 0元购首页
     */
    public function zeroIndex(Request $request, TaobaoZeroBuy $taobaoZeroBuy)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $good = $taobaoZeroBuy->getIndex();
            $no_good = $taobaoZeroBuy->getNo();
            return $this->getResponse([
                'good' => $good,
                'no_good' => $no_good,
            ]);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 转链
     */
    public function zeroChange(Request $request, TaobaoZeroBuy $taobaoZeroBuy, UserOrderNew $userOrderNew, AdUserInfo $adUserInfo, UserOrderTao $userOrderTao)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'itemid' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $itemid = $arrRequest['itemid'];
            if (empty($itemid)) {
                throw new ApiException('缺少必要参数,错误信息', 3002);
            }
            $taobao_zero_buy = $taobaoZeroBuy->getOne($itemid);
            $is_order = $userOrderNew->where([
                'order_number' => $itemid,
                'user_id' => $arrRequest['app_id']
            ])->count();
            $is_one_order_new = $userOrderNew->where([
                'user_id' => $arrRequest['app_id']
            ])->count();
            $is_one_order = $userOrderTao->where([
                'user_id' => $arrRequest['app_id']
            ])->count();

            if ($is_order >= $taobao_zero_buy->can_buy) {
                return $this->getInfoResponse('4001', '已超过限购数量');
            }

            if ($taobao_zero_buy->type == 1) {
                $ad_user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
                if ($ad_user->groupid < 23) {
                    return $this->getInfoResponse('5001', '该商品仅限超级用户购买');
                }
            }

            if ($taobao_zero_buy->type == 2) {
                if ($is_one_order >= 1 || $is_one_order_new >= 1) {
                    return $this->getInfoResponse('6001', '该商品仅限新户购买');
                }
            }

            $service_dataoke = new BigWashUser();

            $params = [
                'goodsId' => $itemid,
            ];

            $goods_url = $service_dataoke->urlChange($params);

            if (empty($goods_url)) {
                return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
            }

            return $this->getResponse($goods_url);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * 首页
     * @param GoodWarehouse $goodWarehouse
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIndex(BigWashUser $bigWashUser, TaobaoBanner $taobaoBanner, Client $client)
    {

        $redis_key_new_head_classification = 'Alimama_New_new_head_classification';
        $redis_key_new_head_img = 'Alimama_New_new_head_img';

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
            Cache::put($redis_key_new_head_img, $new_head_img, 99);
        }

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
            Cache::put($redis_key_new_head_classification, $new_head_classification, 99);
        }


        $service_alimama = new AlimamaService();
        $params_one = [
            'q' => '天猫超市',
            'size' => 3,
            'has_coupon' => 'true'
        ];
        $params_two = [
            'q' => '天猫国际',
            'size' => 3,
            'has_coupon' => 'true'
        ];
        $three_data = [
            'title' => '热门榜单',
            'right_title' => '足不出户，省钱购好物',
            'left_title' => '发现好物',
        ];

        $obj_config = new JsonConfig();
        $arr_config_data = $obj_config->getValue('ali_index');

        if (!empty($arr_config_data["one"])) {
            $params_one = $arr_config_data["one"];
        }

        if (!empty($arr_config_data["two"])) {
            $params_two = $arr_config_data["two"];
        }

        if (!empty($arr_config_data["three"])) {
            $three_data = $arr_config_data["three"];
        }

        $data_one = $service_alimama->dgSearch($params_one);
        //获取成长值比例 计算次月最大送的成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($data_one as &$v_one) {
            $v_one['growth_value_new_vip'] = (string)round(@$v_one['tkmoney_vip'] / $num_growth_value, 2);
            $v_one['growth_value_new_normal'] = (string)round(@$v_one['tkmoney_general'] / $num_growth_value, 2);
        }

        $data_two = $service_alimama->dgSearch($params_two);
        //获取成长值比例 计算次月最大送的成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($data_two as &$v_two) {
            $v_two['growth_value_new_vip'] = (string)round(@$v_two['tkmoney_vip'] / $num_growth_value, 2);
            $v_two['growth_value_new_normal'] = (string)round(@$v_two['tkmoney_general'] / $num_growth_value, 2);
        }

        //取淘报销关键字
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $str_tao_keyword_value = $obj_growth_user_value_Config->value('tao_keyword_value');

        return $this->getResponse([
            'special_search' => $str_tao_keyword_value,
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
                    'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/circle/9.9.png',
                    'text' => '9.9包邮',
                    'jd_link' => '0',
                ],
                'small' => [
                    [
                        'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/te/%E8%A7%86%E9%A2%91/%E9%99%90%E6%97%B6%E7%A7%92%E6%9D%80.png',
                        'text' => '疯抢',
                        'jd_link' => '0',
                    ],
                    [
                        'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/te/%E8%A7%86%E9%A2%91/%E6%8A%96%E9%9F%B3%E5%90%8C%E6%AC%BE.png',
                        'text' => '抖音同款',
                        'jd_link' => '0',
                    ]
                ]
            ],
            'down_one' => [
                'title' => '天猫超市',
                'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/%E7%93%B7%E7%89%87%E5%9B%BE/05-%E6%B7%98%E9%A6%96%E9%A1%B5banner.png',
                'data' => $data_one,
            ],
            'down_two' => [
                'title' => '天猫国际',
                'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E6%96%B0%E6%89%8B%E6%94%BB%E7%95%A5banner%E5%9B%BE-1114/%E7%AC%AC%E4%BA%8C%E4%B8%AA.png',
                'data' => $data_two,
            ],
            'down_three' => $three_data,
        ]);
    }

    public function getAliSearch(Request $request)
    {

        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
        $post_data = json_decode($request->data, true);
        $rules = [
            'keyword' => 'required',
            'min_id' => 'integer',
            'sort' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]),
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $service_alimama = new AlimamaService();

        $sort_val = [
            2 => 'total_sales_des',
            3 => 'total_sales_asc',
            4 => 'price_des',
            5 => 'price_asc',
            6 => 'tk_total_commi_des',
            7 => 'tk_total_commi_asc',
        ];

        $params = [
            'q' => $post_data['keyword'],
            'ip' => $request->ip(),
            'page_no' => $post_data['min_id'],
        ];
        if (!empty($post_data['has_coupon'])) {
            $params['has_coupon'] = 'true';
        }

        if (in_array($post_data['sort'], [2, 3, 4, 5, 6, 7])) {
            $params['sort'] = $sort_val[$post_data['sort']];
        }
        $cache_str = $post_data['keyword'] . "_" . $post_data['min_id'] . "_" . $post_data['sort'];
        $cache_str = md5($cache_str);
        if (Cache::has($cache_str)) {
            $goods_data = Cache::get($cache_str);
            return $this->getResponse($goods_data);
        }

        $goods_data = $service_alimama->dgSearch($params);

        //获取成长值比例 计算次月最大送的成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($goods_data as &$v) {
            $v['growth_value_new_vip'] = (string)round(@$v['tkmoney_vip'] / $num_growth_value, 2);
            $v['growth_value_new_normal'] = (string)round(@$v['tkmoney_general'] / $num_growth_value, 2);
        }

        if (empty($goods_data)) {
            return $this->getInfoResponse('1001', '手速太快，请稍等片刻~');
        }

        Cache::put($cache_str, $goods_data, 1);

        return $this->getResponse($goods_data);


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getBigSearch(Request $request)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'keyword' => 'required',
            'min_id' => 'integer',
            'sort' => Rule::in([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]),
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $service_dataoke = new BigWashUser();

        $sort_val = [
            2 => 'total_sales_des',
            3 => 'total_sales_asc',
            4 => 'price_des',
            5 => 'price_asc',
        ];

        $params = [
            'pageNo' => $post_data['min_id'],
            'keyWords' => $post_data['keyword']
        ];

        if (in_array($post_data['sort'], [2, 3, 4, 5])) {
            $params['sort'] = $sort_val[$post_data['sort']];
        }


        $good_data = $service_dataoke->superSearch($params);

        if (empty($good_data)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        return $this->getResponse($good_data);
    }

    /**
     * 详情
     * 猜你喜欢
     */
    public function getDetail(Request $request)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'itemid' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $app_id = empty($post_data['app_id']) ? 0 : $post_data['app_id'];
        $itemid = $post_data['itemid'];

        $service_dataoke = new BigWashUser();

        $params = [
            'goodsId' => $post_data['itemid'],
        ];

        $goods_info = $service_dataoke->goodsDetailsImg($params);

        if (empty($goods_info)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }
        $obj_new_new_Collection = new NewNewCollection();

        $goods_info['is_collection'] = 0;
        if (!empty($app_id)) {
            $goods_info['is_collection'] = (int)$obj_new_new_Collection->where(['app_id' => $app_id, 'good_id' => $itemid])->value('id');
        }
        $params = [
            'q' => $goods_info['brand_name'],
            'ip' => $request->ip(),
            'page_no' => 1,
        ];

        $service_alimama = new AlimamaService();
        $goods_data = $service_alimama->dgSearch($params);

        //获取成长值比例 计算次月最大送的成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($goods_data as &$v) {
            $v['growth_value_new_vip'] = (string)round(@$v['tkmoney_vip'] / $num_growth_value, 2);
            $v['growth_value_new_normal'] = (string)round(@$v['tkmoney_general'] / $num_growth_value, 2);
        }

        $goods_info["guess_like"] = $goods_data;

        $goods_info["share_api_url"] = 'http://ax.k5it.com/h5_share/#/';

        //获取成长值比例 计算次月最大送的成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        $goods_info['growth_value_new_vip'] = round($goods_info['tkmoney_vip'] / $num_growth_value, 2);
        $goods_info['growth_value_new_normal'] = round($goods_info['tkmoney_general'] / $num_growth_value, 2);

        return $this->getResponse($goods_info);


    }

    /*
     * 分享商品
     */
    public function shareCommodity(Request $request, AlimamaInfoNew $alimamaInfo, TbkCashCreateServices $cashCreateServices)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'itemid' => 'required',
            'app_id' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }


        $service_dataoke = new BigWashUser();

        $params = [
            'goodsId' => $post_data['itemid'],
        ];

        $goods_info = $service_dataoke->goodsDetails($params);

        if (empty($goods_info)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        $rid = $alimamaInfo->where('app_id', $post_data['app_id'])->first();

        if (empty($rid)) {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
        }
        $share_url_change = $service_dataoke->newManyUrlChange($params, $rid->adzone_id);
        $joint_share_url_change = $share_url_change . '&relationId=' . $rid->relation_id;

        $tbk_command = $cashCreateServices->getTpwdCreate(@$goods_info['title'], $joint_share_url_change);

//        $goods_info['header'] = "http://api.36qq.com/api/xin_share_register?id=" . $post_data['app_id'];
//        $goods_info['header'] = "http://api_new.36qq.com/api/xin_share_register_new?id=" . $post_data['app_id'];
        $goods_info['header'] = "http://a001.p17t.com/share_register/#/?id=" . $post_data['app_id'];
        $goods_info['command'] = @$tbk_command['data']['model'];

        return $this->getResponse($goods_info);
    }

    /*
     * 阿里妈妈饿了么活动
     */
    public function getActivityLink(Request $request, AlimamaInfoNew $alimamaInfonew, TbkCashCreateServices $tbkCashCreateServices)
    {
        try {
            //取用户app_id
            $arrRequest['app_id'] = $request->input('app_id');
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                return ['error' => '1001', "error_msg" => "缺少必要参数"];
            }

            /***********************************/
            //开始处理逻辑问题
            //得到淘宝授权信息
            $rid = $alimamaInfonew->where('app_id', $arrRequest['app_id'])->first();
            if (empty($rid)) {
                return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
            }
            $adzone_id = $rid->adzone_id;
            $relation_id = $rid->relation_id;

            //得到转链url
            $res = $tbkCashCreateServices->getActivityLink($adzone_id, $relation_id);

            return $this->getResponse(@$res['data']);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 零元购 - 大淘客转链
     */
    public function zeroChangeUrl(Request $request)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'itemid' => 'required',
            'app_id' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }


        $service_dataoke = new BigWashUser();

        $params = [
            'goodsId' => $post_data['itemid'],
        ];

        $rid = OneGoAlimamaInfo::where('app_id', $post_data['app_id'])->first();

        if (empty($rid)) {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
        }

        $share_url_change = $service_dataoke->zeroBuyUrlChange($params);

        if (empty($goods_info)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }
        $joint_share_url_change = $share_url_change . '&relationId=' . $rid->relation_id;

        return $this->getResponse($joint_share_url_change);
    }


    /**
     * url change
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function changeUrl(Request $request)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }

        $service_dataoke = new BigWashUser();

        $params = [
            'goodsId' => $itemid,
        ];

        $goods_url = $service_dataoke->urlChange($params);

        if (empty($goods_url)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        return $this->getResponse($goods_url);
    }

    /**
     * 最新版转链20190904
     */
    public function getRushChangeUrl(Request $request, AlimamaInfo $alimamaInfo)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }
        $app_id = $request->get('app_id');
        if (empty($app_id)) {
            throw new ApiException('缺少必要参数！,错误信息', 3002);
        }

        $service_dataoke = new BigWashUser();

        $params = [
            'goodsId' => $itemid,
        ];
        $rid = $alimamaInfo->where('app_id', $app_id)->value('relation_id');

        if (empty($rid)) {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
        }
        $share_url_change = $service_dataoke->newUrlChange($params);
        $joint_share_url_change = $share_url_change . '&relationId=' . $rid;

        if (empty($joint_share_url_change)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        return $this->getResponse($joint_share_url_change);
    }

    /**
     * 最新版转链20190924
     */
    public function getMyWuChangeUrl(Request $request, AlimamaInfo $alimamaInfo)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }
        $app_id = $request->get('app_id');
        if (empty($app_id)) {
            throw new ApiException('缺少必要参数！,错误信息', 3002);
        }

        $service_dataoke = new BigWashUser();

        $params = [
            'goodsId' => $itemid,
        ];
        $rid = $alimamaInfo->where('app_id', $app_id)->first();

        if (empty($rid)) {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
        }
        $share_url_change = $service_dataoke->newManyUrlChange($params, $rid->adzone_id);
        $joint_share_url_change = $share_url_change . '&relationId=' . $rid->relation_id;

        if (empty($joint_share_url_change)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        return $this->getResponse($joint_share_url_change);
    }


    /**
     * 最新版转链20191105
     */
    public function getMyWuChangeUrlNew(Request $request, AlimamaInfoNew $alimamaInfoNew)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }
        $app_id = $request->get('app_id');
        if (empty($app_id)) {
            throw new ApiException('缺少必要参数！,错误信息', 3002);
        }

        $service_dataoke = new BigWashUser();

        $params = [
            'goodsId' => $itemid,
        ];
        $rid = $alimamaInfoNew->where('app_id', $app_id)->first();

        if (empty($rid)) {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
        }
        $share_url_change = $service_dataoke->newManyUrlChange($params, $rid->adzone_id);
        $joint_share_url_change = $share_url_change . '&relationId=' . $rid->relation_id;

        if (empty($share_url_change)) {
            return $this->getInfoResponse('4004', '该商品没有对应的链接！');
        }

        if (empty($joint_share_url_change)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        return $this->getResponse($joint_share_url_change);
    }

    /*
     * 大淘客搜索
     */
    public function getDtkSearch(Request $request, GrowthUserValueConfig $growthUserValueConfig)
    {
        $page_id = empty($request->get('page')) ? 1 : $request->get('page');
        //取搜索所需参数
        $str_tao_keyword_value = $growthUserValueConfig->value('tao_keyword_value');
        $num_price_lower_limit = $growthUserValueConfig->value('price_lower_limit');
        $num_price_upper_limit = $growthUserValueConfig->value('price_upper_limit');
        $num_commission_rate_lower_limit = $growthUserValueConfig->value('commission_rate_lower_limit');
        $params = [
            'keyWords' => $str_tao_keyword_value,                            #搜索关键字
            'priceLowerLimit' => $num_price_lower_limit,                     #价格（券后价）下限
            'priceUpperLimit' => $num_price_upper_limit,                     #价格（券后价）上限
            'commissionRateLowerLimit' => $num_commission_rate_lower_limit,  #最低佣金比例
            'couponPriceLowerLimit' => '1',                                  #最低优惠面额
            'cids' => '1,2,3,4,5,6,7,8,9,10,11,12,13,14',                    #一级类目
            'sort' => '2',                                                   #排序
            'pageId' => $page_id,                                            #页码
        ];

        $service_dataoke = new BigWashUser();
        $good_data = $service_dataoke->getDtkSearchGoods($params);

        //获取成长值比例 计算次月最大送的成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($good_data as &$v) {
            $v['growth_value_new_vip'] = (string)round(@$v['tkmoney_vip'] / $num_growth_value, 2);
            $v['growth_value_new_normal'] = (string)round(@$v['tkmoney_general'] / $num_growth_value, 2);
        }

        return $this->getResponse($good_data);
    }

    /*
     * 淘首页 整改补充
     */
    public function getTaoIndexData(Request $request)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'integer',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        //取为你推荐关键字
//        $obj_growth_user_value_config = new GrowthUserValueConfig();
//        $str_tao_you_recommend_key = $obj_growth_user_value_config->value('tao_you_recommend_key');

        $obj_big_wash_user = new BigWashUser();
        $hot = $obj_big_wash_user->getRankingList(['rankType' => 3]);        #大淘客 热门推荐
        $loophole = $obj_big_wash_user->getRankingList(['rankType' => 2]);   #大淘客 漏单
        $spree = $obj_big_wash_user->ddqGoodsList();                         #大淘客 限时抢购
//        $recommend = $obj_big_wash_user->getDtkSearchGoods(['keyWords' => $str_tao_you_recommend_key] );#为你推荐

        //截取前6条数据
//        $recommend = array_slice($recommend, 0, 6);
        $loophole = array_slice($loophole, 0, 6);
        $hot = array_slice($hot, 0, 5);

        //去掉抢购的商品列表
//        unset($spree['goodsList']);

        //预估成长值和vip预估成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($hot as &$v_hot) {
            $v_hot['growth_value_new_vip'] = (string)round(@$v_hot['tkmoney_vip'] / $num_growth_value, 2);
            $v_hot['growth_value_new_normal'] = (string)round(@$v_hot['tkmoney_general'] / $num_growth_value, 2);
        }
        foreach ($loophole as &$v_loophole) {
            $v_loophole['growth_value_new_vip'] = (string)round(@$v_loophole['tkmoney_vip'] / $num_growth_value, 2);
            $v_loophole['growth_value_new_normal'] = (string)round(@$v_loophole['tkmoney_general'] / $num_growth_value, 2);
        }

        $arr_data = [
            'hot' => $hot,            #热门推荐
            'loophole' => $loophole,  #漏单
            'spree' => $spree['roundsList'],        #限时抢购
//            'recommend' => $recommend,#为你推荐
        ];

        return $this->getResponse($arr_data);
    }

    /*
    * 淘首页 为你推荐
    */
    public function getTaoIndexRecommend(Request $request)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'integer',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        //取为你推荐关键字
        $obj_growth_user_value_config = new GrowthUserValueConfig();
        $str_tao_you_recommend_key = $obj_growth_user_value_config->value('tao_you_recommend_key');

        $page = empty($post_data['page']) ? 1 : $post_data['page'];

        $params = [
            'keyWords' => $str_tao_you_recommend_key,
            'pageId' => $page
        ];

        $obj_big_wash_user = new BigWashUser();
        $recommend = $obj_big_wash_user->getDtkSearchGoods($params);#为你推荐

        //截取前6条数据
        $recommend = array_slice($recommend, 0, 6);

        //预估成长值和vip预估成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($recommend as &$v_recommend) {
            $v_recommend['growth_value_new_vip'] = (string)round(@$v_recommend['tkmoney_vip'] / $num_growth_value, 2);
            $v_recommend['growth_value_new_normal'] = (string)round(@$v_recommend['tkmoney_general'] / $num_growth_value, 2);
        }

        return $this->getResponse($recommend);
    }

    /*
     * 淘首页 限时抢购商品栏
     */
    public function getTaoIndexGoods(Request $request)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'integer',
            'round_time' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $obj_big_wash_user = new BigWashUser();

        $params = [
            'roundTime' => $post_data['round_time'],
        ];

        $spree = $obj_big_wash_user->ddqGoodsList($params);                         #大淘客 限时抢购

        if (!empty($spree['goodsList'])) {
            $spree = $spree['goodsList'];
            $spree = array_slice($spree, 0, 6);
        } else {
            $spree = [];
        }

//        $arr_data = [
//            'spree' => $spree,        #限时抢购
//        ];

        //预估成长值和vip预估成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($spree as &$v_spree) {
            $v_spree['growth_value_new_vip'] = (string)round(@$v_spree['tkmoney_vip'] / $num_growth_value, 2);
            $v_spree['growth_value_new_normal'] = (string)round(@$v_spree['tkmoney_general'] / $num_growth_value, 2);

            //判断图片是否缺失https
            if (!strstr($v_spree['img'], 'https:')) {
                $v_spree['img'] = 'https:' . $v_spree['img'];
            }
        }

        return $this->getResponse($spree);
    }

    /*
     * 淘首页 漏洞单
     */
    public function getLoopholeData(Request $request)
    {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'integer',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $obj_big_wash_user = new BigWashUser();
        $loophole = $obj_big_wash_user->getRankingList(['rankType' => 2]);   #大淘客 漏单
        $loophole = array_slice($loophole, 0, 20);


        //预估成长值和vip预估成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');
        foreach ($loophole as &$v_loophole) {
            $v_loophole['growth_value_new_vip'] = (string)round(@$v_loophole['tkmoney_vip'] / $num_growth_value, 2);
            $v_loophole['growth_value_new_normal'] = (string)round(@$v_loophole['tkmoney_general'] / $num_growth_value, 2);
        }

        return $this->getResponse($loophole);
    }
}
