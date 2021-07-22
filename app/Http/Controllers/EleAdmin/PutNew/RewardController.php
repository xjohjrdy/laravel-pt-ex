<?php

namespace App\Http\Controllers\EleAdmin\PutNew;

use App\Exceptions\ApiException;
use App\Http\Controllers\EleAdmin\BaseController;
use App\Models\EleAdmin\PutNew\Reward as RewardModel;
use App\Services\EleAdmin\PutNew\RewardService;
use App\Tools\ImageUrlFileHandle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RewardController extends BaseController
{
    private $count = 4;

    public function lists(Request $request)
    {
        try {
            $params['deleted_at'] = 'is null';

            $columns = ['id', 'img', 'title', 'money', 'for_one', 'updated_at', 'created_at'];

            $rewards = RewardModel::select($columns)->orderBy('for_one', 'asc')->get();
            if (empty($rewards)) {
                $rewards = RewardService::init();
            }
            if (count($rewards) != $this->count) {
                return $this->getInfoResponse(1000, '数据异常！请联系管理员');
            }

            foreach ($rewards as &$reward) {
                $reward->for_one_text = RewardModel::$forOneList[$reward->for_one] ?? '未知';
                if ($reward->img) {
                    $reward->img_info = ImageUrlFileHandle::setImgUrlData($reward->img, $reward->for_one_text);
                } else {
                    $reward->img_info = '';
                }
            }

            $records = $rewards->toArray();

            $data['records'] = $records;
            $data['pagination'] = [
                'page' => 1,
                'count' => $this->count,
                'page_count' => 1,
                'limit' => $request->input('limit', 10),
            ];

            return $this->getResponse($data);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            $params = $request->all();

            $rules = [
                'img' => 'required|max:150',
                'title' => 'required|max:150',
                'money' => 'required|max:20',
            ];

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $result = RewardService::edit($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }
}