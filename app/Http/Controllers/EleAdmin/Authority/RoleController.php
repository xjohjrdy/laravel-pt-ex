<?php

namespace App\Http\Controllers\EleAdmin\Authority;

use App\Exceptions\ApiException;
use App\Http\Controllers\EleAdmin\BaseController;
use App\Models\EleAdmin\Menu as MenuModel;
use App\Models\EleAdmin\Role as RoleModel;
use App\Models\EleAdmin\RoleMenu as RoleMenuModel;
use App\Services\EleAdmin\RoleService;
use App\Tools\ObjectDataHandle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends BaseController
{
    /**
     * 获取角色信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lists(Request $request)
    {
        try {
            $params = $request->all();

            $columns = ['id', 'role_name', 'desc', 'status', 'created_at', 'updated_at'];

            $query = RoleModel::with(['menus' => function ($query) {
                return $query->select(['id', 'title']);
            }])->select($columns)->ofConditions($params)->orderBy('id', 'desc');

            list($roles, $pagination) = $this->paginate($query);

            if ($roles) {
                $records = $roles->toArray();
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

    public function add(Request $request)
    {
        try {
            $user = $this->getUser($request);
            $params = $request->all();

            $rules = [
                'role_name' => 'required|max:50',
                'status' => 'required',
            ];

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $result = RoleService::add($params, $user->id);
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

            $rules = [
                'role_name' => 'required|max:50',
                'status' => 'required',
            ];

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $result = RoleService::edit($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function getRoles(Request $request)
    {
        try {
            $params['status'] = RoleModel::ACCOUNT_STATUS_DISPLAY;

            $columns = ['id', 'role_name'];

            $query = RoleModel::select($columns)->ofConditions($params)->where('id', '!=', 999)->get();
            $roles = ObjectDataHandle::handle($query);

            return $this->getResponse($roles);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取菜单ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuIds(Request $request)
    {
        try {
            $roleId = $request->input('role_id', null);
            if ($roleId) {
                $params['role_id'] = $roleId;
                $params['status'] = RoleMenuModel::ACCOUNT_STATUS_EFFECTIVE;
                $menus = RoleMenuModel::ofConditions($params)->pluck('menu_id');

                return $this->getResponse($menus);
            }

            return $this->getInfoResponse('1000', '参数异常');
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function setMenus(Request $request)
    {
        try {
            $roleId = $request->input('role_id', null);
            $menuIds = $request->input('menu_ids', null);

            if (!$roleId) {
                return $this->getInfoResponse('1000', '参数异常');
            }
//            if (!$menuIds) {
//                return $this->getInfoResponse('1000', '未选择菜单');
//            }

//            $menus = MenuModel::whereIn('id', $menuIds)->pluck('id');
//            if (count($menuIds) != count($menus)) {
//                return $this->getInfoResponse('1000', '菜单异常');
//            }

            $update['status'] = RoleMenuModel::ACCOUNT_STATUS_FAILURE;
            $params['role_id'] = $roleId;
//            $params['status'] = RoleMenuModel::ACCOUNT_STATUS_EFFECTIVE;

            $result = RoleMenuModel::ofConditions($params)->delete();
            if ($result === false) {
                return $this->getInfoResponse('1000', '网络异常');
            }

            $save = [];
            foreach ($menuIds as $menuId) {
                $save[] = [
                    'menu_id' => $menuId,
                    'role_id' => $roleId,
                ];
            }

            $result = RoleMenuModel::insert($save);
            if ($result === false) {
                return $this->getInfoResponse('1000', '网络异常');
            }

            return $this->getResponse('操作成功！');
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }
}