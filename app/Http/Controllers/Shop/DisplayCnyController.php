<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAccount;
use App\Entitys\App\CommandConfig;
use App\Entitys\App\PretendShopOrdersMaid;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndex;
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\StartPageIndex;
use App\Exceptions\ApiException;
use App\Services\Shop\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class DisplayCnyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ShopIndex $shopIndex, ShopGoods $shopGoods)
    {
        $cache_key = 'shop_good_index_wuhang_2019_0925_cache';
        if (!Cache::has($cache_key)) {
            $res = $shopIndex->where('deleted_at', '=', null)->get(['id', 'content', 'remark', 'key'])->toArray();

            foreach ($res as $k => $v) {
                if ($v['id'] <= 5) {
                    $res['index_img'][] = $res[$k];
                    unset($res[$k]);
                };
                if ($v['id'] >= 6 && $v['id'] <= 15) {
                    if ($v['id'] == 11 || $v['id'] == 12 || $v['id'] == 13 || $v['id'] == 14 || $v['id'] == 15) {
                        unset($res[$k]);
                    } else {
                        $res['sort'][] = $res[$k];
                        unset($res[$k]);
                    }
                }
                if ($v['id'] == 16) {
                    $res['middle_img'] = $res[$k];
                    unset($res[$k]);
                }

                if ($v['id'] == 17) {
                    $good_buy = $shopGoods->getOneById($v['key']);
                    $good_buy->ptb_price = $good_buy->price * 10;
                    $header_img_buy = json_decode($good_buy->header_img, true);
                    if (!array_key_exists(1, $header_img_buy)) {
                        $good_buy->header_img = null;
                    } else {
                        $good_buy->header_img = $header_img_buy[1];
                    }
                    $good_buy->percent = $good_buy->sale_volume / ($good_buy->sale_volume + $good_buy->volume);
                    $res['buy'] = $good_buy;
                    unset($res[$k]);
                }

                if ($v['id'] == 19 || $v['id'] == 21) {
                    unset($res[$k]);
                }
                if ($v['id'] == 23 || $v['id'] == 24) {
                    unset($res[$k]);
                }
                if ($v['id'] == 25) {
                    $content = json_decode($v['content'], true);
                    if (!empty($content)) {
                        foreach ($content as $key => $value) {
                            $good = $shopGoods->getOneById($key);
                            if (!$good) {
                                continue;
                            }
                            $header_img = $value;
                            $good->header_img = $header_img;
                            $res['recommend'][] = $good->toArray();
                        }
                    }
                    unset($res[$k]);
                }

                if ($v['id'] == 27) {
                    $content = json_decode($v['content'], true);
                    if (!empty($content)) {
                        foreach ($content as $key => $value) {
                            $good = $shopGoods->getOneById($key);
                            if (!$good) {
                                continue;
                            }
                            $header_img = json_decode($good->header_img, true);
                            if (array_key_exists(1, $header_img)) {
                                $good->header_img = $header_img[0];
                            } else {
                                $good->header_img = null;
                            }
                            $res['hot'][] = $good->toArray();
                        }
                    }
                    unset($res[$k]);
                }

            }


            Cache::put($cache_key, $res, 10);
        }
        $res = Cache::get($cache_key);


        return $this->getResponse($res);
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
     * ????????????????????????
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->getResponse(['wechat' => 'put238', 'qq' => '3092678892']);
    }

    public function showRsa(Request $request, PretendShopOrdersMaid $ordersMaid, ShopOrdersMaid $shopOrdersMaid, UserAccount $userAccount, AdUserInfo $adUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('??????????????????', '3001');
            }

            //???????????????????????? ????????????????????? ???????????????????????????????????????
            $obj_start_page_index = new StartPageIndex();
            $data_obj_start_page_index = $obj_start_page_index->where('app_id', $arrRequest['app_id'])->first();

            //????????????????????? ??????
            $Obj_Command_config = new CommandConfig();
            $obj_data = $Obj_Command_config->where('id', 1)->first();
            if ($obj_data->status == 0) {
                $int_command_time = $obj_data->end_time;
            } else {
                $int_command_time = $obj_data->start_time;
            }
            $str_command_time = date('Y-m-d H:i:s', $int_command_time);

            //??????????????????????????????????????????????????? + ????????????????????????????????????
            $allPtb = $ordersMaid->getCountMoney($arrRequest['app_id'], [['created_at', '>', $str_command_time]]);
            $allPtb_command = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->pretend_all_user_get;
            $allPtb = $allPtb + $allPtb_command;

            $thisMonthPtb = $ordersMaid->getCountMoney($arrRequest['app_id'], [['created_at', '>=', date("Y-m-01")]]);
            $todayPtb = $ordersMaid->getCountMoney($arrRequest['app_id'], [['created_at', '>=', date("Y-m-d")]]);
            $adInfo = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            if (empty($adInfo)) {
                return $this->getInfoResponse('3002', '??????????????????????????????????????????????????????????????????');
            }
            $uid = $adInfo['uid'];
            $accountInfo = $userAccount->getUserAccount($uid);
            if (empty($accountInfo)) {
                return $this->getInfoResponse('3003', '??????????????????????????????????????????????????????????????????');
            }

            //?????????????????????????????????????????????????????? + ????????????????????????????????????
//            $accountPtb = $shopOrdersMaid->getAllCreditLog($arrRequest['app_id'])->sum('money');
            $obj_accountPtb = $shopOrdersMaid->where(['app_id' => $arrRequest['app_id']])
                ->where('created_at', '>', $str_command_time)
                ->orderByDesc('updated_at')
                ->get();
            $accountPtb = $obj_accountPtb->sum('money');
            $accountPtb_conmmon = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->all_user_get;
            $accountPtb = $accountPtb + $accountPtb_conmmon;

            return $this->getResponse([
                'wechat' => 'put238',
                'qq' => '3092678892',
                'account' => (int)round(($accountPtb / 10), 2),
                'commission' => [
                    'all' => (int)$allPtb / 10,
                    'this_month' => (int)$thisMonthPtb / 10,
                    'today' => (int)$todayPtb / 10
                ]
            ]);

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('????????????????????????????????????', '500');
        }


    }

    /**
     * ????????????????????????
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function showUser(Request $request, $id)
    {
        return $this->getResponse(['wechat' => 'put238', 'qq' => '3092678892']);
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
     * ???????????????????????????
     */
    public function getIndexDisplay(ShopIndex $shopIndex, ShopGoods $shopGoods)
    {
        try {


            $cache_key = 'shop_good_display_1_index_wuhang_2019_0925_cache';
            if (!Cache::has($cache_key)) {
                $shop_index = $shopIndex->where(['id' => 26])->get();
                $shop_index = $shop_index->toArray();
                $content = json_decode($shop_index[0]['content'], true);
                $arr_special_id = [];
                if ($content) {
                    foreach ($content as $k => $item) {
                        $good = $shopGoods->getOneById($k);
                        if ($good) {
                            $arr_special_id[] = $good->id;
                        }
                    }
                }

                Cache::put($cache_key, $arr_special_id, 10);
            }

            $arr_special_id = Cache::get($cache_key);

            return $this->getResponse([
                'head_title' => '????????????',
                'head_one' => [
                    'icon' => 'https://a119112.oss-cn-beijing.aliyuncs.com/%E5%95%86%E5%9F%8E%E9%A6%96%E9%A1%B5/%E5%B0%8F%E5%9B%BE%E6%A0%87/home1_icon_baoyou%403x.png',
                    'remark' => '????????????',
                ],
                'head_two' => [
                    'icon' => 'https://a119112.oss-cn-beijing.aliyuncs.com/%E5%95%86%E5%9F%8E%E9%A6%96%E9%A1%B5/%E5%B0%8F%E5%9B%BE%E6%A0%87/home1_icon_share%403x.png',
                    'remark' => '????????????',
                ],
                'head_three' => [
                    'icon' => 'https://a119112.oss-cn-beijing.aliyuncs.com/%E5%95%86%E5%9F%8E%E9%A6%96%E9%A1%B5/%E5%B0%8F%E5%9B%BE%E6%A0%87/home1_icon_zheng%403x.png',
                    'remark' => '????????????',
                ],
                'today' => '????????????',
                'tomorrow' => '????????????',
                'yesterday' => '????????????',
                'hot' => '????????????',
                'img_slogan_one' => '??????????????????- ?????????????????????',
                'img_slogan_two' => '???????????????????????????',
                'qr_code_url' => 'http://api.36qq.com/api/xin_share_register_new?id=',
                'arr_special_id' => $arr_special_id,
                'special_phone' => ['13194089498', '15126372834', '13182748273'],
                'webURL' => 'http://express.qiehuo.net',
            ]);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('????????????????????????????????????', '500');
        }
    }

    /**
     * ????????????
     * {"address_id":"","order_id":""}
     */
    public function refreshExpress(Request $request, Order $order)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('address_id', $arrRequest) || !array_key_exists('order_id', $arrRequest)) {
                throw new ApiException('??????????????????', '3001');
            }


            $all_express = $order->noArea($arrRequest['address_id'], $arrRequest['order_id']);

            return $this->getResponse($all_express);

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('????????????????????????????????????', '500');
        }
    }

    /**
     * ??????????????????
     * @return \Illuminate\Http\JsonResponse
     */
    public function showReturnInfo(Request $request)
    {
        $good_id = 0;
        if ($request->header('id')) {
            $good_id = $request->header('id');
        }
        return $this->getResponse([
            'url' => 'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/%E7%89%A9%E6%B5%81.jpg',
            'info' => '',
            'good_id' => $good_id,
            'webURL' => 'http://express.qiehuo.net',
        ]);
    }
}
