<?php

namespace App\Http\Controllers\UpgradeVip;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\GrowthUserValue;
use App\Exceptions\ApiException;
use App\Services\UpgradeVip\ChangeVipService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ChangeVipController extends Controller
{
    //通过活跃值升级真实VIP
    public function activeVip(Request $request, ChangeVipService $changeVipService)
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

            $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

            $groupid = $ad_user_info->groupid;

            if ($groupid > 10) {
                return $this->getInfoResponse('1001', '用户级别错误,升级异常！code:' . $groupid);
            }

            $user_info = AppUserInfo::find($app_id);

//            halt($user_info->active_value); //用户当前活跃度 -- 改成用户上月活跃度 history_active_value

            if ($user_info->history_active_value < 97.5) {
                return $this->getInfoResponse('1002', '活跃值未达标，不满足升级条件！');
            }

            //活跃值达标，开始生成订单 pre_aljbgp_order
            $changeVipService->installOrder($app_id, 2);
            $changeVipService->upgradeGroup($app_id);

            //记录lc_growth_user记录

            $changeVipService->installGrowthOrder($app_id, 2); //2：活跃值
            $changeVipService->updateGrowthUser($app_id);

            return $this->getResponse('升级超级用户成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 通过成长值升级超级用户
     */
    public function growthVip(Request $request, ChangeVipService $changeVipService)
    {
        return $this->getInfoResponse('1002', '不支持该方式升级！');
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

            $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

            $groupid = $ad_user_info->groupid;

            if ($groupid > 10) {
                return $this->getInfoResponse('1001', '用户级别错误,升级异常！code:' . $groupid);
            }

            $growth = GrowthUserValue::where('app_id', $app_id)->value('growth');

            if ($growth < 100) {
                return $this->getInfoResponse('1002', '成长值未达标，不满足升级条件！');
            }

            //成长值达标，开始生成订单 pre_aljbgp_order
            $changeVipService->installOrder($app_id, 2, '通过成长值达标100直接升级超级用户');
            $changeVipService->upgradeGroup($app_id);

            //记录lc_growth_user记录

            $changeVipService->installGrowthOrder($app_id, 1); //1:成长值
            $changeVipService->updateGrowthUser($app_id);

            return $this->getResponse('升级超级用户成功！');

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    //TODO 通过报销升级准VIP
    public function shoppingVip(Request $request)
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


        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


}
