<?php

namespace App\Http\Controllers\EleAdmin\PutNew;

use App\Exceptions\ApiException;
use App\Http\Controllers\EleAdmin\BaseController;
use App\Models\EleAdmin\PutNew\BackgroundConfig as BackgroundConfigModel;
use App\Services\EleAdmin\PutNew\BackgroundConfigService;
use App\Tools\ImageUrlFileHandle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BackgroundConfigController extends BaseController
{
    public function get(Request $request)
    {
        try {
            $params['deleted_at'] = 'is null';

            $columns = ['id', 'money', 'user_all', 'first_reward', 'two_reward', 'three_reward', 'other_reward', 'show_add', 'updated_at', 'created_at'];

            $config = BackgroundConfigModel::select($columns)->ofConditions($params)->first();
            if (empty($config)) {
                $config = BackgroundConfigService::init();
            }

            $records = [];
            if ($config) {
                if ($config->first_reward) {
                    $config->first_reward_info = ImageUrlFileHandle::setImgUrlData($config->first_reward, '第1名');
                }
                if ($config->two_reward) {
                    $config->two_reward_info = ImageUrlFileHandle::setImgUrlData($config->two_reward, '第2名');
                }
                if ($config->three_reward) {
                    $config->three_reward_info = ImageUrlFileHandle::setImgUrlData($config->three_reward, '第3名');
                }
                if ($config->other_reward) {
                    $config->other_reward_info = ImageUrlFileHandle::setImgUrlData($config->other_reward, '第4-50名');
                }

                $records = $config->toArray();
            }

            return $this->getResponse($records);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            $params = $request->all();

            $rules = [
                'money' => 'required|max:20',
                'user_all' => 'required|max:11',
                'show_add' => 'required|max:255',
                'first_reward' => 'required|max:255',
                'two_reward' => 'required|max:255',
                'three_reward' => 'required|max:255',
                'other_reward' => 'required|max:255',
            ];

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $result = BackgroundConfigService::edit($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }
}