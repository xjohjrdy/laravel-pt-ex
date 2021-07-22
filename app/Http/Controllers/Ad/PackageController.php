<?php

namespace App\Http\Controllers\Ad;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAccount;
use App\Entitys\App\AdNumberSale;
use App\Entitys\App\ArticleOrders;
use App\Exceptions\ApiException;
use App\Services\Advertising\AdPackage;
use App\Services\Recharge\PurchaseUserGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yansongda\Pay\Pay;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    /**
     * 获取广告包套餐列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getList(Request $request)
    {
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }//仅用于测试兼容旧版-----------------线上可删除

            $model = new AdNumberSale();
            return $this->getResponse($model->getPackages());
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /*
     * 购买广告包
     */
    public function buyAdvertisingPackage(Request $request, UserAccount $userAccount, ArticleOrders $articleOrders, PurchaseUserGroup $purchaseUserGroup)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
                'type' => Rule::in([1, 2]),
                'package_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type'];

            $adNumberModel = new AdNumberSale();
            $package = $adNumberModel->getPackageById($arrRequest['package_id']);
            if(empty($package)){
                throw new ApiException('无效的广告包套餐id', 5002);
            }
            $buy_number = $package['much']; // 购买套餐的广告包数量
            $pay_price = $package['use_money']; // 套餐的金额
            /***********************************/
            if (Cache::has('recharge_member_' . $app_id)) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试！...');
            }
            Cache::put('recharge_member_' . $app_id, 1, 0.5);
            if ($type == 1) {
                $orderid_tag = date('YmdHis') . $purchaseUserGroup->random(18);
//                $obj_data = $articleOrders->createOrder($app_id, $type, $orderid_tag);
                $obj_data = $articleOrders->create([
                    'order_id' => $orderid_tag,
                    'app_id' => $app_id,
                    'pay_type' => $type,
                    'pay_status' => 0,
                    'pay_price' => $pay_price,
                    'number' => $buy_number
                ]);

                if (empty($obj_data)) {
                    throw new ApiException('网络异常，不存在的订单!', 5002);
                }
                $order = [
                    'out_trade_no' => $obj_data->order_id,          #当前交易订单号
                    'total_fee' => ($obj_data->pay_price * 100),    #微信单位为分
                    'body' => '商城购物 - ' . $obj_data->pay_price . '元',
                ];
                $we_config = array_replace(
                    config('unified_pay.we_config'),
                    [
                        'notify_url' => 'http://api.36qq.com/api/buy_advertising_package_XxX_we_notify_2'
                    ]);

                $we_secret = Pay::wechat($we_config)->app($order);
                return $this->getResponse($we_secret->getContent());
            } elseif ($type == 2) {
                $adPackageService = new AdPackage();
                $orderid_tag = date('YmdHis') . $purchaseUserGroup->random(18);
//                $obj_data = $articleOrders->createOrder($app_id, $type, $orderid_tag);
                $obj_data = $articleOrders->create([
                    'order_id' => $orderid_tag,
                    'app_id' => $app_id,
                    'pay_type' => $type,
                    'pay_status' => 0,
                    'pay_price' => $pay_price,
                    'number' => $buy_number
                ]);
                $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);
                if ($user_account->extcredits4 < $obj_data->pay_price * 10) {
                    return $this->getInfoResponse('3001', '我的币余额不足！');
                }

//                $params = [
//                    'pay_status' => 1,
//                ];
//                $articleOrders->upOrder($orderid_tag, $params);
//                $adPackageService->takePtb($app_id, $obj_data->pay_price * 10);
//                $adPackageService->handleArticle($app_id, $buy_number);
                $adPackageService->updateOrderPayStatusByPtb($orderid_tag, $app_id, $buy_number, $obj_data->pay_price * 10);
            }
            return $this->getResponse("开通成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('购买异常：' . $e->getLine(), '5003');
        }
    }

    /*
     * 购买广告包微信回调
     */
    public function weNotify()
    {
        $adPackageService = new AdPackage();
        $this->weLog('---------------start----------');
        $we_config = array_replace(
            config('unified_pay.we_config'),
            [
                'notify_url' => 'http://api.36qq.com/api/buy_advertising_package_XxX_we_notify_2'
            ]);
        $pay = Pay::wechat($we_config);
        try {
            $obj_data = $pay->verify();
            if ($obj_data->return_code != "SUCCESS") {
                $this->weLog('错误信息：' . $obj_data->return_msg);
                $this->weLog('---------------end----------');
                return 'error';
            }

            $order_id = $obj_data->out_trade_no;
            $actual = $obj_data->total_fee;

            $this->weLog('开始查询订单：' . $order_id);
            $md_article_order = new ArticleOrders();
            $order_info = $md_article_order->getUnpaidByOrderId($order_id);
            if (empty($order_info)) {
                $this->weLog('不存或已处理该订单：' . $order_id);
                $this->weLog('---------------end_error----------');
                return 'error';
            }

            $this->weLog('用户支付金额：' . ($actual / 100));
            if ($order_info->pay_price != ($actual / 100)) {
                $this->weLog('该用户实际支付金额有误：实付' . ($actual / 100) . '元');
                $this->weLog('---------------end_error----------');
                return 'error';
            }
            $this->weLog('开始判断订单类型');

            switch ($order_info->pay_type) {
                case 1:
                    $this->weLog('开始更新订单状态');
//                    $params = [
//                        'pay_status' => 1,
//                    ];
//                    $md_article_order->upOrder($order_id, $params);
//                    $adPackageService->handleArticle($order_info->app_id, $order_info->number);
                    $adPackageService->updateOrderPayStatusByWechat($order_id, $order_info->app_id, $order_info->number);
                    $this->weLog('更新完成');
                    break;

                default:
                    $this->weLog('订单类型异常：' . $order_info->pay_type);
                    $this->weLog('---------------end_error----------');
                    return 'error';
            }

            $this->weLog('---------------end----------');

        } catch (\Throwable $e) {
            $this->weLog('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage());
            $this->weLog('---------------end_error----------');
            return 'error';
        }

        return $pay->success();

    }

    /*
     * 记录日志
     */
    private function weLog($msg)
    {
        Storage::disk('local')->append('callback_document/buy_advertising_package_notify_2.txt', var_export($msg, true));
    }

}
