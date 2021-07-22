<?php

namespace App\Http\Controllers\Recharge;

use App\Entitys\Ad\AdUserInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{
    /**
     *
     * 用户三级团队列表
     * {"user_id":"1569840","type":"1"}
     * @param Request $request
     * @param AdUserInfo $adUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request,AdUserInfo $adUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id',$arrRequest) || !array_key_exists('type',$arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $user_three = $adUserInfo->getUserThreeFloor($arrRequest['user_id'],$arrRequest['type']);

            return $this->getResponse($user_three);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
