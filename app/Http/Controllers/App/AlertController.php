<?php

namespace App\Http\Controllers\App;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\ActivityAlertConfig;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\WZeroAdd;
use App\Exceptions\ApiException;
use App\Services\Advertising\UserGroupUpgrade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AlertController extends Controller
{
    /**
     * 获取首页弹窗信息配置
     * @param Request $request
     * @param ActivityAlertConfig $activityAlertConfig
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getUserShareInfo(Request $request, ActivityAlertConfig $activityAlertConfig)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
//            $arrRequest = json_decode($request->data, true);
//            $rules = [
//                'app_id' => 'required',
//            ];
//            $validator = Validator::make($arrRequest, $rules);
//            if ($validator->fails()) {
//                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
//            }

            $res_alert = $activityAlertConfig->getIndex();

            return $this->getResponse($res_alert);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 升级合伙人接口
     */
    public function isUpgrade(Request $request, UserGroupUpgrade $groupUpgrade, AdUserInfo $adUserInfo)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $uid_info = $adUserInfo->getUidById($arrRequest['app_id']);

            if (empty($uid_info)) {
                return $this->getResponse('no_ad');
            }

            $userInfo = $groupUpgrade->isUpgrade($uid_info);
            if ($userInfo) {
                return $this->getResponse('no');
            }
            $resAdd = $groupUpgrade->addPTB();
            if (empty($resAdd)) {
                throw new ApiException('添加葡萄币错误', 3001);
            }
            $resUpdate = $groupUpgrade->updateGroupId();
            if (empty($resUpdate)) {
                throw new ApiException('更改用户组失败', 3002);
            }

            return $this->getResponse('ok');
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获得ip
     */
    public function getIp(Request $request, WZeroAdd $WZeroAdd)
    {
        //拦截版本
        $request_device = $request->header('Accept-Device'); //设备类型
        $request_appversion = $request->header('Accept-Appversion'); //版本号
        $user_device_id = $request->header('User-Device-Id'); //版本号
        $ip = [
            [
                "adv_key" => "101153",
                "adv_type" => "1"
            ],
            [
                "adv_key" => "101456",
                "adv_type" => "2"
            ],
            [
                "adv_key" => "101241",
                "adv_type" => "3"
            ]
        ];

        $arrRequest = $request->input();
        $arrRequest['number'] = $WZeroAdd->isInfo($user_device_id);

        if (!empty($arrRequest['number'])) {
            $zero = $arrRequest['number'] % 9;

            if ($zero <= 2) {
                $ip = [
                    [
                        "adv_key" => "101153",
                        "adv_type" => "1"
                    ],
                    [
                        "adv_key" => "101456",
                        "adv_type" => "2"
                    ],
                    [
                        "adv_key" => "101241",
                        "adv_type" => "3"
                    ]
                ];
            }
            /**
             * 1.中国
             * 1w
             * 1w
             * 6.交通
             * 7.中信
             * 5w+1.1w
             */


            if ($zero <= 5 && $zero >= 3) {

                $ip = [
                    [
                        "adv_key" => "101456",
                        "adv_type" => "2"
                    ],
                    [
                        "adv_key" => "101241",
                        "adv_type" => "3"
                    ],
                    [
                        "adv_key" => "101153",
                        "adv_type" => "1"
                    ]
                ];
            }

            if ($zero <= 8 && $zero >= 6) {
                $ip = [
                    [
                        "adv_key" => "101241",
                        "adv_type" => "3"
                    ],
                    [
                        "adv_key" => "101153",
                        "adv_type" => "1"
                    ],
                    [
                        "adv_key" => "101456",
                        "adv_type" => "2"
                    ]
                ];

            }


        }

        if ($request_device == 'ios') {
            $ip = [
                [
                    "adv_key" => "101164",
                    "adv_type" => "1"
                ],
                [
                    "adv_key" => "101165",
                    "adv_type" => "4"
                ]
            ];
        }


        return $this->getResponse($ip);
    }
}
