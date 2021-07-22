<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\CircleCityKing;
use App\Entitys\App\CircleCityKingAdd;
use App\Entitys\App\CircleRing;
use App\Entitys\App\UserHigh;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CityKingAddController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * 加入城主
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, CircleRing $circleRing, UserHigh $userHigh, AdUserInfo $adUserInfo, CircleCityKing $circleCityKing, CircleCityKingAdd $circleCityKingAdd)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'king_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $is_have = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            if (empty($is_have)) {
                return $this->getInfoResponse('4004', '请先使用广告联盟!');
            }
            $res = $userHigh->getUserHigh($arrRequest['app_id']);
            $is_have_city = $circleCityKingAdd->getByAppId($arrRequest['app_id']);
            if ($is_have->groupid == 24 && $res->number >= 3 && empty($is_have_city)) {
                $circleCityKingAdd->createNewAdd($arrRequest['king_id'], $arrRequest['app_id']);
                $circleCityKing->updateNewKing($arrRequest['king_id'], $arrRequest['app_id']);
                return $this->getResponse('成为城主成功！');
            }

            return $this->getInfoResponse('5000', '您还未满足成为城主的条件！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
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
