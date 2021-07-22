<?php

namespace App\Http\Controllers\Other;

use App\Entitys\Other\JdMaidOldOther;
use App\Entitys\Other\MtMaidOldOther;
use App\Entitys\Other\PddMaidOldOther;
use App\Entitys\Other\TaobaoMaidOldOther;
use App\Entitys\Other\ThreeEleMaidOld;
use App\Entitys\Other\UserThreeUpMaid;
use App\Entitys\OtherOut\AppUserInfoOut;
use App\Entitys\OtherOut\EleEnterOrderOut;
use App\Entitys\OtherOut\JdEnterOrdersOut;
use App\Entitys\OtherOut\MtEnterOrderOut;
use App\Entitys\OtherOut\PddEnterOrdersOut;
use App\Entitys\OtherOut\ShopOrdersOut;
use App\Entitys\OtherOut\TaobaoEnterOrderOut;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use ETaobao\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MiNiTeamDataController extends Controller
{
    /*
     * 得到爆款商城管理费预估收入
     */
    public function hotShopEstimatedIncome(Request $request, UserThreeUpMaid $userThreeUpMaid)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',         #必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];

            /***********************************/
            //开始处理逻辑问题
            //得到当日时间
            $today = date('Y-m-d 00:00:00', time());
            //得到今月时间
            $month = date('Y-m-01 00:00:00', time());

            $num_today_data = (string)$userThreeUpMaid->getEstimatedMoneyByTime($app_id, $today);#当日预估收入
            $num_month_data = (string)$userThreeUpMaid->getEstimatedMoneyByTime($app_id, $month);#当月预估收入
            $num_all_data = (string)$userThreeUpMaid->getEstimatedMoneyByTime($app_id);          #累计预估收入

            $data = [
                'num_today_data' => round($num_today_data, 2),
                'num_month_data' => round($num_month_data, 2),
                'num_all_data' => round($num_all_data, 2),
            ];

            return $this->getResponse($data);//正常返回数据
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
     * 得到淘宝团队订单数据
     */
    public function getTbTeamOrdersData(Request $request, TaobaoEnterOrderOut $taobaoEnterOrderOut, TaobaoMaidOldOther $maidOldOther, AppUserInfoOut $appUserInfoOut)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = 0;

            if ($arrRequest['status'] == 0) {
                $status = 0;
            }

            if ($arrRequest['status'] == 1) {
                $status = 1;
            }

            if ($arrRequest['status'] == 2) {
                $status = 2;
            }

            $orders = $maidOldOther->getTaobaoMaidAll($arrRequest['app_id'], $status);

            $orders = $orders->toArray();
            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $taobaoEnterOrderOut->getOneOrders($order['trade_id']);
                $app_user = $appUserInfoOut->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                if (empty($enter_order)) {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = '商品名字同步中...';
                    $arr_orders[$k]['money'] = '同步中..';
                    $arr_orders[$k]['img'] = '';
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                } else {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = $enter_order->item_title;
                    $config = config('taobao.config_taobao_one');
                    $app = Factory::Tbk($config);
                    $param = [
                        'num_iids' => $enter_order->num_iid,
                    ];
                    $res = $app->item->getInfo($param);
                    if (empty($res->results->n_tbk_item[0]->pict_url)) {
                        $img = '';
                    } else {
                        $img = $res->results->n_tbk_item[0]->pict_url;
                    }
                    $arr_orders[$k]['img'] = $img;
                    $arr_orders[$k]['money'] = round($enter_order->alipay_total_price, 2);
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                }
            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到京东团队订单数据
     */
    public function getJdTeamOrdersData(Request $request, JdMaidOldOther $jdMaidOldOther, JdEnterOrdersOut $jdEnterOrdersOut, AppUserInfoOut $appUserInfoOut)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required', //0,1,2
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = 0;

            if ($arrRequest['status'] == 0) {
                $status = 0;
            }

            if ($arrRequest['status'] == 1) {
                $status = 1;
            }

            if ($arrRequest['status'] == 2) {
                $status = 2;
            }

            $orders = $jdMaidOldOther->getTaobaoMaidAll($arrRequest['app_id'], $status);

            $orders = $orders->toArray();
            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $jdEnterOrdersOut->getJdOne($order['trade_id'], $order['sku_id']);
                $app_user = $appUserInfoOut->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                //理论上不可能出现enter_order不存在的情况
                $arr_orders[$k]['jd_id'] = $enter_order['orderId'];
                $arr_orders[$k]['good_id'] = $enter_order['skuId'];
                $arr_orders[$k]['title'] = $enter_order['skuName'];
                $arr_orders[$k]['money'] = (string)round($enter_order['estimateCosPrice'], 2);
                $time = substr($enter_order['orderTime'], 0, 10);
                $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);
                $arr_orders[$k]['status'] = $arrRequest['status'];
                $arr_orders[$k]['from_id'] = $order['father_id'];
                $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                $arr_orders[$k]['tkmoney_general'] = (string)$order['maid_money'];
                $arr_orders[$k]['tkmoney_vip'] = (string)$order['maid_money'];
            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到拼多多团队订单数据
     */
    public function getPddTeamOrdersData(Request $request, PddMaidOldOther $maidOldOther, PddEnterOrdersOut $pddEnterOrdersOut, AppUserInfoOut $appUserInfoOut)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = 0;

            if ($arrRequest['status'] == 0) {
                $status = 0;
            }

            if ($arrRequest['status'] == 1) {
                $status = 1;
            }

            if ($arrRequest['status'] == 2) {
                $status = 2;
            }
            $orders = $maidOldOther->getPddMaidAll($arrRequest['app_id'], $status);

            $orders = $orders->toArray();
            $arr_orders = [];

            foreach ($orders['data'] as $k => $order) {
                $enter_order = $pddEnterOrdersOut->getPddOne($order['trade_id'], $order['father_id']);
                $app_user = $appUserInfoOut->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                $arr_orders[$k]['pdd_id'] = $enter_order['order_sn'];
                $arr_orders[$k]['status'] = $arrRequest['status'];
                $arr_orders[$k]['status_show'] = $enter_order['order_status_desc'];
                $arr_orders[$k]['title'] = $enter_order['goods_name'];
                $arr_orders[$k]['money'] = round($enter_order['order_amount'] / 100, 2);
                $time = substr($enter_order['order_create_time'], 0, 10);
                $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);
                $arr_orders[$k]['from_id'] = $order['father_id'];
                $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到饿了么团队订单数据
     */
    public function getEleTeamOrdersData(Request $request, EleEnterOrderOut $eleEnterOrderOut, ThreeEleMaidOld $threeEleMaidOld, AppUserInfoOut $appUserInfoOut)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = 0;

            if ($arrRequest['status'] == 0) {
                $status = 0;
            }

            if ($arrRequest['status'] == 1) {
                $status = 1;
            }

            if ($arrRequest['status'] == 2) {
                $status = 2;
            }

            $orders = $threeEleMaidOld->getEleMaidAll($arrRequest['app_id'], $status);
            $orders = $orders->toArray();

            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $eleEnterOrderOut->getOneOrders($order['trade_id']);
                $app_user = $appUserInfoOut->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                if (empty($enter_order)) {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = '商品名字同步中...';
                    $arr_orders[$k]['money'] = '同步中..';
                    $arr_orders[$k]['img'] = '';
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                } else {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = $enter_order->item_title;
                    $config = config('taobao.config_taobao_one');
                    $app = Factory::Tbk($config);
                    $param = [
                        'num_iids' => $enter_order->num_iid,
                    ];
                    $res = $app->item->getInfo($param);
                    if (empty($res->results->n_tbk_item[0]->pict_url)) {
                        $img = '';
                    } else {
                        $img = $res->results->n_tbk_item[0]->pict_url;
                    }
                    $arr_orders[$k]['img'] = $img;
                    $arr_orders[$k]['money'] = (string)round($enter_order->alipay_total_price, 2);
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];

                }
            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到美团团队订单数据
     */
    public function getMtTeamOrdersData(Request $request, MtEnterOrderOut $mtEnterOrderOut, MtMaidOldOther $mtMaidOldOther, AppUserInfoOut $appUserInfoOut)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $status = 0;

            if ($arrRequest['status'] == 0) {
                $status = 0;
            }

            if ($arrRequest['status'] == 1) {
                $status = 1;
            }

            if ($arrRequest['status'] == 2) {
                $status = 2;
            }

            $orders = $mtMaidOldOther->getMtTeamMaidAll($arrRequest['app_id'], $status);
            $orders = $orders->toArray();

            $arr_orders = [];
            foreach ($orders['data'] as $k => $order) {
                $enter_order = $mtEnterOrderOut->getOneOrders($order['trade_id']);
                $app_user = $appUserInfoOut->getUserById($order['father_id']);

                //10进制id转换33进制
                $order['father_id'] = CommonFunction::userAppIdCompatibility($order['father_id']);

                if (empty($enter_order)) {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = '商品名字同步中...';
                    $arr_orders[$k]['money'] = '同步中..';
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                } else {
                    $arr_orders[$k]['taobao_id'] = $order['trade_id'];
                    $arr_orders[$k]['from_id'] = $order['father_id'];
                    $arr_orders[$k]['from_name'] = empty($app_user->real_name) ? '未填写真实姓名' : $app_user->real_name;
                    $arr_orders[$k]['from_is_push'] = @$app_user->parent_id <> $arrRequest['app_id'] ? 0 : 1;
                    $arr_orders[$k]['status'] = $arrRequest['status'];
                    $arr_orders[$k]['title'] = $enter_order->shop_name;
                    $arr_orders[$k]['money'] = (string)round($enter_order->actual_item_amount, 2);
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                    $arr_orders[$k]['tkmoney_general'] = $order['maid_money'];
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                }
            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到全部类型订单简要数据
     */
    public function getAllTeamOrdersData(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'type' => 'required', #2.淘宝 3.京东 4.拼多多 6.饿了么 5.美团 1.爆款
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $taobaoEnterOrderOut = new TaobaoEnterOrderOut();
            $maidOldOther = new TaobaoMaidOldOther();
            $jdMaidOldOther = new JdMaidOldOther();
            $jdEnterOrdersOut = new JdEnterOrdersOut();
            $pddMaidOldOther = new PddMaidOldOther();
            $pddEnterOrdersOut = new PddEnterOrdersOut();
            $eleEnterOrderOut = new EleEnterOrderOut();
            $threeEleMaidOld = new ThreeEleMaidOld();
            $mtEnterOrderOut = new MtEnterOrderOut();
            $mtMaidOldOther = new MtMaidOldOther();
            $userThreeUpMaid = new UserThreeUpMaid();
            $shopOrdersOut = new ShopOrdersOut();


            if ($arrRequest['type'] == 2) {
                $orders = $maidOldOther->withTrashed()->where(['app_id' => $arrRequest['app_id'], 'type' => 1])->orderByDesc('created_at')->paginate(20);
                $orders = $orders->toArray();
                $arr_orders = [];
                foreach ($orders['data'] as $k => $order) {
                    $enter_order = $taobaoEnterOrderOut->getOneOrders($order['trade_id']);
                    if (empty($enter_order)) {
                        $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                        $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                        $arr_orders[$k]['created_at'] = $order['created_at'];
                    } else {
                        $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                        $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                        $arr_orders[$k]['created_at'] = $order['created_at'];
                    }
                }
            } elseif ($arrRequest['type'] == 3) {
                $orders = $jdMaidOldOther->withTrashed()->where(['app_id' => $arrRequest['app_id'], 'type' => 1])->orderByDesc('created_at')->paginate(20);
                $orders = $orders->toArray();
                $arr_orders = [];
                foreach ($orders['data'] as $k => $order) {
                    $enter_order = $jdEnterOrdersOut->getJdOne($order['trade_id'], $order['sku_id']);
                    //理论上不可能出现enter_order不存在的情况
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                    $time = substr($enter_order['orderTime'], 0, 10);
                    $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);
                    $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                }
            } elseif ($arrRequest['type'] == 4) {
                $orders = $pddMaidOldOther->withTrashed()->where(['app_id' => $arrRequest['app_id'], 'type' => 1])->orderByDesc('created_at')->paginate(20);
                $orders = $orders->toArray();
                $arr_orders = [];
                foreach ($orders['data'] as $k => $order) {
                    $enter_order = $pddEnterOrdersOut->getPddOne($order['trade_id'], $order['father_id']);
                    $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                    $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                    $time = substr($enter_order['order_create_time'], 0, 10);
                    $arr_orders[$k]['created_at'] = date('Y-m-d H:i:s', $time);
                }
            } elseif ($arrRequest['type'] == 6) {
                $orders = $threeEleMaidOld->withTrashed()->where(['app_id' => $arrRequest['app_id'], 'type' => 1])->orderByDesc('created_at')->paginate(20);
                $orders = $orders->toArray();
                $arr_orders = [];
                foreach ($orders['data'] as $k => $order) {
                    $enter_order = $eleEnterOrderOut->getOneOrders($order['trade_id']);
                    if (empty($enter_order)) {
                        $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                        $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                        $arr_orders[$k]['created_at'] = $order['created_at'];
                    } else {
                        $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                        $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                        $arr_orders[$k]['created_at'] = $order['created_at'];
                    }
                }
            } elseif ($arrRequest['type'] == 5) {
                $orders = $mtMaidOldOther->withTrashed()->where(['app_id' => $arrRequest['app_id'], 'type' => 1])->orderByDesc('created_at')->paginate(20);
                $orders = $orders->toArray();
                $arr_orders = [];
                foreach ($orders['data'] as $k => $order) {
                    $enter_order = $mtEnterOrderOut->getOneOrders($order['trade_id']);
                    if (empty($enter_order)) {
                        $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                        $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                        $arr_orders[$k]['created_at'] = $order['created_at'];
                    } else {
                        $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                        $arr_orders[$k]['tkmoney_vip'] = $order['maid_money'];
                        $arr_orders[$k]['created_at'] = $order['created_at'];
                    }
                }
            } elseif ($arrRequest['type'] == 1) {
                $orders = $userThreeUpMaid->where(['app_id' => $arrRequest['app_id']])->orderByDesc('created_at')->paginate(20);
                $orders = $orders->toArray();
                $arr_orders = [];
                foreach ($orders['data'] as $k => $order) {
                    if (empty($order['order_id'])) {
                        continue;
                    }
                    $order_data = $shopOrdersOut->getByOrderId($order['order_id']);
                    if (!$order_data) {
                        continue;
                    }
                    $arr_orders[$k]['status'] = empty($order['deleted_at']) ? 1 : 0;
                    $arr_orders[$k]['money'] = $order['money'];
                    $arr_orders[$k]['created_at'] = $order['created_at'];
                }
            }

            return $this->getResponse(
                [
                    'data' => $arr_orders,
                    'lastPage' => $orders['last_page'],
                    'currentPage' => $orders['current_page'],
                ]
            );

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
