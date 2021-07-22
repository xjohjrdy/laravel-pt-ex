<?php

namespace App\Http\Controllers\Other;

use App\Entitys\App\ShopGoods;
use App\Entitys\Other\ManagerPretendMaid;
use App\Entitys\Other\ShopGoodsOut;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\AppUserInfoOut;
use App\Entitys\OtherOut\ShopOrdersOneOut;
use App\Entitys\OtherOut\ShopOrdersOut;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    //
    /**
     * 经理分佣
     */
    public function maid(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'order_id' => 'required',    //必须有数据
                'all_profit_value' => 'required',    //必须有数据
                'parent_id' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];
            $commission = $arrRequest['all_profit_value'];
            $ptPid = $arrRequest['parent_id'];


            $obj_shop_orsers = new ShopOrdersOut();
            $order = $obj_shop_orsers->where('order_id', $order_id)->first();
            if (empty($order)) {
                return $this->getInfoResponse('1441', '订单号找不到子订单！');
            }
            $obj_shop_orsers_one = new ShopOrdersOneOut();
            $order_one = $obj_shop_orsers_one->where('order_id', $order->id)->first();
            if (empty($order_one)) {
                return $this->getInfoResponse('1441', '订单号找不到子订单号！');
            }
            $obj_shop_goods = new ShopGoodsOut();
            $good_s = $obj_shop_goods->where('id', $order_one->good_id)->first();
            if (empty($good_s)) {
                return $this->getInfoResponse('1441', '订单号找不到商品！商品可能被删除');
            }
            $growth = $good_s->can_active; //成长值


//            if (Cache::has('general_shop_commission_' . $order_id)) {
//                return $this->getInfoResponse('1001', '该订单已被确认！');
//            }
//            Cache::put('general_shop_commission_' . $order_id, 1, 10);
            /***********************************/
            //开始处理逻辑问题
            $commission = $commission * 0.41;
            $signBool = false;
            $signOk = false;
            for ($i = 0; $i < 50; $i++) {
                if (empty($ptPid)) {
                    break; #无上级跳过
                }

                //得到上级用户app等级
                $obj_app_user_info_out = new AppUserInfoOut();
                $user_level = $obj_app_user_info_out->where('id', $ptPid)->value('level');
                $parentInfo['pt_id'] = $ptPid;
                $parentInfo['pt_pid'] = $obj_app_user_info_out->where('id', $ptPid)->value('parent_id');


                $ptPid = $parentInfo['pt_pid'];
                if ($growth > 0) {
                    //拨出佣金的3.73%给本人往上推的第1~2个经理（每个人3.73%）；
                    $commission_percent = 0.0373;
                } else {
                    //拨出佣金的1.5%给本人往上推的第1~2个经理（每个人1.5%）；
                    $commission_percent = 0.015;
                }
                if ($i > 0) {

                    if ($user_level != 4) {
                        continue;
                    }

                    if ($signBool) {
                        $signOk = true;
                    } else {
                        $signBool = true;
                    }


                } else {
                    //直属处理
                    if ($user_level != 4) {
                        continue;
                    }

                    if ($signBool) {
                        $signOk = true;
                    } else {
                        $signBool = true;
                    }

                }

                $obj_three_up_maid = new ManagerPretendMaid();
                if ($obj_three_up_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                    Log::warning('真实重复分佣情况！', [$parentInfo['pt_id'] => $order_id]);
                    continue;
                }

                $commission_result = $commission * $commission_percent;

                $commission_result = round($commission_result, 2);

                if ($commission_result > 0) {
                    //添加分佣记录
                    $obj_three_up_maid->addMaidLog([
                        'app_id' => $parentInfo['pt_id'],
                        'order_id' => $order_id,
                        'father_id' => $order->app_id,
                        'type' => 1,
                        'money' => $commission_result,
                        'status' => 0,
                    ]);
                }

                /**
                 * 暂时不给用户加管理费以及管理费变化记录
                 * 这段代码暂时注释
                 */
//                //给用户加可提余额
//                $obj_three_user = new ThreeUser();
//                $obj_three_user->where('app_id', $parentInfo['pt_id'])->update(['money' => DB::raw("money + " . $commission_result)]);
//
//                //记录可提余额变化记录值与变化说明
//                $obj_three_change_user_log = new ThreeChangeUserLog();
//                $later_money = $perentAcount + $commission_result;
//                $obj_three_change_user_log->addLog($parentInfo['pt_id'], $perentAcount, $commission_result, $later_money, 0, 'SPT');

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
}
