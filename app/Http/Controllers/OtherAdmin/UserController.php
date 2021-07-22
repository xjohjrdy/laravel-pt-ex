<?php

namespace App\Http\Controllers\OtherAdmin;

use App\Entitys\Other\AdminUser;
use App\Entitys\Other\ThreeUserGet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['phone'];
            $wheres = [];
            foreach ($search_keys as $key=>$item){
                if(!is_null($params[$item])){
                    $wheres[$item] = $params[$item];
                }
            }
            $model = new AdminUser();
            $list = $model->where($wheres)->paginate($limit);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage() );
        }

    }

    public function add(Request $request)
    {
        try {
            $params = $request->input();
            $data = $params['data'];
            $model = new AdminUser();
            $model->create($data);
            return $this->getResponse("添加成功！");
        } catch (\Exception $e) {
            dd( $e);
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage() );
        }
    }

    public function edit(Request $request)
    {
        try {
            $params = $request->input();
            $data = $params['data'];
            $model = new AdminUser();
            $model->where(['id' => $data['id']])->update($data);
            return $this->getResponse("更新成功！");
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage() );
        }
    }
}
