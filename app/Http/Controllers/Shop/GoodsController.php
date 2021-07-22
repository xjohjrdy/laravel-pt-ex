<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndex;
use App\Entitys\App\ShopVipBuy;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GoodsController extends Controller
{
    /**
     * 展示商品列表 ?page=1
     * get {"app_id":"1","sort":"","title":"\u8863\u670d"}
     * @param Request $request
     * @param ShopGoods $shopGoods
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, ShopGoods $shopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('sort', $arrRequest) || !array_key_exists('title', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            $res = $shopGoods->getAllGoods($arrRequest['sort'], $arrRequest['title']);
            $res->map(function ($model) {
                $model->header_img = json_decode($model->header_img);
                $model->ptb_price = $model->price * 10;
            });
            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * 查询出单个商品详情
     * Display the specified resource.
     * get {"app_id":"123"}
     * @param $id
     * @param Request $request
     * @param ShopGoods $shopGoods
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, ShopGoods $shopGoods, AdUserInfo $adUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $res = $shopGoods->getOneGood($id);
            //兼容
            $profit_value = $res->profit_value;

            if (!$res) {
                return $this->getInfoResponse('4004', '当前商品已经被下架！暂时无法购买');
            }
            $shopGoods->increaseClickNumber($id);
            $res->ptb_price = $res->price * 10;
            $res->real_test_price = $res->price * 1.2;
            $res->header_img = json_decode($res->header_img, true);
            $res->parameter = json_decode($res->parameter);
            $res->custom = json_decode($res->custom);
            $res->detail_img = json_decode($res->detail_img, true);
            if (empty($res->detail_img)) {
                $res->detail_img = '';
            } else {
                $arr_arr = $res->detail_img;
                $arr_arr[0] = $res->detail_img[0] . '/yasuo-123';
                $res->detail_img = $arr_arr;
            }
            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            if (!empty($user->groupid)) {
                if ($user->groupid < 23) {

                    $res->profit_value = number_format($res->profit_value * 0.41 * 0.3, 2);

                } else {
                    $res->profit_value = number_format($res->profit_value * 0.41 * 0.6, 2);
                    $obj_shop_index = new ShopIndex();
                    if (!$obj_shop_index->isVipGoods($id) && $user->groupid == 24) {
                        $res->profit_value = number_format($res->profit_value * 1.2, 2);
                    }
                }
            } else {
                $res->profit_value = number_format($res->profit_value * 0.41 * 0.3, 2);
            }

            $res->remain_time = $res->open_time - time();
            $res->video_url = json_decode($res->video_url);
            $res->detail_share_img = json_decode($res->detail_share_img, true);

            //获取成长值比例 计算次月最大送的成长值
            $obj_growth_user_value_Config = new GrowthUserValueConfig();
            $num_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');

            //判断是不是vip商品
            $obj_shop_vip_buy = new ShopVipBuy();
            $is_vip_shop = $obj_shop_vip_buy->where('vip_id', $id)->first();
            if (empty($is_vip_shop)) {
                $res->is_vip_goods = 0;

                //整改成长值
                if (!empty($user->groupid)) {
                    if ($user->groupid < 23) {
                        if (!($res->can_active > 0)) {
                            $res->growth_value_new = round($res->profit_value / $num_growth_value * 8, 2);
                        } else {
                            //普通用户 普通商品 填了成长值的情况下 利润值改为*0.05 成长值直接取值
                            $res->profit_value = number_format($profit_value * 0.41 * 0.05, 2);
                            $res->growth_value_new = round($res->can_active, 2);
                        }
                    } else {
                        $res->growth_value_new = round($res->profit_value / $num_growth_value, 2);
                    }

                } else {
                    $res->growth_value_new = round($res->profit_value / $num_growth_value, 2);
                }
            } else {
                $res->is_vip_goods = 1;
                $res->growth_value_new = $is_vip_shop->can_active;

                if (!empty($user->groupid)) {
                    if ($user->groupid == 23) {
                        $res->profit_value = number_format($is_vip_shop->maid * 0.56, 2);
                    } elseif ($user->groupid == 24) {
                        $res->profit_value = number_format($is_vip_shop->maid * 0.67, 2);
                    } else {
                        $res->profit_value = number_format($is_vip_shop->maid * 0.05, 2);
                    }
                } else {
                    $res->profit_value = 0;
                }
            }

            return $this->getResponse($res);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * 确认收货的内容提示
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInfoMessage()
    {
        return $this->getResponse('注意：请确保收到产品后再进行确认收货。确认收货后无法申请退货退款。若恶意确认收货导致活跃值虚增，则双倍扣除活跃值。');
    }

    /*
     * vip商品指定
     */
    public function vipCommodityAssign(Request $request, ShopVipBuy $shopVipBuy, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            /***********************************/
//            if (Cache::has("vip_commodity_assign_" . $app_id)) {
//                return $this->getResponse(Cache::get("vip_commodity_assign_" . $app_id));
//            }
            $parent_id = $appUserInfo->where('id', $app_id)->value('parent_id');
            if (!empty($parent_id)) {
                $data = $shopVipBuy->where('is_up', 0)->where('is_show', 0)->get();
            } else {
                $data = $shopVipBuy->where('is_up', 0)->get();
            }
//            Cache::put("vip_commodity_assign_" . $app_id, $data, 10);

            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
