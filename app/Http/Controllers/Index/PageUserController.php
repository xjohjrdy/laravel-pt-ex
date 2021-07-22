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

class PageUserController extends Controller
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
    public function index()
    {
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
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id', $arrRequest) || $arrRequest['user_id'] != $id || $arrRequest['user_id'] == 0) {
                throw new ApiException('传入参数错误', '3001');
            }

            $arr = $appUserInfo->getNextFloorPage($arrRequest['user_id']);
            foreach ($arr["data"] as $key => $item) {

                //10000000
                $new_app_id = $item['id'];
                if ($item['id'] >= 10000000) {
                    $new_app_id = base_convert($item['id'], 10, 33); // 10 转 33
                    $new_app_id = 'x' . $new_app_id;
                }

                $arr["data"][$key]['show_id'] = $new_app_id;


                $user_high = $userHigh->getUserHigh($item['id']);
                if ($user_high && $user_high->number >= 1) {
                    $arr["data"][$key]['level'] = 0;
                }
                if ($item['active_value'] >= config('putao.active_all_high')) {
                    $arr["data"][$key]['level'] = 0;
                }
                $user = $adUserInfo->appToAdUserId($item['id']);
                if ($user) {
                    $arr["data"][$key]['role'] = $user->groupid;
                }
                $arr["data"][$key]['count'] = $appUserInfo->getNextFloorCount($item['id']);
                $arr["data"][$key]['real_Name'] = $arr["data"][$key]['real_name'];
                $obj_user_wechat_show = new UserWechatShow();
                $arr["data"][$key]['wechat_info'] = $obj_user_wechat_show->where('app_id', $item['id'])->value('wechat_info');
                $obj_is_user_wechat_show = new WechatInfo();
                $arr["data"][$key]['is_wechat_info'] = $obj_is_user_wechat_show->getAppId($item['id']) ? 1 : 0;
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
