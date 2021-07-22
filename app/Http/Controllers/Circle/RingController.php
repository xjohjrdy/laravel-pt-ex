<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleActive;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\CircleRingType;
use App\Exceptions\ApiException;
use App\Services\Circle\BecomeHost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class RingController extends Controller
{
    public $is_service_arr = ['1569840'];

    /**
     * 拉出列表
     * 针对多种查询方式
     * {"type":"1"}
     * 查询种类
     *
     * @param Request $request
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleRing $circleRing, AppUserInfo $appUserInfo, CircleActive $circleActive, CircleRingAdd $circleRingAdd, CircleRingType $circleRingType)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['type'])) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['type'] == 1) {
                $rules = [
                    'type' => 'required',
                    'app_id' => 'required'
                ];
                $validator = Validator::make($arrRequest, $rules);
                if ($validator->fails()) {
                    throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
                }

                $redis_key = 'circle_list_ring_' . $arrRequest['app_id'] . 'active_' . $request->get('page');
                if (Cache::has($redis_key)) {
                    $list_active = Cache::get($redis_key);
                } else {
                    $list_active = $circleActive->getNewIndexList();
                    foreach ($list_active as $k => $item) {
                        $list_active[$k]->is_in = $circleRingAdd->getByAppCircle($item->circle_id, $arrRequest['app_id']);
                        $list_active[$k]->new_number_person = 0;
                        $ring_number_person = $circleRing->getById($item->circle_id);
                        if (!empty($ring_number_person)) {
                            $list_active[$k]->new_number_person = $ring_number_person->number_person;
                            $list_active[$k]->new_circle_ico_img = $ring_number_person->ico_img;
                        }
                        $ring_add = $circleRingAdd->getThreeByCircle($item->circle_id);
                        $avatars = [];
                        foreach ($ring_add as $t => $value) {
                            $app_user = $appUserInfo->getUserById($value->app_id);
                            $avatars[$t] = 0;
                            if (!empty($app_user)) {
                                $avatars[$t] = $app_user->avatar;
                            }
                        }
                        $list_active[$k]->new_user = $avatars;
                        $list_active[$k]->group_id = 10;
                    }
                    Cache::put($redis_key, $list_active, 2);
                }

                $is_service = 0;
                if (in_array($arrRequest['app_id'], $this->is_service_arr)) {
                    $is_service = 1;
                }
                $res_recommend = $circleRing->getByRecommend();


                return $this->getResponse([
                    'is_service' => $is_service,
                    'active' => $list_active,
                    'recommend' => $res_recommend,
                ]);
            }
            if ($arrRequest['type'] == 2) {
                $rules = [
                    'type' => 'required',
                    'ring_type' => 'required',
                ];
                $validator = Validator::make($arrRequest, $rules);
                if ($validator->fails()) {
                    throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
                }

                $ring_type = $circleRingType->getList();

                $type_circle = $circleRing->getByType($arrRequest['ring_type']);

                return $this->getResponse([
                    'ring_type' => $ring_type,
                    'circle' => $type_circle,
                ]);
            }

            return $this->getInfoResponse('4004', '不存在任何的类型');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 拉出列表   ---- 都写index太多了，看着太杂乱，分离出来便于看
     * 针对多种查询方式
     * {"type":"1"}
     * @param Request $request
     * @param CircleRing $circleRing
     * @param CircleRingAdd $circleRingAdd
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getMyCircle(Request $request, CircleRing $circleRing, CircleRingAdd $circleRingAdd, BecomeHost $becomeHost)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'filter' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if ($arrRequest['filter'] == -1) {
                $res_my_type = $circleRingAdd->getByAppId($arrRequest['app_id']);
                $res_my = [];
                foreach ($res_my_type as $k => $type) {
                    $res_res_circle = $circleRing->getById($type->circle_id);
                    if (empty($res_res_circle)) {
                        continue;
                    }
                    $res_my[$k] = $res_res_circle;
                    $res_my[$k]->is_need_status = $type->status;
                    if ($becomeHost->isLock($type->circle_id)) {
                        $res_my[$k]->is_need_lock = 1;
                    } else {
                        $res_my[$k]->is_need_lock = 0;
                    }
                    $res_my[$k]->is_need_use = $type->use;
                }
            } else {
                $res_my_type = $circleRingAdd->getByAppIdFilter($arrRequest['app_id'], $arrRequest['filter']);
                $res_my = [];
                foreach ($res_my_type as $k => $type) {
                    $res_res_circle = $circleRing->getById($type->circle_id);
                    $res_my[$k] = $res_res_circle;
                    $res_my[$k]->is_need_status = $type->status;
                    if ($becomeHost->isLock($type->circle_id)) {
                        $res_my[$k]->is_need_lock = 1;
                    } else {
                        $res_my[$k]->is_need_lock = 0;
                    }
                    $res_my[$k]->is_need_use = $type->use;
                }
            }
            
            return $this->getResponse(collect($res_my)->values());
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 针对特殊的情况，拉出当前用户所在圈子的公告排序
     * @param Request $request
     * @param CircleRingAdd $circleRingAdd
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getShowMessage(Request $request, CircleRingAdd $circleRingAdd, CircleRing $circleRing)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $all_message = [];
            $all_ring_add = $circleRingAdd->getByAppId($arrRequest['app_id']);
            foreach ($all_ring_add as $k => $ring_add) {
                $circle_ring = $circleRing->getById($ring_add->circle_id);
                $all_message[$k] = $circle_ring;
            }

            return $this->getResponse($all_message);
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
     *
     * @param $id
     * @param Request $request
     * @param CircleRing $ring
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, CircleRing $ring)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $circle_ring = $ring->getById($arrRequest['circle_id'], 1);
            $is_need_show = 0;
            if ($circle_ring->app_id == $arrRequest['app_id']) {
                $is_need_show = 1;
            }
            return $this->getResponse([
                'is_need_show' => $is_need_show,
                'circle_ring' => $circle_ring,
            ]);
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
     * put
     * @param Request $request
     * @param $id
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, CircleRing $ring)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
                'ico_img' => 'required',
                'ico_title' => 'required',
                'desc' => 'required',
                'area' => 'required',
                'area_land' => 'required',
                'add_price' => 'required',
                'use_time' => 'required',
                'close' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];
            $circle_id = $arrRequest['circle_id'];
            $is_service = 0;
            if (in_array($arrRequest['app_id'], $this->is_service_arr)) {
                $is_service = 1;
            }
            if (!$is_service) {
                $circle_ring = $ring->getById($circle_id, 1);
                if ($arrRequest['app_id'] <> $circle_ring->app_id) {
                    return $this->getInfoResponse('4000', '您没有权限修改这个圈子！');
                }
                if ($circle_ring->buy_number < 3 && ($circle_ring->close <> $arrRequest['close'])) {
                    return $this->getInfoResponse('4000', '未到达锁定修改条件！');
                }
            }
            unset($arrRequest['circle_id']);

            $ring->updateLittle($circle_id, $arrRequest);

            return $this->getResponse('修改成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 退出圈子
     * @param $id
     * @param Request $request
     * @param CircleRing $circleRing
     * @param CircleRingAdd $circleRingAdd
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function destroy($id, Request $request, CircleRingAdd $ringAdd)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'circle_id' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $circle_id = $arrRequest['circle_id'];
            $circleRing = new CircleRing();
            $circle_ring = $ringAdd->getByAppCircle($circle_id, $app_id);
            if (empty($circle_ring)) {
                return $this->getInfoResponse('4000', '您未进入过这个圈子！！');
            }
            $ring = $circleRing->getById($circle_id);

            if (empty($ring)) {
                return $this->getInfoResponse('4000', '圈子不存在！！');
            }

            if ($ring->app_id == $app_id) {
                return $this->getInfoResponse('4000', '圈主不可退出自己的圈子。');
            }

            $ringAdd->deleteRingAdd($circle_id, $app_id);

            return $this->getResponse('退出成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
