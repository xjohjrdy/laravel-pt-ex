<?php

namespace App\Http\Controllers\Other;

use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeCircleMaid;
use App\Entitys\Other\ThreeUser;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\AppUserInfoOut;
use App\Entitys\OtherOut\CircleOrderOut;
use App\Entitys\OtherOut\CircleRingOut;
use App\Entitys\OtherOut\CircleUserAddOut;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;
use App\Services\WuHang\Maid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CircleCommissionController extends Controller
{
    /*
     * 加入圈子分
     */
    public function getInfoCircleCommission(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'order_id' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];

            /***********************************/
            //开始处理逻辑问题

            $obj_circle_user_add_out = new CircleUserAddOut();
            //得到订单信息
            $order_info = $obj_circle_user_add_out->getOrder($order_id);
            $app_id = $order_info->app_id;
            $money = $order_info->money;
            $circle_id = $order_info->circle_id;

            //得到圈子信息
            $obj_circle = new CircleRingOut();
            $obj_circle_info = $obj_circle->where('id', $circle_id)->first();

            //得到用户上级信息
            $obj_ad_user = new AdUserInfoOut();
            $obj_app_user = new AppUserInfoOut();
            $obj_ad_info = $obj_ad_user->where('pt_id', $app_id)->first();
            $obj_app_info = $obj_app_user->where('id', $app_id)->first();
            $ptPid = $obj_ad_info->pt_pid;
            $signBool = false;
            $signOk = false;

            //多级分
            for ($i = 0; $i < 50; $i++) {
                if (empty($ptPid)) {
                    break;
                }

                //得到上级信息
                $parentInfo = $this->getParentInfo($ptPid);
                if (empty($parentInfo)) {
                    break;
                }
                $ptPid = $parentInfo['pt_pid'];

                if ($i == 0) {
//                    $commission = $money * 10 * 0.1;
                    if ($parentInfo['groupid'] == 24) {
                        $signBool = true;
                    }
                    continue;//跳过直属
                } else {
                    $commission = $money * 10 * 0.05;
                    if ($signBool) {
                        $commission *= 0.2;
                        $signOk = true;
                    }
                    if ($parentInfo['groupid'] != 24) {
                        continue;
                    }
                    $signBool = true;
                }

                //检测是否有分佣过
                $obj_maid = new ThreeCircleMaid();
                if ($obj_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id, 'type' => 2])->exists()) {
                    Storage::disk('local')->append('callback_document/circle_again_log.txt', var_export(var_export($parentInfo['pt_id'] . '--' . $order_id, true), true));
                    continue;
                }

                //添加分佣记录
                if ($commission <> 0 || $money <> 0) {
                    $obj_maid->create([
                        'app_id' => $parentInfo['pt_id'],
                        'from_user_name' => $obj_app_info->real_name,
                        'from_user_phone' => $obj_app_info->phone,
                        'from_user_img' => $obj_app_info->avatar,
                        'from_circle_name' => $obj_circle_info->ico_title,
                        'from_circle_img' => $obj_circle_info->ico_img,
                        'order_id' => $order_id,
                        'order_money' => $money * 10,
                        'money' => $commission,
                        'type' => 2,
                    ]);
                }

                if ($commission > 0) {

                    //根据id 获取当前的可提余额
                    $perentAcount = $this->getParentCarryMoney($parentInfo['pt_id']);

                    $commission = $commission / 10;
                    //给用户添加分佣的钱
                    $taobao_user = new ThreeUser();
                    $obj_taobao_user = $taobao_user->where('app_id', $parentInfo['pt_id'])->first();

                    if (empty($obj_taobao_user)) {
                        $taobao_user->create([
                            'app_id' => $parentInfo['pt_id'],
                            'money' => $commission,
                        ]);
                    } else {
                        $obj_taobao_user->money = $obj_taobao_user->money + $commission;
                        $obj_taobao_user->save();
                    }

                    //记录可提余额变化记录值与变化说明
                    $obj_three_change_user_log = new ThreeChangeUserLog();
                    $later_money = $perentAcount + $commission;
                    $obj_three_change_user_log->addLog($parentInfo['pt_id'], $perentAcount, $commission, $later_money, 0, 'CAF');
                }
                if ($signOk) {
                    break;
                }
            }
            return $this->getResponse('请求成功！');
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
     * 团队会员购买圈子多级分
     */
    public function buyCircleCommission(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'order_id' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];

            /***********************************/
            //开始处理逻辑问题
            //得到订单信息
            $order_info = $this->getInfoByOrderId($order_id);
            $app_id = $order_info->app_id;
            $money = $order_info->money;
            $circle_id = $order_info->circle_id;

            //得到圈子信息
            $obj_circle = new CircleRingOut();
            $obj_circle_info = $obj_circle->where('id', $circle_id)->first();

            //得到用户信息
            $obj_ad_user = new AdUserInfoOut();
            $obj_app_user = new AppUserInfoOut();
            $obj_ad_info = $obj_ad_user->where('pt_id', $app_id)->first();
            $obj_app_info = $obj_app_user->where('id', $app_id)->first();
            $ptPid = $obj_ad_info->pt_pid;

            $wuhang = new Maid();
            $wuhang->circleMaid($order_id, $ptPid, $app_id);


            $signBool = false;
            $signOk = false;
            for ($i = 0; $i < 50; $i++) {
                if (empty($ptPid)) {
                    break;
                }
                $parentInfo = $this->getParentInfo($ptPid);
                if (empty($parentInfo)) {
                    break;
                }
                $ptPid = $parentInfo['pt_pid'];
                if ($i == 0) {
                    if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                        continue;
                    }
                    if ($parentInfo['groupid'] == 24) {
                        $signBool = true;
                    }
                    continue;
                } else {
                    $commission = $money * 10 * 0.1;
                    if ($signBool) {
                        $commission *= 0.1;
                        $signOk = true;
                    }

                    if ($parentInfo['groupid'] != 24) {
                        continue;
                    }
                    $signBool = true;
                }

                //检测是否分过佣
                $obj_maid = new ThreeCircleMaid();
                if ($obj_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                    Storage::disk('local')->append('callback_document/circle_again_log.txt', var_export($parentInfo['pt_id'] . '--' . $order_id, true));
                    continue;
                }

                //添加分佣记录
                $obj_maid->create([
                    'app_id' => $parentInfo['pt_id'],
                    'from_user_name' => $obj_app_info->real_name,
                    'from_user_phone' => $obj_app_info->phone,
                    'from_user_img' => $obj_app_info->avatar,
                    'from_circle_name' => $obj_circle_info->ico_title,
                    'from_circle_img' => $obj_circle_info->ico_img,
                    'order_id' => $order_id,
                    'order_money' => $money * 10,
                    'money' => $commission,
                    'type' => 4,
                ]);

                //根据id 获取当前的可提余额
                $perentAcount = $this->getParentCarryMoney($parentInfo['pt_id']);

                $commission = $commission / 10;
                //给用户添加分佣的钱
                $taobao_user = new ThreeUser();
                $obj_taobao_user = $taobao_user->where('app_id', $parentInfo['pt_id'])->first();

                if (empty($obj_taobao_user)) {
                    $taobao_user->create([
                        'app_id' => $parentInfo['pt_id'],
                        'money' => $commission,
                    ]);
                } else {
                    $obj_taobao_user->money = $obj_taobao_user->money + $commission;
                    $obj_taobao_user->save();
                }

                //记录可提余额变化记录值与变化说明
                $obj_three_change_user_log = new ThreeChangeUserLog();
                $later_money = $perentAcount + $commission;
                $obj_three_change_user_log->addLog($parentInfo['pt_id'], $perentAcount, $commission, $later_money, 0, 'CFP');

                if ($signOk) {
                    break;
                }
            }
            return $this->getResponse('请求成功！');
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
     * 团队会员竞价圈子多级分
     */
    public function biddingCircleCommission(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'order_id' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];

            /***********************************/
            //开始处理逻辑问题
            //得到订单信息
            $order_info = $this->getInfoByOrderId($order_id);
            $app_id = $order_info->app_id;
            $money = round($order_info->money / 6, 2);
            $circle_id = $order_info->circle_id;

            //得到圈子信息
            $obj_circle = new CircleRingOut();
            $obj_circle_info = $obj_circle->where('id', $circle_id)->first();

            //得到用户信息
            $obj_ad_user = new AdUserInfoOut();
            $obj_app_user = new AppUserInfoOut();
            $obj_ad_info = $obj_ad_user->where('pt_id', $app_id)->first();
            $obj_app_info = $obj_app_user->where('id', $app_id)->first();
            $ptPid = $obj_ad_info->pt_pid;

            $signBool = false;
            $signOk = false;
            for ($i = 0; $i < 50; $i++) {
                if (empty($ptPid)) {
                    break;
                }
                $parentInfo = $this->getParentInfo($ptPid);
                if (empty($parentInfo)) {
                    break;
                }
                $ptPid = $parentInfo['pt_pid'];
                if ($i == 0) {
                    if ($parentInfo['groupid'] != 23 && $parentInfo['groupid'] != 24) {
                        continue;
                    }
                    if ($parentInfo['groupid'] == 24) {
                        $signBool = true;
                    }
                    continue;
                } else {
                    $commission = $money * 10 * 0.1;
                    if ($signBool) {
                        $commission *= 0.1;
                        $signOk = true;
                    }

                    if ($parentInfo['groupid'] != 24) {
                        continue;
                    }
                    $signBool = true;
                }

                //检测是否分过佣
                $obj_maid = new ThreeCircleMaid();
                if ($obj_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id, 'type' => 5])->exists()) {
                    Storage::disk('local')->append('callback_document/circle_again_log.txt', var_export($parentInfo['pt_id'] . '--' . $order_id, true));
                    continue;
                }

                //记录分佣日志
                $obj_maid->create([
                    'app_id' => $parentInfo['pt_id'],
                    'from_user_name' => $obj_app_info->real_name,
                    'from_user_phone' => $obj_app_info->phone,
                    'from_user_img' => $obj_app_info->avatar,
                    'from_circle_name' => $obj_circle_info->ico_title,
                    'from_circle_img' => $obj_circle_info->ico_img,
                    'order_id' => $order_id,
                    'order_money' => $money * 10,
                    'money' => $commission,
                    'type' => 5,
                ]);

                //根据id 获取当前的可提余额
                $perentAcount = $this->getParentCarryMoney($parentInfo['pt_id']);

                $commission = $commission / 10;
                //给用户添加分佣的钱
                $taobao_user = new ThreeUser();
                $obj_taobao_user = $taobao_user->where('app_id', $parentInfo['pt_id'])->first();

                if (empty($obj_taobao_user)) {
                    $taobao_user->create([
                        'app_id' => $parentInfo['pt_id'],
                        'money' => $commission,
                    ]);
                } else {
                    $obj_taobao_user->money = $obj_taobao_user->money + $commission;
                    $obj_taobao_user->save();
                }

                //记录可提余额变化记录值与变化说明
                $obj_three_change_user_log = new ThreeChangeUserLog();
                $later_money = $perentAcount + $commission;
                $obj_three_change_user_log->addLog($parentInfo['pt_id'], $perentAcount, $commission, $later_money, 0, 'CBP');

                if ($signOk) {
                    break;
                }
            }
            return $this->getResponse('请求成功！');
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
     * 得到用户信息
     */
    public function getParentInfo($ptPid)
    {
        $obj_ad_user = new AdUserInfoOut();
        $parentInfo = $obj_ad_user->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if (empty($parentInfo)) {
            return false;
        }
        return $parentInfo->toArray();
    }

    /*
     * 通过order_id 拿到该笔订单的信息
     */
    public function getInfoByOrderId($order_id)
    {
        $obj_order = new CircleOrderOut();
        $obj_info = $obj_order->where('order_id', $order_id)->first();
        return $obj_info;
    }

    /*
     * 根据app_id 取该用户可提余额
     */
    public function getParentCarryMoney($ptPid)
    {
        $obj_three_user = new ThreeUser();
        $account = $obj_three_user->where(['app_id' => $ptPid])->first();
        if (!$account) {
            $obj_three_user->create([
                'app_id' => $ptPid,
                'money' => 0,
            ]);
            $account = $obj_three_user->where(['app_id' => $ptPid])->first();
        }
        return $account->money;
    }
}
