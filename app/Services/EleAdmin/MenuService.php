<?php

namespace App\Services\EleAdmin;

use App\Services\Service;
use App\Models\EleAdmin\Menu as MenuModel;
use App\Models\EleAdmin\RoleMenu as RoleMenuModel;
use App\Tools\ObjectDataHandle;

class MenuService extends Service
{
    /**
     * 新增
     * @param $params
     * @return array
     */
    public static function add($params, $opId = 0)
    {
        $title = $params['title'] ?? null;
        $name = $params['name'] ?? null;
        $desc = $params['desc'] ?? '';
        $path = $params['path'] ?? null;
        $component = $params['component'] ?? '';
        $redirect = $params['redirect'] ?? '';
        $parentId = $params['parent_id'] ?? null;
        $icon = $params['icon'] ?? '';
        $status = $params['status'] ?? null;

        if (!$title) {
            return ['code' => 1000, 'message' => '菜单名称不能为空'];
        }
        if (!$path) {
            return ['code' => 1000, 'message' => '菜单路由不能为空'];
        }
        if (!$status || !in_array($status, array_keys(MenuModel::$statusList))) {
            return ['code' => 1000, 'message' => '状态异常'];
        }
        if ($parentId > 0) {
            $level = self::getParentLevel($parentId) + 1;
        } else {
            $level = 1;
        }

        $time = time();
        $save['title'] = $title;
        $save['name'] = $name;
        $save['desc'] = $desc;
        $save['path'] = $path;
        $save['component'] = $component;
        $save['redirect'] = $redirect;
        $save['parent_id'] = $parentId;
        $save['level'] = $level;
        $save['icon'] = $icon;
        $save['status'] = $status;
        $save['op_id'] = $opId;
        $save['created_at'] = $time;
        $save['updated_at'] = $time;

        $id = MenuModel::insertGetId($save);
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
        $title = $params['title'] ?? null;
        $name = $params['name'] ?? null;
        $desc = $params['desc'] ?? '';
        $path = $params['path'] ?? null;
        $component = $params['component'] ?? '';
        $redirect = $params['redirect'] ?? '';
        $parentId = $params['parent_id'] ?? null;
        $icon = $params['icon'] ?? '';
        $status = $params['status'] ?? null;

        if (!$id) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $menu = MenuModel::find($id);
        if (empty($menu)) {
            return ['code' => 1000, 'message' => '查询不到菜单信息'];
        }

        if (!$title) {
            return ['code' => 1000, 'message' => '菜单名称不能为空'];
        }
        if (!$path) {
            return ['code' => 1000, 'message' => '菜单路由不能为空'];
        }
        if (!$status || !in_array($status, array_keys(MenuModel::$statusList))) {
            return ['code' => 1000, 'message' => '状态异常'];
        }

        $menu->title = $title;
        $menu->name = $name;
        $menu->desc = $desc;
        $menu->path = $path;
        $menu->component = $component;
        $menu->redirect = $redirect;
        $menu->parent_id = $parentId;
        $menu->icon = $icon;
        $menu->status = $status;
        $menu->updated_at = time();

        $result = $menu->save();
        if ($result ===false) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        return ['code' => 200, 'message' => '操作成功！'];
    }

    public static function getMenuTree($roles = [], $all = true)
    {
        $params = [];
        $ids = [];

        if ($roles) {
            $menus = self::getMenusByRoles($roles);
            if (empty($menus)) {
                return null;
            }

            $params['ids'] = $menus;
            $ids = $menus;
        }

        $menus = self::getMenuTreeByParams($params, $all);
        if ($ids) {
            $menus = self::menuTreeHandle($menus, $ids);
        }

        return $menus;
    }

    public static function getMenus()
    {
        $columns = ['id', 'title', 'parent_id'];

        $menus = MenuModel::with(['parent' => function ($query) {
            return $query->select(['id', 'title']);
        }])->select($columns)->get();

        return $menus;
    }

    public static function getMenusByRoles($roles = [])
    {
        $params['role_ids'] = $roles;
        $params['status'] = RoleMenuModel::ACCOUNT_STATUS_EFFECTIVE;

        $query = RoleMenuModel::ofConditions($params)->pluck('menu_id');
        $menus = ObjectDataHandle::handle($query);

        $params = [];
        $params['ids'] = $menus;
        $params['status'] = MenuModel::ACCOUNT_STATUS_DISPLAY;

        $query = MenuModel::ofConditions($params)->pluck('id');
        $menus = ObjectDataHandle::handle($query);

        return $menus;
    }

    public static function getMenuIds($roleIds = [])
    {
        $params = [];

        if ($roleIds) {
            $params['role_ids'] = $roleIds;
            $menuIds = RoleMenuModel::ofConditions($params)->pluck('menu_id');
        } else {
            $menuIds = MenuModel::pluck('id');
        }

        return $menuIds;
    }

    protected static function getMenuTreeByParams($params = [], $all = true)
    {
        $columns = ['id', 'title', 'name', 'path', 'component', 'redirect', 'parent_id', 'icon'];

        $params['status'] = MenuModel::ACCOUNT_STATUS_DISPLAY;
        $params['level'] = 1;

        $model = new MenuModel();

        if (!$all) {
            $model->childrenMap = ['status' => MenuModel::ACCOUNT_STATUS_DISPLAY];
        }

        $query = $model->with('allChildren')->select($columns)
            ->ofConditions($params)
            ->get();
        $menus = ObjectDataHandle::handle($query);

        return $menus;
    }

    protected static function menuTreeHandle($menus = [], $ids = [])
    {
        if (empty($menus)) {
            return [];
        }

        foreach ($menus as $key => $menu) {
            $menus[$key]['children'] = $menu['all_children'];
            unset($menus[$key]['all_children']);

            $menus[$key]['meta'] = [
                'title' => $menus[$key]['title'],
                'icon' => $menus[$key]['icon'],
            ];
            unset($menus[$key]['title']);
            unset($menus[$key]['icon']);
            unset($menus[$key]['parent_id']);

            if (!in_array($menu['id'], $ids)) {
                unset($menus[$key]);
            } else {
                if ($menu['all_children']) {
                    $menus[$key]['children'] = self::menusHandle($menu['all_children'], $ids);
                }
            }
        }

        return $menus;
    }

    public static function getParentLevel($parentId)
    {
        if ($parentId > 0) {
            $menu = MenuModel::find($parentId);
            if ($menu) {
                return $menu->level;
            }

            return 1;
        }

        return 1;
    }
}