<?php

namespace App\Http\Controllers\Pay;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\App\ShopOrders;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\Shop\Order;
use Illuminate\Support\Facades\Storage;
use Yansongda\Pay\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yansongda\Pay\Pay;

class RechargeAliPayController extends Controller
{
    //支付宝卖家号
    protected $PID = '2088531728490041';
    //

    /**
     * 此处建议移动到Recharge/Member控制器的store方法，进行加密处理以后，根据type区分，如果未支付宝直接反馈sign
     */
    public function getUserOrderSign(Request $request, RechargeUserLevel $rechargeUserLevel)
    {
        //此处是产生的方法
        $type = 1;
        $uid = 1;

        if ($type == 2) {
            list($order_id, $price, $desc) = $rechargeUserLevel->generatingOrder($uid);

            $order = [
                'out_trade_no' => $order_id,
                'total_amount' => $price,
                'subject' => $desc . ' - ' . $price . '元',
            ];

            //$alipay = Pay::alipay($this->config)->app($order);

            $alipay = Pay::alipay($this->config)->app($order);

            return $alipay;
        }
    }

    /**
     *
     * 阿里巴巴通知回调路由
     * @param RechargeOrder $rechargeOrder
     * @param RechargeUserLevel $rechargeUserLevel
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callBackForAli(RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, ShopOrders $shopOrders, Order $order_model)
    {
        $this->PID = config('unified_pay.ali_pid');
        $alipay = Pay::alipay($this->config);
        try {
            //写入文件，代表支付宝通知成功
//            file_put_contents('test_alipay_notify.txt', '---------------start--------' . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export('---------------start--------', true));
            $data = $alipay->verify(); // 验签
            //写入文件，写入通知的格式
//            file_put_contents('test_alipay_notify.txt', $data->trade_status . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->trade_status, true));
            // 只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            if ($data->trade_status == 'TRADE_SUCCESS' || $data->trade_status == 'TRADE_FINISHED') {
                //写入文件，判断是否有存在通知的各种参数
                // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
                $order = $rechargeOrder->getOrdersById($data->out_trade_no);
//                file_put_contents('test_alipay_notify.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->out_trade_no, true));
                // 第二种订单情况，如果存在则进入商品回调
                $shop_order = $shopOrders->getByOrderId($data->out_trade_no);
                if ($shop_order) {
//                    file_put_contents('test_alipay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

                    Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export($data->out_trade_no, true));
//                    if ($shop_order->real_price == $data->total_amount) {
                    // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
//                    file_put_contents('test_alipay_notify_shop.txt', $data->seller_id . PHP_EOL, FILE_APPEND);

                    Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export($data->seller_id, true));
                    if ($data->seller_id == $this->PID) {
                        // 4、验证app_id是否为该商户本身。
//                        file_put_contents('test_alipay_notify_shop.txt', $data->app_id . PHP_EOL, FILE_APPEND);

                        Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export($data->app_id, true));
                        if ($data->app_id == $this->config['app_id']) {
//                            file_put_contents('test_alipay_notify_shop.txt', "other " . PHP_EOL, FILE_APPEND);

                            Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export("other ", true));
                            if ($shop_order->status == 0) {
//                                file_put_contents('test_alipay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);

                                Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export("run", true));
                                $res_maid = $order_model->processOrder($shop_order->order_id);
                            }
                        }
                    }
//                    }
                }
                if ($order) {
                    // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
//                    file_put_contents('test_alipay_notify.txt', $data->total_amount . PHP_EOL, FILE_APPEND);

                    Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->total_amount, true));
//                    if ($order->uid == 1602765){
//                        $order->price = 0.01;
//                    }
//                    if ($order->price == $data->total_amount) {
                    // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
//                    file_put_contents('test_alipay_notify.txt', $data->seller_id . PHP_EOL, FILE_APPEND);

                    Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->seller_id, true));
                    if ($data->seller_id == $this->PID) {
                        // 4、验证app_id是否为该商户本身。
//                        file_put_contents('test_alipay_notify.txt', $data->app_id . PHP_EOL, FILE_APPEND);

                        Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->app_id, true));
                        if ($data->app_id == $this->config['app_id']) {
//                            file_put_contents('test_alipay_notify.txt', "other " . PHP_EOL, FILE_APPEND);

                            Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export("other ", true));
                            if ($order->status == 1) {
//                                file_put_contents('test_alipay_notify.txt', "run" . PHP_EOL, FILE_APPEND);

                                Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export("run", true));
                                // 5、其它业务逻辑情况
                                $arr = [
                                    'uid' => $order->uid,
                                    'money' => $order->price,
                                    'orderid' => $data->out_trade_no,
                                ];
                                if ($shop_order) {
                                    $arr = [
                                        'uid' => $order->uid,
                                        'money' => 800,
                                        'orderid' => $data->out_trade_no,
                                    ];
                                }
                                $rechargeUserLevel->initOrder($arr);
                                $rechargeUserLevel->updateExt(); //升级
                                //$data->total_amount;
                                if ($data->total_amount != 10) {
                                    $rechargeUserLevel->returnCommission(); //返佣
                                } else {
                                    $rechargeUserLevel->returnCommissionV12(); //返佣
                                }
                                $rechargeUserLevel->handleArticle(); //更新文章
                                $rechargeOrder->updateOrderStatus($data->out_trade_no);//更新订单
                            }
                        }
                    }
//                    }
                }
            }
//            file_put_contents('test_alipay_notify.txt', '---------------end--------' . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export('---------------end--------', true));

            //Log::debug('Alipay notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
        }
//        file_put_contents('test_alipay_notify.txt', '---------------' . $alipay->success() . '--------' . PHP_EOL, FILE_APPEND);

        Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export('---------------' . $alipay->success() . '--------', true));
        return $alipay->success();
    }


    /**
     *
     * 阿里巴巴通知回调路由 余额支付回调
     * @param RechargeOrder $rechargeOrder
     * @param RechargeUserLevel $rechargeUserLevel
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callBackForAliV1(RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, ShopOrders $shopOrders, Order $order_model)
    {
        $this->PID = config('unified_pay.ali_pid');
        $alipay = Pay::alipay($this->config);
        try {
            //写入文件，代表支付宝通知成功
//            file_put_contents('test_alipay_notify.txt', '---------------start--------' . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export('---------------start--------', true));
            $data = $alipay->verify(); // 验签
            //写入文件，写入通知的格式
//            file_put_contents('test_alipay_notify.txt', $data->trade_status . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->trade_status, true));
            // 只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            if ($data->trade_status == 'TRADE_SUCCESS' || $data->trade_status == 'TRADE_FINISHED') {
                //写入文件，判断是否有存在通知的各种参数
                // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
                $order = $rechargeOrder->getOrdersById($data->out_trade_no);
//                file_put_contents('test_alipay_notify.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->out_trade_no, true));
                // 第二种订单情况，如果存在则进入商品回调
                $shop_order = $shopOrders->getByOrderId($data->out_trade_no);
                if ($shop_order) {
//                    file_put_contents('test_alipay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

                    Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export($data->out_trade_no, true));
//                    if ($shop_order->real_price == $data->total_amount) {
                    // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
//                    file_put_contents('test_alipay_notify_shop.txt', $data->seller_id . PHP_EOL, FILE_APPEND);

                    Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export($data->seller_id, true));
                    if ($data->seller_id == $this->PID) {
                        // 4、验证app_id是否为该商户本身。
//                        file_put_contents('test_alipay_notify_shop.txt', $data->app_id . PHP_EOL, FILE_APPEND);

                        Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export($data->app_id, true));
                        if ($data->app_id == $this->config['app_id']) {
//                            file_put_contents('test_alipay_notify_shop.txt', "other " . PHP_EOL, FILE_APPEND);

                            Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export("other ", true));
                            if ($shop_order->status == 0) {
//                                file_put_contents('test_alipay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);

                                Storage::disk('local')->append('callback_document/test_alipay_notify_shop.txt', var_export("run", true));
                                $res_maid = $order_model->processOrderV1($shop_order->order_id, 0);
                            }
                        }
                    }
//                    }
                }
                if ($order) {
                    // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
//                    file_put_contents('test_alipay_notify.txt', $data->total_amount . PHP_EOL, FILE_APPEND);

                    Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->total_amount, true));
//                    if ($order->uid == 1602765){
//                        $order->price = 0.01;
//                    }
//                    if ($order->price == $data->total_amount) {
                    // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
//                    file_put_contents('test_alipay_notify.txt', $data->seller_id . PHP_EOL, FILE_APPEND);

                    Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->seller_id, true));
                    if ($data->seller_id == $this->PID) {
                        // 4、验证app_id是否为该商户本身。
//                        file_put_contents('test_alipay_notify.txt', $data->app_id . PHP_EOL, FILE_APPEND);

                        Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export($data->app_id, true));
                        if ($data->app_id == $this->config['app_id']) {
//                            file_put_contents('test_alipay_notify.txt', "other " . PHP_EOL, FILE_APPEND);

                            Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export("other ", true));
                            if ($order->status == 1) {
//                                file_put_contents('test_alipay_notify.txt', "run" . PHP_EOL, FILE_APPEND);

                                Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export("run", true));
                                // 5、其它业务逻辑情况
                                $arr = [
                                    'uid' => $order->uid,
                                    'money' => $order->price,
                                    'orderid' => $data->out_trade_no,
                                ];
                                if ($shop_order) {
                                    $arr = [
                                        'uid' => $order->uid,
                                        'money' => 800,
                                        'orderid' => $data->out_trade_no,
                                    ];
                                }
                                $rechargeUserLevel->initOrder($arr);
                                $rechargeUserLevel->updateExt(); //升级
                                //$data->total_amount;
                                if ($data->total_amount != 10) {
                                    $rechargeUserLevel->returnCommission(); //返佣
                                } else {
                                    $rechargeUserLevel->returnCommissionV12(); //返佣
                                }
                                $rechargeUserLevel->handleArticle(); //更新文章
                                $rechargeOrder->updateOrderStatus($data->out_trade_no);//更新订单
                            }
                        }
                    }
//                    }
                }
            }
//            file_put_contents('test_alipay_notify.txt', '---------------end--------' . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export('---------------end--------', true));

            //Log::debug('Alipay notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
        }
//        file_put_contents('test_alipay_notify.txt', '---------------' . $alipay->success() . '--------' . PHP_EOL, FILE_APPEND);

        Storage::disk('local')->append('callback_document/test_alipay_notify.txt', var_export('---------------' . $alipay->success() . '--------', true));
        return $alipay->success();
    }


    /**
     * 微信支付回调
     */
    public function callBackWechatPay(RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, ShopOrders $shopOrders, Order $order_model)
    {
//        file_put_contents('wechat_pay_notify_shop.txt', 'step-1' . PHP_EOL, FILE_APPEND);

        Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export('step-1', true));
        $pay = Pay::wechat($this->wechat_config);

        try {
            $data = $pay->verify(); // 是的，验签就这么简单！

//            file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->out_trade_no, true));
            if ($data->return_code <> "SUCCESS") {
//                file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->out_trade_no, true));
//                file_put_contents('wechat_pay_notify_shop.txt', $data->return_msg . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->return_msg, true));
                exit();
            }

            //拿到订单
            //  // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            $order = $rechargeOrder->getOrdersById($data->out_trade_no);
            // 第二种订单情况，如果存在则进入商品回调
            $shop_order = $shopOrders->getByOrderId($data->out_trade_no);
            if (!empty($shop_order)) {
                if ($shop_order->app_id == 1569840) {
                    $shop_order->real_price = 0.01;
                }

                //对比金额
//                file_put_contents('wechat_pay_notify_shop.txt', $data->total_fee . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->total_fee, true));
//                file_put_contents('wechat_pay_notify_shop.txt', $shop_order->real_price . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($shop_order->real_price, true));
                $computer_price = $shop_order->real_price * 100;
//                if ($data->total_fee == $computer_price) {
//                file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->out_trade_no, true));
//                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export("run", true));
                $res_maid = $order_model->processOrder($shop_order->order_id);
//                }
            }

            if (!empty($order)) {
                if ($order->uid == 1499531) {
                    $order->price = 0.01;
                }
//                if (($order->price * 100) <> $data->total_fee) {
//                    file_put_contents('wechat_pay_notify_shop.txt', '金额不对等' . PHP_EOL, FILE_APPEND);
//                    file_put_contents('wechat_pay_notify_shop.txt', $data->total_fee . PHP_EOL, FILE_APPEND);
//                    file_put_contents('wechat_pay_notify_shop.txt', "订单金额：" . $order->price . PHP_EOL, FILE_APPEND);
//                    exit();
//                }
//                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export("run", true));
                // 5、其它业务逻辑情况
                $arr = [
                    'uid' => $order->uid,
                    'money' => $order->price,
                    'orderid' => $data->out_trade_no,
                ];
                if ($shop_order) {
                    $arr = [
                        'uid' => $order->uid,
                        'money' => 800,
                        'orderid' => $data->out_trade_no,
                    ];
                }
//                $AdUserInfo = new AdUserInfo();
//                $x = $AdUserInfo->getUserById($order->uid);
//                if ($x->groupid <= 22) {
                $rechargeUserLevel->initOrder($arr);
                $rechargeUserLevel->updateExt(); //升级
                $rechargeUserLevel->returnCommission(); //返佣
                $rechargeUserLevel->handleArticle(); //更新文章
                $rechargeOrder->updateOrderStatus($data->out_trade_no);//更新订单
//                }
            }
//            file_put_contents('wechat_pay_notify_shop.txt', 'step-2' . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export('step-2', true));
        } catch (\Throwable $e) {
//            file_put_contents('wechat_pay_notify_shop.txt', 'error!need change is!' . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export('error!need change is!', true));
        }
//        return 1;
        return $pay->success();
        //return $pay->success()->send();// laravel 框架中请直接 `return $pay->success()`

    }

    /**
     * 微信支付回调 余额支付回调
     */
    public function callBackWechatPayV1(RechargeOrder $rechargeOrder, RechargeUserLevel $rechargeUserLevel, ShopOrders $shopOrders, Order $order_model)
    {
//        file_put_contents('wechat_pay_notify_shop.txt', 'step-1' . PHP_EOL, FILE_APPEND);

        Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export('step-1', true));
        $pay = Pay::wechat($this->wechat_config);

        try {
            $data = $pay->verify(); // 是的，验签就这么简单！

//            file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->out_trade_no, true));
            if ($data->return_code <> "SUCCESS") {
//                file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->out_trade_no, true));
//                file_put_contents('wechat_pay_notify_shop.txt', $data->return_msg . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->return_msg, true));
                exit();
            }

            //拿到订单
            //  // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            $order = $rechargeOrder->getOrdersById($data->out_trade_no);
            // 第二种订单情况，如果存在则进入商品回调
            $shop_order = $shopOrders->getByOrderId($data->out_trade_no);
            if (!empty($shop_order)) {
                if ($shop_order->app_id == 1569840) {
                    $shop_order->real_price = 0.01;
                }

                //对比金额
//                file_put_contents('wechat_pay_notify_shop.txt', $data->total_fee . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->total_fee, true));
//                file_put_contents('wechat_pay_notify_shop.txt', $shop_order->real_price . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($shop_order->real_price, true));
                $computer_price = $shop_order->real_price * 100;
//                if ($data->total_fee == $computer_price) {
//                file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export($data->out_trade_no, true));
//                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export("run", true));
                $res_maid = $order_model->processOrderV1($shop_order->order_id, 0);
//                }
            }

            if (!empty($order)) {
                if ($order->uid == 1499531) {
                    $order->price = 0.01;
                }
//                if (($order->price * 100) <> $data->total_fee) {
//                    file_put_contents('wechat_pay_notify_shop.txt', '金额不对等' . PHP_EOL, FILE_APPEND);
//                    file_put_contents('wechat_pay_notify_shop.txt', $data->total_fee . PHP_EOL, FILE_APPEND);
//                    file_put_contents('wechat_pay_notify_shop.txt', "订单金额：" . $order->price . PHP_EOL, FILE_APPEND);
//                    exit();
//                }
//                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);

                Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export("run", true));
                // 5、其它业务逻辑情况
                $arr = [
                    'uid' => $order->uid,
                    'money' => $order->price,
                    'orderid' => $data->out_trade_no,
                ];
                if ($shop_order) {
                    $arr = [
                        'uid' => $order->uid,
                        'money' => 800,
                        'orderid' => $data->out_trade_no,
                    ];
                }
//                $AdUserInfo = new AdUserInfo();
//                $x = $AdUserInfo->getUserById($order->uid);
//                if ($x->groupid <= 22) {
                $rechargeUserLevel->initOrder($arr);
                $rechargeUserLevel->updateExt(); //升级
                $rechargeUserLevel->returnCommission(); //返佣
                $rechargeUserLevel->handleArticle(); //更新文章
                $rechargeOrder->updateOrderStatus($data->out_trade_no);//更新订单
//                }
            }
//            file_put_contents('wechat_pay_notify_shop.txt', 'step-2' . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export('step-2', true));
        } catch (\Throwable $e) {
//            file_put_contents('wechat_pay_notify_shop.txt', 'error!need change is!' . PHP_EOL, FILE_APPEND);

            Storage::disk('local')->append('callback_document/wechat_pay_notify_shop.txt', var_export('error!need change is!', true));
        }
//        return 1;
        return $pay->success();
        //return $pay->success()->send();// laravel 框架中请直接 `return $pay->success()`

    }

    /**
     * 处理客户端拿到阿里的支付sign，丢回来的信息
     */
    public function callBackForClient()
    {
    }
}
