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

    const OFFLINE = 'offline';
    const INSPECT_SCAN = 'inspect.scan';
    const ONLINE = 'online';
    const CHANGE_CIRCLE = 'change.circle';
    const CHANGE_GROUP = 'change.group';
    const LOGIN_QR_CODE = 'login.qr.code';
    const AGREE_TIP = 'agree.tip';
    const INSECT_ROBOT = 'inspect.robot';

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
            switch ($method) {
                case self::OFFLINE:
                    return $this->getInfoResponse('1102', '功能开发中');
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
                default:
                    return $this->getInfoResponse('1101', '无效的method');
                    break;
            }
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $service = new AssistantService($app_id);
            $data = "";
            switch ($method) {
                case self::OFFLINE:
                    $service->offLine();
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
            }
            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
