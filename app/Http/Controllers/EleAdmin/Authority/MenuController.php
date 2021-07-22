<?php

namespace App\Http\Controllers\EleAdmin\Authority;

use App\Http\Controllers\EleAdmin\BaseController;
use App\Http\Requests\EleAdmin\StoreMenuPost;
use App\Models\EleAdmin\Menu as MenuModel;
use App\Services\EleAdmin\MenuService;
use Illuminate\Http\Request;

class MenuController extends BaseController
{
    /**
     * 获取菜单信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lists(Request $request)
    {
        try {
            $params = $request->all();

            if (empty($params['parent_id'])) {
                $params['level'] = 1;
            }

            $columns = ['id', 'title', 'name', 'desc', 'path', 'parent_id', 'level', 'icon', 'status', 'created_at', 'updated_at'];

            $query = MenuModel::select($columns)->ofConditions($params)->orderBy('id', 'desc');

            list($menus, $pagination) = $this->paginate($query);

            if ($menus) {
                $records = $menus->toArray();
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

    public function add(Request $request, StoreMenuPost $menuPost)
    {
        try {
            $user = $this->getUser($request);

            $params = $request->all();

            $result = MenuService::add($params, $user->id);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function edit(Request $request, StoreMenuPost $menuPost)
    {
        try {
            $params = $request->all();

            $result = MenuService::edit($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取菜单树
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuTree(Request $request)
    {
        try {
            $menus = MenuService::getMenuTree();

            return $this->getResponse($menus);
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
        try {
            $menus = MenuService::getMenus();

            $records = [];
            if ($menus) {
                foreach ($menus as &$menu) {
                    $record['id'] = $menu->id;
                    $record['title'] = $menu->title;
                    if (!empty($menu->parent)) {
                        $record['title'] .= '(上级菜单：' . $menu->parent->title . ')';
                    }

                    $records[] = $record;
                }
            }

            return $this->getResponse($records);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }
}