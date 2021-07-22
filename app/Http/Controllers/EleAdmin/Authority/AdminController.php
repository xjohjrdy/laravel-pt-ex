<?php

namespace App\Http\Controllers\EleAdmin\Authority;

use App\Exceptions\ApiException;
use App\Http\Controllers\EleAdmin\BaseController;
use App\Services\EleAdmin\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\EleAdmin\Admin as AdminModel;
use Illuminate\Support\Facades\Validator;

class AdminController extends BaseController
{
    /**
     * 获取用户信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInfo(Request $request)
    {
        try {
            $token = $request->header('Accept-Token');
            if (Cache::has($token)) { // 用户已经登录
                $user = Cache::get($token);
                return $this->getResponse($user);
            } else {
                return $this->getInfoResponse("3001", "请先登录后操作");
            }
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取菜单列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenus(Request $request)
    {
        $user = $this->getUser($request);

        $menus = AdminService::getMenus($user->id);

        return $this->getResponse($menus);
    }

    /**
     * 获取角色列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoles(Request $request)
    {
        $user = $this->getUser($request);

        $roles = AdminService::getRoles($user->id);

        return $this->getResponse($roles);
    }

    /**
     * 管理员列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lists(Request $request)
    {
        try {
            $params = $request->all();

            $columns = ['id', 'admin_name', 'admin_password', 'real_name', 'phone', 'status', 'remark', 'logined_at', 'created_at', 'updated_at'];

            $query = AdminModel::with(['roles' => function ($query) {
                return $query->select(['id', 'role_name']);
            }])->select($columns)->ofConditions($params)->orderBy('id', 'desc');

            list($admins, $pagination) = $this->paginate($query);

            if ($admins) {
                $records = $admins->toArray();
                $records = $this->rolesHandle($records);
            } else {
                $records = [];
            }

            $data['records'] = $records;
            $data['pagination'] = $pagination;

            return $this->getResponse($data);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 角色显示处理
     * @param $records
     * @return mixed
     */
    protected function rolesHandle($records)
    {
        if ($records) {
            foreach ($records as &$record) {
                $roles = [];
                $roleIds = [];

                if ($record['roles']) {
                    foreach ($record['roles'] as &$role) {
                        $roles[] = $role['role_name'] ?? '';
                        $roleIds[] = $role['id'] ?? '';
                    }
                }

                $record['roles'] = implode(',', $roles);
                $record['role_ids'] = $roleIds;
            }
        }

        return $records;
    }

    public function add(Request $request)
    {
        try {
            $user = $this->getUser($request);

            $params = $request->all();

            $rules = [
                'admin_name' => 'required|max:50',
                'admin_password' => 'required|max:255',
                'real_name' => '',
                'phone' => '',
                'status' => 'required',
            ];

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $result = AdminService::add($params, $user->id);
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
            $user = $this->getUser($request);

            $params = $request->all();

            $rules = [
                'admin_name' => 'required|max:50',
                'real_name' => '',
                'phone' => '',
                'status' => 'required',
            ];

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $result = AdminService::edit($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 退出登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logOut(Request $request)
    {
        try {
            $token = $request->header('Accept-Token');
            if (Cache::has($token)) { // 用户已经登录
                Cache::forget($token);
                return $this->getResponse("操作成功！");
            } else {
                return $this->getInfoResponse("3001", "请先登录后操作");
            }
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }
}