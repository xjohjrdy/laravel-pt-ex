<?php

namespace App\Http\Controllers\Wechat\Assistant;

use App\Entitys\App\WechatAssistantAudit;
use App\Exceptions\ApiException;
use App\Extend\Random;
use App\Services\Wechat\AssistantService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * 群助手
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function info(Request $request)
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
            $service = new AssistantService($app_id);
            $user_info = $service->getUserInfo();
            $user_info['expiry_time'] = date('Y-m-d H:i:s', $user_info['expiry_time']);
            $package = $service->getPackageInfo();
            if (!empty($user_info['robot_info'])) {
                $robot_info = $user_info['robot_info'];
                $user_info['login_status'] = $robot_info['login_status'];
                $user_info['is_enabled'] = $robot_info['is_enabled'];
                unset($user_info['robot_info']);
            }
            return $this->getResponse([
                'user' => $user_info,
                'package' => $package
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    const OFFLINE           = 'offline';
    const INSPECT_SCAN      = 'inspect.scan';
    const ONLINE            = 'online';
    const CHANGE_CIRCLE     = 'change.circle';
    const CHANGE_GROUP      = 'change.group';
    const LIST_GROUP        = 'list.group';
    const LIST_GROUP_DETAIL = 'list.group.detail';
    const LIST_CHECK_GROUP  = 'list.check.group';
    const SET_GROUP         = 'set.group';
    const REMOVE_GROUP      = 'remove.group';
    const LOGIN_QR_CODE     = 'login.qr.code';
    const AGREE_TIP         = 'agree.tip';
    const INSECT_ROBOT      = 'inspect.robot';
    const SECOND_LOGIN      = 'second.login';
    public function getWay(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'method' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $method = $arrRequest['method'];
            $rules = [];
            switch ($method) {
                case self::SET_GROUP:
                    $rules['user_name'] = 'required';
                    $rules['wx_id'] = 'required';
                    break;
                case self::LIST_CHECK_GROUP:
                    $rules['wx_id'] = 'required';
                    break;
                case self::REMOVE_GROUP:
                    $rules['user_name'] = 'required';
                    $rules['wx_id'] = 'required';
                    break;
                case self::SECOND_LOGIN:
                    break;
                case self::OFFLINE:
                    break;
                case self::ONLINE:
                    $rules['wId'] = 'required';
                    break;
                case self::INSPECT_SCAN:
                    break;
                case self::CHANGE_GROUP:
                    $rules['status'] = Rule::in([0, 1]);
                    break;
                case self::CHANGE_CIRCLE:
                    $rules['status'] = Rule::in([0, 1]);
                    break;
                case self::LOGIN_QR_CODE:
                    break;
                case self::AGREE_TIP:
                    break;
                case self::INSECT_ROBOT:
                    break;
                case self::LIST_GROUP:
                    break;
                case self::LIST_GROUP_DETAIL:
                    $rules['user_names'] = 'required|array';
                    break;
            }
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $service = new AssistantService($app_id);
            $data = "";
            switch ($method) {
                case self::SET_GROUP:
                    $header_img = empty($arrRequest['header_img']) ? '' : $arrRequest['header_img'];
                    $group_id = $arrRequest['user_name'];
                    $name = $arrRequest['nike_name'];
                    $wx_id = $arrRequest['wx_id'];
                    $service->setGroup($group_id, $header_img, $name, $wx_id);
                    break;
                case self::REMOVE_GROUP:
                    $group_id = $arrRequest['user_name'];
                    $wx_id = $arrRequest['wx_id'];
                    $service->removeGroup($group_id, $wx_id);
                    break;
                case self::SECOND_LOGIN:
                    $data = $service->secondOnline();
                    break;
                case self::OFFLINE:
                    $data = $service->offLine();
                    break;
                case self::ONLINE:
                    $wId = $arrRequest['wId'];
                    $data = $service->onLine($wId); // [.....]
                    break;
                case self::INSPECT_SCAN:
                    $data = $service->inspectScan(); // false / true
                    break;
                case self::CHANGE_CIRCLE:
                    $status = $arrRequest['status'];
                    $service->changeCircle($status);
                    break;
                case self::LOGIN_QR_CODE:
                    $data = $service->loginQrCode();
                    break;
                case self::CHANGE_GROUP:
                    $status = $arrRequest['status'];
                    $service->changeGroup($status);
                    break;
                case self::INSECT_ROBOT:
                    $data = $service->inspectRobotDetail();
                    break;
                case self::AGREE_TIP:
                    $service->agreeTip();
                    break;
                case self::LIST_GROUP:
                    $data = $service->groupList();
                    break;
                case self::LIST_CHECK_GROUP:
                    $wx_id = $arrRequest['wx_id'];
                    $data = $service->checkGroupList($wx_id);
                    break;
                case self::LIST_GROUP_DETAIL:
                    $user_names = $arrRequest['user_names'];
                    $data = $service->groupListDetails($user_names);
                    break;
                default:
                    return $this->getInfoResponse('1101', '无效的method');
                    break;
            }
            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' .$e->getMessage() .  $e->getLine(), '500');
        }
    }
}
