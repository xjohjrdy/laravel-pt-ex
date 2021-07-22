<?php

namespace App\Services\EleAdmin\PutNew;

use App\Models\EleAdmin\PutNew\Reward as RewardModel;
use App\Services\Service;

class RewardService extends Service
{
    public static function init()
    {
        $params['deleted_at'] = 'is null';

        $columns = ['id', 'img', 'title', 'money', 'for_one', 'updated_at', 'created_at'];

        $rewards = RewardModel::select($columns)->ofConditions($params)->get();
        if ($rewards) {
            return $rewards;
        }

        $dateTime = date('Y-m-d H:i:s');
        $img = '';
        $title = '';
        $money = 0;
        $updatedAt = $dateTime;
        $createdAt = $dateTime;

        $forOnes = array_keys(RewardModel::$forOneList);
        foreach ($forOnes as $forOne) {
            $data['img'] = $img;
            $data['title'] = $title;
            $data['money'] = $money;
            $data['updated_at'] = $updatedAt;
            $data['created_at'] = $createdAt;
            $data['for_one'] = $forOne;

            $save[] = $data;
        }

        RewardModel::insert($save);

        $rewards = RewardModel::select($columns)->ofConditions($params)->orderBy('for_one', 'asc')->get();

        return $rewards;
    }

    public static function edit($params)
    {
        $id = $params['id'] ?? null;
        if (empty($id)) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $reward = RewardModel::find($id);
        if (empty($reward) || !in_array($reward->for_one, array_keys(RewardModel::$forOneList))) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $img = $params['img'] ?? '';
        $title = $params['title'] ?? '';
        $money = $params['money'] ?? '0.00';

        if (empty($img)) {
            return ['code' => 1000, 'message' => '图片不能为空'];
        }
        if (empty($title)) {
            return ['code' => 1000, 'message' => '标题不能为空'];
        }
        if (empty($money)) {
            return ['code' => 1000, 'message' => '现金奖励金额不能为空'];
        }

        $reward->img = $img;
        $reward->title = $title;
        $reward->money = $money;

        $result = $reward->save();
        if ($result === false) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        return ['code' => 200, 'message' => '操作成功！'];
    }
}