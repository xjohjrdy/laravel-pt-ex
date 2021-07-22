<?php

namespace App\Http\Controllers\Index;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\UserHigh;
use App\Entitys\App\UserWechatShow;
use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use App\Services\Advertising\UserGroupUpgrade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\Exception;

class UserController extends Controller
{
    /**
     *
     * 取出用户的我的币提现记录
     * /api/user
     * data:{"user_id": "3"}
     * @param Request $request
     * @param AdUserInfo $adUserInfo
     * @param UserCreditLog $userCreditLog
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, AdUserInfo $adUserInfo, UserCreditLog $userCreditLog)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            if (!$user) {
                throw new ApiException('用户异常', '4004');
            }
            $log = $userCreditLog->getTypeLog($user->uid, "APT");
            $count = $userCreditLog->getTypeLogCount($user->uid, "APT");
            $log->map(function ($model) {
                $model->status = 1;
                $model->money = $model->extcredits4 / 10;
            });

            return $this->getResponse(['username' => $user->username, 'count' => $count, 'log' => $log]);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * get /api/user/#id
     * data:{"user_id": "3"}
     * @param $id
     * @param Request $request
     * @param AppUserInfo $appUserInfo
     * @param AdUserInfo $adUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, AppUserInfo $appUserInfo, AdUserInfo $adUserInfo, UserHigh $userHigh)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id', $arrRequest) || $arrRequest['user_id'] != $id || $arrRequest['user_id'] == 0) {
                throw new ApiException('传入参数错误', '3001');
            }

            $arr = $appUserInfo->getNextFloor($arrRequest['user_id']);
            foreach ($arr as $key => $item) {
                $user_high = $userHigh->getUserHigh($item['id']);
                if ($user_high && $user_high->number >= 1) {
                    $arr[$key]['level'] = 0;
                }
                if ($item['active_value'] >= config('putao.active_all_high')) {
                    $arr[$key]['level'] = 0;
                }
                $user = $adUserInfo->appToAdUserId($item['id']);
                if ($user) {
                    $arr[$key]['role'] = $user->groupid;
                }
                $arr[$key]['count'] = $appUserInfo->getNextFloorCount($item['id']);
                $arr[$key]['real_Name'] = $arr[$key]['real_name'];
                $obj_user_wechat_show = new UserWechatShow();
                $arr[$key]['wechat_info'] = $obj_user_wechat_show->where('app_id', $item['id'])->value('wechat_info');
                $obj_is_user_wechat_show = new WechatInfo();
                $arr[$key]['is_wechat_info'] = $obj_is_user_wechat_show->getAppId($item['id']) ? 1 : 0;

            }


            return $this->getResponse($arr);
        } catch (Exception $e) {
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * @param $uid
     * @param UserGroupUpgrade $groupUpgrade
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function edit($uid, UserGroupUpgrade $groupUpgrade)
    {
        $userInfo = $groupUpgrade->isUpgrade($uid);
        if ($userInfo) {
            return $this->getResponse('no');
        }
        $resAdd = $groupUpgrade->addPTB();
        if (empty($resAdd)) {
            throw new ApiException('添加我的币错误', 3001);
        }
        $resUpdate = $groupUpgrade->updateGroupId();
        if (empty($resUpdate)) {
            throw new ApiException('更改用户组失败', 3002);
        }

        return $this->getResponse('ok');

    }

    /**
     * put /api/user/#id
     * data:{"user_id": "3","secret":"123456"}
     * @param Request $request
     * @param $id
     * @param AdUserInfo $adUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, AdUserInfo $adUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id', $arrRequest) || $arrRequest['user_id'] != $id || $arrRequest['user_id'] == 0) {
                throw new ApiException('传入参数错误', '3001');
            }

            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            if (array_key_exists('secret', $arrRequest)) {
                $user->resetTwoPassword($arrRequest['secret']);
            } else {
                $user->resetTwoPassword();
            }

            return $this->getResponse("成功");

        } catch (Exception $e) {
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
