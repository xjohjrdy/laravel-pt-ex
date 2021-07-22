<?php

namespace App\Http\Controllers\EleAdmin\AppConfig;

use App\Entitys\App\HomeTopBanner;
use App\Entitys\App\HomeTopCategory;
use App\Entitys\App\HomeTopCategoryChild;
use App\Entitys\App\WechatAssistantAudit;
use App\Services\Common\HomeConfigService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    //
    public function getCategoryList(Request $request)
    {
        try {
            $model = new HomeTopCategory();
            $list = $model->orderBy('sort', 'desc')->get();
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    /**
     * 更新类别
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCategory(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'id' => 'required',
                'sort' => 'required',
                'title' => 'required',
            ];
            $model = new HomeTopCategory();
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $audit_info = $model->where(['id' => $params['id']])->first();
            if(empty($audit_info)){
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $audit_info->update([
                    'sort' => $params['sort'],
                    'title' => $params['title'],
                    'desc' => @$params['desc'],
                ]);
            }
            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    /**
     * 删除icon
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delIcon(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'id' => 'required',
            ];
            $model = new HomeTopCategoryChild();
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $audit_info = $model->where(['id' => $params['id']])->first();
            if(empty($audit_info)){
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $model->where(['id' => $params['id']])->delete();
            }
            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /**
     * 删除banner
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delBanner(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'id' => 'required',
            ];
            $model = new HomeTopBanner();
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $audit_info = $model->where(['id' => $params['id']])->first();
            if(empty($audit_info)){
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $model->where(['id' => $params['id']])->delete();
            }
            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }
    public function getBannerList(Request $request)
    {
        try {
            $model = new HomeTopBanner();
            $list = $model->orderBy('sort', 'desc')->get();
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    public function getSubCategoryList(Request $request)
    {
        try {
            $params = $request->input();
//            $limit = $params['limit'];
            $search_keys = ['category_id'];
            $wheres = [];
            unset($params['s']);
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$item] = $params[$item];
                }
            }
            $model = new HomeTopCategoryChild();
            $model = $model->where($wheres);
            $list = $model->orderBy('sort', 'desc')->get();
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    /**
     * 新增或更新栏目ICON
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operateSubCategory(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'sort' => 'required',
                'category_id' => 'required',
                'icon' => 'required',
                'icon_type' => 'required',
                'text' => 'required',
                'redirect_type' => 'required',
                'show_flag' => 'required',
                'min_ios_version' => 'required',
                'min_android_version' => 'required|integer',
            ];
            unset($params['s']);
            $model = new HomeTopCategoryChild();
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            if(empty($params['id'])){
                $model->create($params);
            } else {
                $audit_info = $model->where(['id' => $params['id']])->first();
                if(empty($audit_info)){
                    return $this->getInfoResponse(2000, '为查找到该记录');
                } else {
                    $model->where(['id' => $params['id']])->update($params);
                }
            }

            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /**
     * 新增或更新Banner
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operateBanners(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'sort' => 'required',
                'title' => 'required',
                'image_url' => 'required',
                'redirect_type' => 'required',
                'show_flag' => 'required',
                'min_ios_version' => 'required|integer',
                'min_android_version' => 'required|integer',
            ];
            unset($params['s']);
            $model = new HomeTopBanner();
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            if(empty($params['id'])){
                $model->create($params);
            } else {
                $audit_info = $model->where(['id' => $params['id']])->first();
                if(empty($audit_info)){
                    return $this->getInfoResponse(2000, '为查找到该记录');
                } else {
                    $model->where(['id' => $params['id']])->update($params);
                }
            }
            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }


    /**
     * 发布更新缓存
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pushConfig(Request $request)
    {
        try {
            $homeService = new HomeConfigService();
            return $this->getResponse($homeService->getHomeConfigCache());
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }
}
