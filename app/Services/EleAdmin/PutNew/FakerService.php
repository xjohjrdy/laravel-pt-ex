<?php

namespace App\Services\EleAdmin\PutNew;

use App\Services\Service;
use App\Models\EleAdmin\PutNew\Faker as FakerModel;

class FakerService extends Service
{
    /**
     * 新增
     * @param $params
     * @return array
     */
    public static function add($params)
    {
        $phone = $params['phone'] ?? null;
        $userName = $params['user_name'] ?? null;

        if (!$phone) {
            return ['code' => 1000, 'message' => '用户手机号不能为空'];
        }
        if (mb_strlen($phone) != 11 || !preg_match("/^1[34578]\d{9}$/", $params['phone'])) {
            return ['code' => 1000, 'message' => '手机号格式错误'];
        }
        if (!$userName) {
            return ['code' => 1000, 'message' => '用户昵称不能为空'];
        }

        $userNameLen = mb_strlen($userName);
        if (mb_strlen($userName) == 11 && preg_match("/^1[34578]\d{9}$/", $userName)) {
            $userName = substr_replace($userName, '****', 3, 4);
        } elseif ($userNameLen > 2) {
            $userNameMid = bcdiv($userNameLen, 2, 0);
            $userNameHidden = ($userNameMid * 2 == $userNameLen) ? '**' : '*';
            $userNameFrontLen = ($userNameMid * 2 == $userNameLen) ? $userNameMid - 1 : $userNameMid;
            $userNameBackStart = $userNameFrontLen + mb_strlen($userNameHidden);
            $userName = mb_substr($userName, 0, $userNameFrontLen) . $userNameHidden . mb_substr($userName, $userNameBackStart);
        }

        $dateTime = date('Y-m-d H:i:s');
        $save['phone'] = $phone = substr_replace($phone, '****', 3, 4);
        $save['created_at'] = $dateTime;
        $save['updated_at'] = $dateTime;
        $save['user_name'] = $userName;

        $id = FakerModel::insertGetId($save);
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

        if (!$id) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $faker = FakerModel::find($id);
        if (empty($faker)) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $phone = $params['phone'] ?? null;
        $userName = $params['user_name'] ?? null;

        if (!$phone) {
            return ['code' => 1000, 'message' => '用户手机号不能为空'];
        }
        if (mb_strlen($phone) != 11 || !(preg_match("/^1[345789]\d(\*{4})\d{4}$/", $phone) || preg_match("/^1[345789]\d{9}$/", $phone))) {
            return ['code' => 1000, 'message' => '手机号格式错误'];
        }
        if (!$userName) {
            return ['code' => 1000, 'message' => '用户昵称不能为空'];
        }

        $userNameLen = mb_strlen($userName);
        if (mb_strlen($userName) == 11 && (preg_match("/^1[345789]\d(\*{4})\d{4}$/", $userName) || preg_match("/^1[345789]\d{9}$/", $userName))) {
            $userName = substr_replace($userName, '****', 3, 4);
        } elseif ($userNameLen > 2) {
            $userNameMid = bcdiv($userNameLen, 2, 0);
            $userNameHidden = ($userNameMid * 2 == $userNameLen) ? '**' : '*';
            $userNameFrontLen = ($userNameMid * 2 == $userNameLen) ? $userNameMid - 1 : $userNameMid;
            $userNameBackStart = $userNameFrontLen + mb_strlen($userNameHidden);
            $userName = mb_substr($userName, 0, $userNameFrontLen) . $userNameHidden . mb_substr($userName, $userNameBackStart);
        }

        $dateTime = date('Y-m-d H:i:s');
        $faker->phone = $phone = substr_replace($phone, '****', 3, 4);;
        $faker->user_name = $userName;
        $faker->updated_at = $dateTime;

        $result = $faker->save();
        if ($result ===false) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        return ['code' => 200, 'message' => '操作成功！'];
    }
}