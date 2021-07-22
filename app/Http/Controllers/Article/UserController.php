<?php

namespace App\Http\Controllers\Article;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\Article\Agent;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
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
     * 更新用户的文章数量
     * 传入：
     * 1.用户id
     * 返回：
     * 1.用户username
     * 2.用户文章数更新时间
     * 3.剩余文章数
     * Store a newly created resource in storage.
     * 问题1(注意：用户充值广告包、权限类用户就要增加记录(而且要详细))(如果购买时间正好在3号20点30分以前，则不增加数量)
     * 1.用户旧数据
     * 2.迁移旧数据-》新数据结构
     * 3.由于头条数量在不断变化，需要采用增量迁移
     * 问题2
     * 1.每个月自动赠送用户头条文章数量，累计
     * 问题3
     * 1.展示页面内容
     * @param Request $request
     * @param AdUserInfo $adUserInfo
     * @param Agent $agent
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, AdUserInfo $adUserInfo, Agent $agent, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest) {
                throw new ApiException('传入参数错误', '3001');
            }
            $user = $adUserInfo->appToAdUserId($arrRequest['user_id']);
            if ($user) {
                $agent->updateOrInsert(['pt_id' => $arrRequest['user_id']], [
                    'username' => $user->username,
                    'pt_id' => $user->pt_id,
                    'uid' => $user->uid,
                ]);
            } else {
                return $this->getInfoResponse('4004', '未注册广告联盟');
            }

            $user_agent = $agent->getAgent($arrRequest['user_id']);
            if (!empty($user_agent)) {
                $app_user = $appUserInfo->getUserById($user->pt_id);
                $user_agent->username = $app_user->phone;
            }
            return $this->getResponse($user_agent);
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
