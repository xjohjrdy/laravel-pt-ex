<?php

namespace App\Services\EleAdmin\PutNew;

use App\Models\EleAdmin\PutNew\BackgroundConfig as BackgroundConfigModel;
use App\Services\Service;

class BackgroundConfigService extends Service
{
    public static function init()
    {
        $params['deleted_at'] = 'is null';

        $columns = ['id', 'money', 'user_all', 'first_reward', 'two_reward', 'three_reward', 'other_reward', 'show_add', 'updated_at', 'created_at'];

        $config = BackgroundConfigModel::select($columns)->ofConditions($params)->first();
        if ($config) {
            return $config;
        }

        $dateTime = date('Y-m-d H:i:s');
        $save['money'] = '0.00';
        $save['user_all'] = 0;
        $save['first_reward'] = '0';
        $save['two_reward'] = '0';
        $save['three_reward'] = '0';
        $save['other_reward'] = '0';
        $save['show_add'] = '0';
        $save['updated_at'] = $dateTime;
        $save['created_at'] = $dateTime;

        $result = BackgroundConfigModel::insertGetId($save);

        return BackgroundConfigModel::select($columns)->where('id', $result)->first();
    }

    public static function edit($params)
    {
        $id = $params['id'] ?? null;
        if (empty($id)) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $config = BackgroundConfigModel::find($id);
        if (empty($config)) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $money = $params['money'] ?? '0.00';
        $userAll = $params['user_all'] ?? 0;
        $firstReward = $params['first_reward'] ?? '0';
        $twoReward = $params['two_reward'] ?? '0';
        $threeReward = $params['three_reward'] ?? '0';
        $otherReward = $params['other_reward'] ?? '0';
        $showAdd = $params['show_add'] ?? '0';

        if (empty($money)) {
            return ['code' => 1000, 'message' => '总瓜分金额不能为空'];
        }
        if (empty($firstReward)) {
            return ['code' => 1000, 'message' => '第1名奖品图片不能为空'];
        }
        if (empty($twoReward)) {
            return ['code' => 1000, 'message' => '第2名奖品图片不能为空'];
        }
        if (empty($threeReward)) {
            return ['code' => 1000, 'message' => '第3名奖品图片不能为空'];
        }
        if (empty($otherReward)) {
            return ['code' => 1000, 'message' => '第4-50名奖品图片不能为空'];
        }
        if (empty($showAdd)) {
            return ['code' => 1000, 'message' => '海报链接配置不能为空'];
        }

        $config->money = $money;
        $config->user_all = $userAll;
        $config->first_reward = $firstReward;
        $config->two_reward = $twoReward;
        $config->three_reward = $threeReward;
        $config->other_reward = $otherReward;
        $config->show_add = $showAdd;

        $result = $config->save();
        if ($result === false) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        return ['code' => 200, 'message' => '操作成功！'];
    }
}