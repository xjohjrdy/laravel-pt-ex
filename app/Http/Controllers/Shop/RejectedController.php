<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\ReturnBack;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndex;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersOne;
use App\Entitys\App\ShopVipBuy;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RejectedController extends Controller
{
    /**
     * (拉出列表，仅用于退款与售后)
     * get {"app_id":"1"}
     * @param Request $request
     * @param ReturnBack $returnBack
     * @param ShopOrdersOne $shopOrdersOne
     * @param ShopGoods $shopGoods
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, ReturnBack $returnBack, ShopOrdersOne $shopOrdersOne, ShopGoods $shopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $res = $returnBack->getAllUserBack($arrRequest['app_id']);
            foreach ($res as $k => $v) {
                $order_one = $shopOrdersOne->getOneById($v->orders_one_id);
                if ($order_one) {
                    $good = $shopGoods->getOneById($order_one->good_id, 0);
                    if ($good) {
                        $header_img = json_decode($good->header_img, true);
                        if (array_key_exists(0, $header_img)) {
                            $header_img = $header_img[0];
                        } else {
                            $header_img = '';
                        }
                    } else {
                        throw new ApiException('商品不存在', '4004');
                    }
                } else {
                    throw new ApiException('订单不存在', '4004');
                }
                $res[$k]->good_id = $good->id;
                $res[$k]->title = $good->title;
                $res[$k]->header_img = $header_img;
                $res[$k]->real_price = $order_one->real_price;
                $res[$k]->number = $order_one->number;


                $good_id = $good->id;
                $app_id = $arrRequest['app_id'];
                $res_one = $shopGoods->getOneById($good->id, 0);
                $adUserInfo = new AdUserInfo();
                $user = $adUserInfo->appToAdUserId($app_id);
                //获取成长值比例 计算次月最大送的成长值
                $obj_growth_user_value_Config = new GrowthUserValueConfig();
                $num_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');

                if (!empty($user->groupid)) {
                    if ($user->groupid < 23) {
                        $res_one->profit_value = number_format($res_one->profit_value * 0.41 * 0.3, 2);
                    } else {
                        $res_one->profit_value = number_format($res_one->profit_value * 0.41 * 0.6, 2);
                    }
                } else {
                    $res_one->profit_value = number_format($res_one->profit_value * 0.41 * 0.3, 2);
                }

                //判断是不是vip商品
//                $obj_shop_vip_buy = new ShopVipBuy();
//                $is_vip_shop = $obj_shop_vip_buy->where('vip_id', $good_id)->first();
//                if (empty($is_vip_shop)) {
//                    $res_one->is_vip_goods = 0;
//                    $is_refund = round($res_one->profit_value / $num_growth_value, 2);
//                } else {
//                    $res_one->is_vip_goods = 1;
//                    $is_refund = $is_vip_shop->can_active;
//                }
//
//                if ($is_refund >= 20) {
//                    $is_refund = 0;
//                } else {
//                    $is_refund = 1;
//                }
                $res[$k]->is_refund = 1;

            }
            return $this->getResponse($res);

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }

    /**
     * 发起退款(用于订单状态待收货)
     * （把退款、退换货信息存入数据库）
     *  get {"order_one_id":"1","app_id":"1"}
     * remark_desc 可以为空，但是必须要占位
     * @param Request $request
     * @param ReturnBack $returnBack
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function create(Request $request, ReturnBack $returnBack, ShopOrdersOne $shopOrdersOne, ShopIndex $shopIndex)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('order_one_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $order_one = $shopOrdersOne->getOneById($arrRequest['order_one_id']);
            if ($order_one && $order_one->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('4004', '用户没有这个商品，请重新选择');
            }
            $shopOrders = new ShopOrders();
            $order = $shopOrders->getById($order_one->order_id);
            if ($order->status > 2) {
                return $this->getInfoResponse('4001', '主订单状态异常，无法发起退款！');
            }
            if ($order_one->status > 2) {
                return $this->getInfoResponse('4001', '您已申请退款，请勿重复操作！');
            }
            if ($order_one->status < 1) {
                return $this->getInfoResponse('4003', '您已申请退款，请勿重复操作！');
            }

            if ($shopIndex->isVipGoods($order_one->good_id)) {
                return $this->getInfoResponse('4002', '礼包商品不支持退款');
            }


            $shopGoods = new ShopGoods();
            $res_one = $shopGoods->getOneById($order_one->good_id, 0);
            //获取成长值比例 计算次月最大送的成长值
            $obj_growth_user_value_Config = new GrowthUserValueConfig();
            $num_growth_value = $obj_growth_user_value_Config->value('growth_shop_config_value');
            //判断是不是vip商品
            $obj_shop_vip_buy = new ShopVipBuy();
            $is_vip_shop = $obj_shop_vip_buy->where('vip_id', $order_one->good_id)->first();
            if (empty($is_vip_shop)) {
                $res_one->is_vip_goods = 0;
                $is_refund = round($res_one->profit_value / $num_growth_value, 2);
            } else {
                $res_one->is_vip_goods = 1;
                $is_refund = $is_vip_shop->can_active;
            }

            if ($is_refund >= 20) {
                $is_refund = 0;
            } else {
                $is_refund = 1;
            }


            $res = $returnBack->createNewBack($arrRequest['app_id'], $arrRequest['order_one_id']);

            $res->is_refund = $is_refund;
            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
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
     * 展示退换货界面信息，根据状态采用不同的情况
     * Display the specified resource.
     * get {"app_id":"1","order_one_id":"1"}
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request, ReturnBack $returnBack)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('order_one_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $res = $returnBack->getBack($arrRequest['app_id'], $arrRequest['order_one_id']);

            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }

    /**
     *
     * 换货（再次发货）（填写换货的收货地址与其他信息）(此处后台审核以后，状态才会变化)
     * 地址由两个参数连接拼凑而成
     * Show the form for editing the specified resource.
     * get {"address":"123","collection":"123","phone":"123","app_id":"1","order_one_id":"1"}
     * @param $id
     * @param Request $request
     * @param ReturnBack $returnBack
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function edit($id, Request $request, ReturnBack $returnBack)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('order_one_id', $arrRequest) || !array_key_exists('address', $arrRequest) || !array_key_exists('collection', $arrRequest) || !array_key_exists('phone', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $returnBack->updateReturnBack($arrRequest['app_id'], $arrRequest['order_one_id'], $arrRequest['address'], $arrRequest['phone'], $arrRequest['collection']);

            return $this->getResponse('成功！');

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }

    }

    /**
     * ?enter=1   (此处要后台审核过了，才能调用这个接口,后台审核需要扣除相对应的分佣)
     * 用户退货发货（填写快递单号）{"app_id":"1","order_one_id":"1","express":""} (其他参数是后台编辑写入的)  enter 1
     * 也可以用来(修改申请) {"order_one_id":"1","app_id":"1","remark":"商品有问题","remark_desc":"123测试","remark_img":"http://www.baidu.com,http://www.baidu.com","type":"1"}  enter 2
     * 也可以用来（审核失败以后重新填写） {"order_one_id":"1","app_id":"1","remark":"商品有问题","remark_desc":"123测试","remark_img":"http://www.baidu.com,http://www.baidu.com","type":"1"}
     * @param $id
     * @param Request $request
     * @param ReturnBack $returnBack
     * @param ShopOrdersOne $shopOrdersOne
     * @param AppUserInfo $appUserInfo
     * @param ShopOrders $shopOrders
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update($id, Request $request, ReturnBack $returnBack, ShopOrdersOne $shopOrdersOne, AppUserInfo $appUserInfo, ShopOrders $shopOrders)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest) || !array_key_exists('order_one_id', $arrRequest)) {
                throw new ApiException('传入参数错误！', '3001');
            }

            if ($request->enter == 1) {
                if (!array_key_exists('express', $arrRequest)) {
                    throw new ApiException('传入参数错误！！', '3002');
                }
                $returnBack->updateExpress($arrRequest['app_id'], $arrRequest['order_one_id'], $arrRequest['express']);
            }

            if ($request->enter == 2) {
                if (!array_key_exists('remark', $arrRequest) || !array_key_exists('remark_desc', $arrRequest) || !array_key_exists('type', $arrRequest)) {
                    throw new ApiException('传入参数错误！！！', '3003');
                }
                $arrRequest['remark_img'] = $request->remark_img;
                if (!$arrRequest['remark_img']) {
                    $arrRequest['remark_img'] = ' ';
                }
                if (empty($arrRequest['remark_type'])) {
                    $arrRequest['remark_type'] = 1;
                } else {
                    $app_user = $appUserInfo->getUserById($arrRequest['app_id']);
                    if (empty($app_user->alipay)) {
                        return $this->getInfoResponse('3004', '您未填写支付宝账号，请先前往填写支付宝账号！');
                    }
                    $shop_order_one_cart = $shopOrdersOne->getOneById($arrRequest['order_one_id']);
                    $count_orders = $shopOrdersOne->getAllGoods($shop_order_one_cart->order_id)->count();
                    if ($count_orders > 1) {
                        return $this->getInfoResponse('3005', '购物车购买的商品不支持支付宝退款！');
                    }
                }
                $returnBack->updateBack($arrRequest['app_id'], $arrRequest['order_one_id'], $arrRequest['remark_desc'], $arrRequest['remark'], $arrRequest['remark_img'], $arrRequest['type'], $arrRequest['remark_type']);
            }
            $shopOrdersOne->updateStatusById($arrRequest['order_one_id'], 4);
            $shop_order_one = $shopOrdersOne->getOneById($arrRequest['order_one_id']);
            if (!$shop_order_one) {
                return $this->getInfoResponse('4004', '订单情况不存在！');
            }
            $shopOrders->updateStatusOrders($shop_order_one->order_id, 4);

            return $this->getResponse('成功！');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }

    }

    /**
     * 取消申请！
     * @param $id
     * @param Request $request
     * @param ReturnBack $returnBack
     * @param ShopOrdersOne $shopOrdersOne
     * @param ShopOrders $shopOrders
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function destroy($id, Request $request, ReturnBack $returnBack, ShopOrdersOne $shopOrdersOne, ShopOrders $shopOrders)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('rejected_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $return_back = $returnBack->getBackById($arrRequest['rejected_id']);

            if (!$return_back) {
                throw new ApiException('退款情况不存在！', '4004');
            }

            if ($return_back->status <> 2) {
                throw new ApiException('退款情况错误！', '4004');
            }

            $shop_order_one = $shopOrdersOne->getOneById($return_back->orders_one_id);

            if (!$shop_order_one) {
                throw new ApiException('订单情况不存在！', '4004');
            }

            $shopOrdersOne->updateStatusById($return_back->orders_one_id, 2);
            $shopOrders->updateStatusOrders($shop_order_one->order_id, 2);
//            $returnBack->where(['id' => $arrRequest['rejected_id']])->delete();

            return $this->getResponse('成功！');

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
        }
    }
}
