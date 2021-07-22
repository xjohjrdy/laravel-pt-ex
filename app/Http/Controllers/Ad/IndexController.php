<?php

namespace App\Http\Controllers\Ad;

use App\Entitys\Ad\AdMenu;
use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAccount;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\JsonConfig;
use App\Entitys\App\UserHigh;
use App\Exceptions\ApiException;
use App\Services\Advertising\AdvertisingUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller
{
    /**
     *
     * {"user_id":"1777999"}
     * @param Request $request
     * @param AdMenu $adMenu
     * @param AdUserInfo $adUserInfo
     * @param UserAccount $userAccount
     * @param AppUserInfo $appUserInfo
     * @param AdvertisingUser $advertisingUser
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     */
    public function index(Request $request, AdMenu $adMenu, AdUserInfo $adUserInfo, UserAccount $userAccount, AppUserInfo $appUserInfo, AdvertisingUser $advertisingUser)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['user_id'] == 0) {
                return $this->getInfoResponse(4004, '用户信息未注册，请退出重新登录');
            }
            $app_user = $appUserInfo->getUserById($arrRequest['user_id']);
            if (!$app_user) {
                return $this->getInfoResponse(3004, '数据错乱！请勿随意使用id');
            }
            $level_show = "无等级";
            if ($app_user->level == 1) {
                $level_show = "无";
            }
            if ($app_user->level == 2) {
                $level_show = "实习";
            }
            if ($app_user->level == 3) {
                $level_show = "转正";
                $userHigh = new UserHigh();
                $user_high = $userHigh->getUserHigh($arrRequest['user_id']);
                if ($user_high && $user_high->number >= 1) {
                    $level_show = "优质转正";
                }
                if ($app_user->active_value >= config('putao.active_all_high')) {
                    $level_show = "优质转正";
                }
            }
            if ($app_user->level == 4) {
                $level_show = "经理";
            }
            if ($app_user->level == 5) {
                $level_show = "董事";
            }
            $res_is_need_level = $advertisingUser->isNeedLevel($arrRequest['user_id']);
            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            if (!$user) {
                $user = $adUserInfo->getUserByUsername($app_user->phone);
                if ($user) {
                    $app_user->phone = $app_user->phone . '_' . rand(0, 9);
                }
                $user = $advertisingUser->compatibleAddUsers($app_user->phone, $app_user->id, $app_user->parent_id, $request->ip());
            }
            $adUserInfo->relationshipReset($app_user->parent_id, $app_user->id, $app_user->phone);
            $account = $userAccount->getUserAccount($user->uid);
            $menu = $adMenu->getMenu();
            $menu->map(function ($model) {
                if ($model->id == 39 || $model->id == 35 || $model->id == 36 || $model->id == 21 || $model->id == 29) {
                    $model->close = 1;
                } else {
                    $model->close = 0;
                }
            });
            $user->username = $user->pt_username;

            //直播显示
            $obj_config = new JsonConfig();
            $ali_resq = $obj_config->getValue('is_live');
            $is_live = $ali_resq['is_live'];
            //临时增加
            $request_device = $request->header('Accept-Device'); //设备类型
            if ($request_device == 'android') {
                $is_live = 1;
            } else {
                $is_live = 1;
            }

            return $this->getResponse([
                'user' => $user,
                'account' => $account,
                'menu' => $menu,
                'is_need_level' => $res_is_need_level,
                'level' => $level_show,
                'level_int' => $app_user->level,
                'is_live' => $is_live,
                'taobao_m_data' => [
                    'mm_122930784_46170255_91593200288',
                    'mm_123348922_46184097_98173200486',
                ]
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
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
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
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
