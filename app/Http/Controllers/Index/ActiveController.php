<?php

namespace App\Http\Controllers\Index;

use App\Entitys\App\AppActive;
use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;
use App\Services\Commands\CountEverydayService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActiveController extends Controller
{
    /**
     * 展示用户本月总分
     * @param Request $request
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $user = $appUserInfo->getUserById($arrRequest['user_id']);

            if (!$user) {
                return $this->getInfoResponse('4004', '不存在这个用户');
            }

            return $this->getResponse(['value' => config('putao.active_all')]);

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
     * 活跃值详情 get/active/#id
     * {"user_id": "1620824","time":"1527868800"}
     * @param $id
     * @param Request $request
     * @param AppActive $appActive
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, AppActive $appActive)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id', $arrRequest) || $arrRequest['user_id'] != $id || $arrRequest['user_id'] == 0) {
                throw new ApiException('传入参数错误', '3001');
            }
            $obj_user = new AppUserInfo();
            $int_user_level = $obj_user->where('id', $arrRequest['user_id'])->value('level');
            if ($int_user_level == 1){
                $obj_order = new CountEverydayService();
                if ($obj_order->isValid($arrRequest['user_id'])) {
                    return $this->getResponse(json_decode('{"1":"0.00","2":"0.00","3":"0.00","4":"0.00","5":"0.00","6":"0.00","7":"0.00","8":"0.00"}'));
                }
            }


            $res = $appActive->getOneDays($arrRequest['user_id'], $arrRequest['time']);

            if (!$res) {
                return $this->getInfoResponse('4004', '不存在这个记录');
            }

            return $this->getResponse(json_decode($res->context));

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
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

    /**
     * 获取规则web页面
     */
    public function getRegulation()
    {
        return view('active.active');
    }
}
