<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\UserHigh;
use App\Entitys\App\UserOrderTao;
use App\Entitys\App\WechatInfo;
use App\Entitys\Xin\ApplyUpgrade;
use App\Exceptions\ApiException;
use App\Services\Commands\CountEverydayService;
use App\Services\Common\Time;
use App\Services\Xin\GroupManageServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GroupManageController extends Controller
{
    /*
     * 获取团队模块用户个人信息数据
     */
    public function getUserInfo(Request $request, AppUserInfo $appUserInfo, UserOrderTao $userOrderTao, ApplyUpgrade $applyUpgrade, UserHigh $userHigh)
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
            //做缓存数据
            if (Cache::has('getUserInfo:' . $app_id)) {
                $data = Cache::get('getUserInfo:' . $app_id);
                return $this->getResponse($data);
            }

            /***********************************/
            $obj_user_info = $appUserInfo->find($app_id);
            $arr_level = config('level_config');
            $data = [
                'user_name' => $obj_user_info->user_name,
                'level' => $arr_level[$obj_user_info->level],
                'level_modify_time' => date('Y-m-d', $obj_user_info->level_modify_time),
                'active_value' => $obj_user_info->active_value,
            ];
            if ($data['level'] == '转正') {
                $obj_user_high = $userHigh->getUserHighInfo($app_id);
                if ($obj_user_high && $obj_user_high->number >= 1) {
                    $data['level'] = '旧优质转正';
                } elseif ($obj_user_info->active_value >= 97.5) {
                    $data['level'] = '旧优质转正';
                }
            }
            $data['children_total'] = $appUserInfo->getChildrenCount($app_id);
            $data['three_floor_children_total'] = $appUserInfo->getNewThreeFloorChildrenCount($app_id);
            $data['reviewed_order_total'] = $userOrderTao->getTeamCurrentMonthPassedOrder($app_id);
            $data['team_next_month_cash_amount'] = $userOrderTao->teamNextMonthCash($app_id);
            $data['history_active_value'] = $obj_user_info->history_active_value;
            $data['active_value'] = $obj_user_info->active_value;
            $obj_user = new AppUserInfo();
            $int_user_level = $obj_user->where('id', $app_id)->value('level');
            if ($int_user_level == 1) {
                $obj_order = new CountEverydayService();
                if ($obj_order->isValid($app_id)) {
                    $data['history_active_value'] = '0.00';
                    $data['active_value'] = '0.00';
                }
            }
            $last_month_order_amount = $userOrderTao->teamLastMonthOrderAmount($app_id);
            $data['team_cash_amount'] = $obj_user_info->order_amount + $last_month_order_amount;
            $data['team_cash_amount'] = number_format($data['team_cash_amount'], 2);
            $data['apply_upgrade'] = json_encode($applyUpgrade->getUserApplyUpgradeInfo($app_id));
            $data['has_apply_upgrade'] = empty($data['apply_upgrade']) ? 0 : 1;

            $data['level_int'] = $int_user_level;
            if ($int_user_level == 2) {
                $applyUpgrade = new ApplyUpgrade();
                $data['text'] = $applyUpgrade->where('user_id', $app_id)->orderByDesc('id')->value('reason');
                preg_match('/put\d+/', $data['text'], $wechat);
                $data['wechat'] = empty($wechat[0]) ? '无' : $wechat[0];
                $data['text'] =  preg_replace('/put\d+/', '', $data['text']);
            }

            Cache::put('getUserInfo:' . $app_id, $data, 1);//缓存一分钟
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 检查是否满足申请级别条件
     */
    public function checkApplyUpgrade(Request $request, AppUserInfo $appUserInfo, ApplyUpgrade $applyUpgrade, WechatInfo $wechatInfo)
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
            $max_level = (int)config('level_config.max');
            $int_level_by_id = $appUserInfo->getFieldsData($app_id, 'level');
            if (!($int_level_by_id < $max_level)) {
                return $this->getInfoResponse('1001', '已到达当前系统最高等级！');
            }
            $check_has_apply_upgrade = $applyUpgrade->checkHasApplyUpgrade($app_id);
            if ($check_has_apply_upgrade) {
                return $this->getInfoResponse('1002', '当前有升级请求正在等待处理,请勿重复提交！');
            }
            $no_conform_reason = "不具备申请升级的条件！";
            if ($appUserInfo->getChildrenCount($app_id) < 10) {
                $res = 0;
                $no_conform_reason = "直属用户不足10,不具备申请升级的条件!";
            }
            $int_level_by_id = $appUserInfo->find($app_id)->level;


            $goal_level = $int_level_by_id + 1;
            if ($int_level_by_id == 1) {
                $arr_directly_id = $appUserInfo->getChildrenId($app_id)->toArray();
                $login_wechat_num = $wechatInfo->whereIn('app_id', $arr_directly_id)->count();
                if ($login_wechat_num < 10) {
                    return $this->getInfoResponse('1006', "直推用户微信登陆少于10，不具备升级条件");
                }
                $num_active_value = $appUserInfo->find($app_id)->active_value;
                $num_history_active_value = $appUserInfo->find($app_id)->history_active_value;

                $obj_timestamp = new Time();
                $current_month = $obj_timestamp->getCurrentMonthTimestamp();
                $int_user_num = $appUserInfo->where(['parent_id' => $app_id, 'status' => 1])->where('create_time', '<', $current_month)->count('id');

                $sum = $num_active_value + $num_history_active_value + $int_user_num;
                $group_manage_services = new GroupManageServices();
                $calculate_active_value = $group_manage_services->calculateActiveValue($app_id);
                if ($calculate_active_value < 20 && $sum < 20) {
                    return $this->getInfoResponse('1005', "活跃值少于20，不具备升级条件");
                }

                $res = $login_wechat_num >= 10 && ($calculate_active_value >= 20 || $sum >= 20) ? $goal_level : 0;

            }
            if ($int_level_by_id == 2) {
                $str_reason = $applyUpgrade->where(['user_id' => $app_id])->orderByDesc('id')->value('reason');
                if (empty($str_reason)) {
                    return $this->getInfoResponse('1003', "恭喜您已通过实习！请加转正客服微信put186，邀请客服进群，耐心等待审核！");
                }
                return $this->getInfoResponse('1003', $str_reason);
            }
            if ($int_level_by_id >= 3) {
                $res = $appUserInfo->checkApplyUpgrade($app_id, $int_level_by_id, $goal_level);
            }
            $level_map = config('level_config');
            if (!$res) {
                return $this->getInfoResponse('1004', $no_conform_reason);
            }
            if ($int_level_by_id >= 3) {
                return $this->getInfoResponse('1002', '转正以上等级申请经理,请满足条件以后联系客服！');
            }
            return [
                'code' => 200,
                'message' => '成功',
                'data' => ['to_apply_level' => $level_map[$res]],
            ];
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 查看分红记录
     */
    public function getBonusLog(Request $request, BonusLog $bonusLog)
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
            $data['bonus_total'] = $bonusLog->getBonusTotalByUserId($app_id);
            $data['list'] = $bonusLog->getBonusList($app_id);
            foreach ($data['list']->items() as &$data_one) {
                $data_one->create_time = date('Y-m-d H:i:s', $data_one->create_time);
            }
            if (empty($data['list']->items())) {
                return $this->getInfoResponse('1001', '获取用户数据失败！');
            }
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 级别申请
     */
    public function ApplyUpgrade(Request $request, AppUserInfo $appUserInfo, ApplyUpgrade $applyUpgrade, WechatInfo $wechatInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'upgrade_image' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $upgrade_image = $arrRequest['upgrade_image'];
            if (!is_array($upgrade_image)) {
                $upgrade_image = str_replace('"', "", $upgrade_image);
                $upgrade_image = str_replace('\\', "", $upgrade_image);
                $upgrade_image = explode(',', $upgrade_image);
            }
            if ($appUserInfo->getChildrenCount($app_id) < 10) {
                return $this->getInfoResponse('4012', '直属用户不足10,不具备申请升级的条件!');
            }
            $arr_directly_id = $appUserInfo->getChildrenId($app_id)->toArray();
            $login_wechat_num = $wechatInfo->whereIn('app_id', $arr_directly_id)->count();
            if ($login_wechat_num < 10) {
                return $this->getInfoResponse('1006', "直推用户微信登陆少于10，不具备升级条件");
            }


            /***********************************/
            if (Cache::has('apply_upgrade_' . $app_id)) {
                return $this->getInfoResponse('1005', '您已申请成功，请勿重复操作！');
            }
            Cache::put('apply_upgrade_' . $app_id, 1, 1);
            $max_level = (int)config('level_config.max');
            $int_level_by_id = $appUserInfo->getFieldsData($app_id, 'level');
            if (!($int_level_by_id < $max_level)) {
                return $this->getInfoResponse('1001', '已到达当前系统最高等级！');
            }
            $check_has_apply_upgrade = $applyUpgrade->checkHasApplyUpgrade($app_id);
            if ($check_has_apply_upgrade) {
                return $this->getInfoResponse('1002', '当前有升级请求正在等待处理,请勿重复提交！');
            }

            $apply_upgrade = new ApplyUpgrade();
            $res = $apply_upgrade->saveApplyUpgrade($arrRequest, $upgrade_image);
            if (!$res) {
                return $this->getInfoResponse('1003', '失败');
            }
            return $this->getResponse('成功');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
