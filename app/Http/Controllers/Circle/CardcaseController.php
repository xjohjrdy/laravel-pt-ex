<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleCardcase;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CardcaseController extends Controller
{
    /**
     * 获取用户名片
     * @param Request $request
     * @param CircleCardcase $circleCardcase
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleCardcase $circleCardcase, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $arrRequest, 3002);
            }

            $user = $appUserInfo->getUserById($arrRequest['app_id']);

            $card = $circleCardcase->getUserInfo($arrRequest['app_id'], $user);
            $create_number = 0;
            $add_number = 0;
            $friend = 0;
            $fans = 0;


            $card->create_number = $create_number;
            $card->add_number = $add_number;
            $card->friend = $friend;
            $card->fans = $fans;

            return $this->getResponse($card);
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
     * {"app_id":"1","username":"1","ico_img":"1569840","content":"0","wechat":"0","area":"1","qq":"123","phone":"123","talk":"123"}
     * 更新用户名片
     * @param Request $request
     * @param $id
     * @param CircleCardcase $circleCardcase
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, CircleCardcase $circleCardcase)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'username' => 'required',
                'ico_img' => 'required',
                'content' => 'required',
                'wechat' => 'required',
                'area' => 'required',
                'qq' => 'required',
                'phone' => 'required',
                'talk' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $circleCardcase->updateCardcase($arrRequest['app_id'], $arrRequest);

            return $this->getResponse('修改成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
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
