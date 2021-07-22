<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleFriend;
use App\Entitys\App\CircleRing;
use App\Entitys\App\CircleRingAdd;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FriendController extends Controller
{
    /**
     * 好友列表
     * @param Request $request
     * @param CircleFriend $circleFriend
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CircleFriend $circleFriend)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }
            $friends = $circleFriend->getAllUserFriend($arrRequest['app_id']);
            return $this->getResponse($friends);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 由于需求的变化，好友列表不仅仅只有增加好友列表，还要获取所有的圈子列表
     * @param Request $request
     * @param CircleFriend $circleFriend
     * @param CircleRingAdd $circleRingAdd
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getAllIndex(Request $request, CircleFriend $circleFriend, CircleRingAdd $circleRingAdd, CircleRing $circleRing)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }

            $friends = $circleFriend->getAllUserFriend($arrRequest['app_id']);
            $all_ring_add = $circleRingAdd->getByAppId($arrRequest['app_id']);

            foreach ($all_ring_add as $item) {
                $circle_ring = $circleRing->getById($item->circle_id);
                if (empty($circle_ring)) {
                    continue;
                }
                $item->ico_img = $circle_ring->ico_img;
                $item->ico_title = $circle_ring->ico_title;
            }

            return $this->getResponse([
                'friend' => $friends,
                'circle' => $all_ring_add,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 查询当前的用户，圈子，群聊（模糊查询）
     * @param Request $request
     * @param CircleFriend $circleFriend
     * @param CircleRing $circleRing
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function findAllIndex(Request $request, CircleFriend $circleFriend, CircleRing $circleRing)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['keyword'])) {
                throw new ApiException('传入参数错误', '3001');
            }

            $friends = $circleFriend->getByLike($arrRequest['keyword']);
            $circle_ring = $circleRing->getByLike($arrRequest['keyword']);

            return $this->getResponse([
                'friend' => $friends,
                'circle' => $circle_ring,
                'chat' => $circle_ring,
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
     * 搜索陌生人
     * @param $id
     * @param Request $request
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show($id, Request $request, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }
            if (empty($arrRequest['is_phone'])) {
                $user = $appUserInfo->getUserById($id);
            } else {
                $user = $appUserInfo->getUserByPhone($id);
            }
            if (empty($user)) {
                return $this->getInfoResponse('4004', '当前用户不存在！');
            }
            $circleFriend = new CircleFriend();
            $circle_friend = $circleFriend->getUser($arrRequest['app_id'], $id);
            if (empty($circle_friend)) {
                $is_have = 0;
            } else {
                $is_have = 1;
            }
            $user_info = [
                'avatar' => $user->avatar,
                'real_name' => $user->real_name,
                'is_check' => 1,
                'qr_code' => 'http://api.36qq.com',
                'number' => $user->id,
                'address' => '北京市',
                'summary' => '暂无',
                'dynamic' => '暂无动态',
                'is_have' => $is_have
            ];
            return $this->getResponse($user_info);
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
     * 删除好友
     * @param $id
     * @param Request $request
     * @param CircleFriend $circleFriend
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function destroy($id, Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'friend_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circleFriend = new CircleFriend();
            $circleFriend->deleteFriend($arrRequest['app_id'], $arrRequest['friend_id']);
            return $this->getResponse('删除成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
