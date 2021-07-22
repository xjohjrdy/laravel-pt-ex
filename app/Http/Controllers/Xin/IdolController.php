<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\UserWechatShow;
use App\Entitys\App\WechatInfo;
use App\Entitys\Xin\UserIdol;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IdolController extends Controller
{
    /*
     * 创建偶像
     */
    public function createParentUser(Request $request, AppUserInfo $appUserInfo, UserIdol $userIdol, AdUserInfo $adUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'min:0',
                'parent_id' => 'min:0',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $parent_id = $arrRequest['parent_id'];
            if ($parent_id >= $app_id) {
                return $this->getInfoResponse('1004', '设定的偶像，注册的时间不可晚于自身');
            }

            //判断输入放入偶像是否为33进制
            if (strpos($parent_id, 'x') === 0) {
                $parent_id = base_convert(substr($parent_id, 1), 33, 10);
            }

            $obj_get_user_info = $appUserInfo->getUserInfo($app_id);
            $obj_idol_user_info = $appUserInfo->getIdolInfo($parent_id);
            if (empty($obj_idol_user_info)) {
                return $this->getInfoResponse('1001', '要创建的偶像不存在！');
            }
            if (!$obj_get_user_info->parent_id) {
                $obj_get_user_info->order_can_apply_amount += 100;
            } else {
                return $this->getInfoResponse('1006', '不允许修改偶像!');
            }
            $obj_get_user_info->parent_id = $parent_id;
            $res = $obj_get_user_info->save();
            if (!$res) {
                return $this->getInfoResponse('1002', '首次创建偶像失败！');
            }
            $userIdol->app_id = $app_id;
            $userIdol->parent_id = $parent_id;
            $userIdol->save();
            $res_update_ad = $adUserInfo->updateSyncPtPid($app_id, $parent_id);
            if (!$res_update_ad) {
                return $this->getInfoResponse('1003', '同步创建联盟上级失败！');
            }
            return $this->getResponse('偶像创建成功！');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到用户偶像名
     */
    function getTargetUserName(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'parent_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $parent_id = $arrRequest['parent_id'];
            /***********************************/
            //判断输入放入偶像是否为33进制
            if (strpos($parent_id, 'x') === 0) {
                $parent_id = base_convert(substr($parent_id, 1), 33, 10);
            }

            $obj_target_user_name = $appUserInfo->getTaegetUserName($parent_id);
            if (empty($obj_target_user_name)) {
                return $this->getInfoResponse('1001', '获取用户偶像名失败！');
            }
            return $this->getResponse($obj_target_user_name);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到我的偶像数据
     */
    function getParentUserInfo(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'parent_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $parent_id = $arrRequest['parent_id'];
            /***********************************/
            $res = $appUserInfo->isCorresponding($app_id, $parent_id);
            if (!$res) {
                return $this->getInfoResponse('1001', '用户id和偶像id不对应！');
            }
            $obj_parent_user = $appUserInfo->getParentUserInfo($parent_id);
            if (empty($obj_parent_user)) {
                return $this->getInfoResponse('1002', '获取偶像数据失败！');
            }
            $data = $obj_parent_user->toArray();
            $data['create_time'] = date('Y-m-d', strtotime($obj_parent_user->create_time));

            if ($data['parent_id'] >= 10000000) {
                $data['parent_id'] = base_convert($data['parent_id'], 10, 33); // 10 转 33
                $data['parent_id'] = 'x' . $data['parent_id'];
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
     * 得到我的偶像信息
     */
    function getMyIdolInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //得到上级id 和第一个合伙人id
            $AdUserInfo = new AdUserInfo();
            $appUserInfo = new AppUserInfo();
            $userWechatShow = new UserWechatShow();

            //得到用户信息
            $obj_ad_info = $AdUserInfo->where('pt_id', $app_id)->first(['groupid', 'pt_pid', 'pt_id']);
            if (empty($obj_ad_info)) {
                return $this->getInfoResponse('1001', '该用户不存在！');
            }

            //得到直属上级app_id
            $next_app_id = $obj_ad_info->pt_pid;

            for ($i = 0; $i < 50; $i++) {
                if (empty($next_app_id)) {
                    break;
                }

                //得到上级信息
                $parent_info = $AdUserInfo->where('pt_id', $next_app_id)->first(['groupid', 'pt_pid', 'pt_id']);

                if (empty($parent_info)) {
                    $parent_info = $appUserInfo->where('id', $next_app_id)->first(['parent_id', 'id']);
                    $parent_info->groupid = 10;
                    $parent_info->pt_id = $parent_info->id;
                    $parent_info->pt_pid = $parent_info->parent_id;
                }

                if ($i == 0) {
                    if ($parent_info->groupid == 24) {
                        $direct_app_id = $parent_info->pt_id;
                        $one_partner_app_id = $parent_info->pt_id;
                        break;
                    } else {
                        $next_app_id = $parent_info->pt_pid;
                        $direct_app_id = $parent_info->pt_id;
                    }
                } else {
                    if ($parent_info->groupid == 24) {
                        $one_partner_app_id = $parent_info->pt_id;
                        break;
                    }
                    $next_app_id = $parent_info->pt_pid;
                    continue;
                }
            }

            $data = [];

            //得到用户微信号
            $data['user']['wechat_info'] = $userWechatShow->where('app_id', $app_id)->value('wechat_info');
            $data['parent']['wechat_info'] = $userWechatShow->where('app_id', @$direct_app_id)->value('wechat_info');
            $data['partner']['wechat_info'] = $userWechatShow->where('app_id', @$one_partner_app_id)->value('wechat_info');

            //得到用户的等级
            $data['user']['groupid'] = $AdUserInfo->where('pt_id', $app_id)->value('groupid') ?? 10;
            $data['parent']['groupid'] = $AdUserInfo->where('pt_id', @$direct_app_id)->value('groupid') ?? 10;
            $data['partner']['groupid'] = $AdUserInfo->where('pt_id', @$one_partner_app_id)->value('groupid') ?? 10;

            //得到用户头像
            $data['user']['avatar'] = $appUserInfo->where('id', $app_id)->value('avatar');
            $data['parent']['avatar'] = $appUserInfo->where('id', @$direct_app_id)->value('avatar');
            $data['partner']['avatar'] = $appUserInfo->where('id', @$one_partner_app_id)->value('avatar');

            //得到用户昵称
            $data['user']['user_name'] = $appUserInfo->where('id', $app_id)->value('user_name');
            $data['parent']['user_name'] = $appUserInfo->where('id', @$direct_app_id)->value('user_name');
            $data['partner']['user_name'] = $appUserInfo->where('id', @$one_partner_app_id)->value('user_name');

            //得到用户app_id
            $data['user']['app_id'] = $app_id;
            $data['parent']['app_id'] = @$direct_app_id;
            $data['partner']['app_id'] = @$one_partner_app_id;

            if ($app_id >= 10000000) {
                $data['user']['app_id'] = 'x' . base_convert($app_id, 10, 33); // 10 转 33
            }
            if (@$direct_app_id >= 10000000) {
                $data['parent']['app_id'] = 'x' . base_convert(@$direct_app_id, 10, 33); // 10 转 33
            }
            if (@$one_partner_app_id >= 10000000) {
                $data['partner']['app_id'] = 'x' . base_convert($one_partner_app_id, 10, 33); // 10 转 33
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
}
