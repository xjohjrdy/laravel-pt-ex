<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\CircleCityKing;
use App\Entitys\App\CircleRing;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CityKingController extends Controller
{
    /**
     * 获取当前城市的所有圈子信息
     * @param Request $request
     * @param CircleCityKing $circleCityKing
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleCityKing $circleCityKing, CircleRing $circleRing)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'area' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $circle_city_king = $circleCityKing->getByArea($arrRequest['area']);
            if (!empty($circle_city_king)) {
                $circle_ring = $circleRing->getByKingId($circle_city_king->id);
            } else {
                $circle_ring = [];
            }
            $all_circle_ring = $circle_ring;
            return $this->getResponse([
                'city_king' => $circle_city_king,
                'circle_ring' => $circle_ring,
                'all_circle_ring' => $all_circle_ring,
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
     * 搜索
     * @param Request $request
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, CircleRing $circleRing)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'king_id' => 'required',
                'content' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_ring = $circleRing->getByKingIdAndTitle($arrRequest['king_id'], $arrRequest['content']);

            return $this->getResponse($circle_ring);
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
