<?php

namespace App\Services\EleAdmin\PutNew;

use App\Services\Service;
use App\Models\EleAdmin\PutNew\Rank as RankModel;

class RankService extends Service
{
    /**
     * 新增
     * @param $params
     * @return array
     */
    public static function add($params)
    {
        $avatar = $params['avatar'] ?? null;
        $showInfo = $params['show_info'] ?? null;
        $successAdd = $params['success_add'] ?? 0;

        if (!$avatar) {
            return ['code' => 1000, 'message' => '用户头像不能为空'];
        }
        if (!$showInfo) {
            return ['code' => 1000, 'message' => '用户信息不能为空'];
        }

        $dateTime = date('Y-m-d H:i:s');
        $save['avatar'] = $avatar;
        $save['show_info'] = $showInfo;
        $save['success_add'] = intval($successAdd);
        $save['change'] = RankModel::CHANGE_ADMIN;
        $save['created_at'] = $dateTime;
        $save['updated_at'] = $dateTime;

        $id = RankModel::insertGetId($save);
        if (!$id) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        return ['code' => 200, 'message' => '操作成功！'];
    }

    /**
     * 编辑
     * @param $params
     * @return array
     */
    public static function edit($params)
    {
        $id = $params['id'] ?? null;
        $avatar = $params['avatar'] ?? null;
        $showInfo = $params['show_info'] ?? null;
        $successAdd = $params['success_add'] ?? 0;

        if (!$id) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $rank = RankModel::find($id);
        if (empty($rank)) {
            return ['code' => 1000, 'message' => '参数异常'];
        }
        if ($rank->change == RankModel::CHANGE_SCRIPT) {
            return ['code' => 1000, 'message' => '无法执行此操作'];
        }

        if (!$avatar) {
            return ['code' => 1000, 'message' => '用户头像不能为空'];
        }
        if (!$showInfo) {
            return ['code' => 1000, 'message' => '用户信息不能为空'];
        }

        $dateTime = date('Y-m-d H:i:s');
        $rank->avatar = $avatar;
        $rank->show_info = $showInfo;
        $rank->success_add = $successAdd;
        $rank->updated_at = $dateTime;

        $result = $rank->save();
        if ($result ===false) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        return ['code' => 200, 'message' => '操作成功！'];
    }
}