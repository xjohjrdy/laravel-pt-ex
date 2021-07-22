<?php

namespace App\Http\Controllers\EleAdmin\PutNew;

use App\Exceptions\ApiException;
use App\Http\Controllers\EleAdmin\BaseController;
use App\Models\EleAdmin\PutNew\Faker as FakerModel;
use App\Services\EleAdmin\PutNew\FakerService;
use App\Tools\ObjectDataHandle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FakerController extends BaseController
{
    public function lists(Request $request)
    {
        try {
            $params = $request->all();

            $columns = ['id', 'phone', 'user_name', 'updated_at', 'created_at'];

            $query = FakerModel::select($columns)->ofConditions($params)->orderBy('updated_at', 'desc');

            list($fakers, $pagination) = $this->paginate($query);

            $records = ObjectDataHandle::handle($fakers);

            $data['records'] = $records;
            $data['pagination'] = $pagination;

            return $this->getResponse($data);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function add(Request $request)
    {
        try {
            $params = $request->all();

            $result = $this->operate($params, 'add');
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            $params = $request->all();

            $result = $this->operate($params, 'edit');
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    protected function operate($params, $type)
    {
        $rules = [
            'phone' => 'required|max:150',
            'user_name' => 'required|max:150',
        ];

        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        switch ($type) {
            case 'add':
                $result = FakerService::add($params);
                break;
            case 'edit':
                $result = FakerService::edit($params);
                break;
            default:
                $result = ['code' => 1000, 'message' => '操作异常'];
                break;
        }

        return $result;
    }
}