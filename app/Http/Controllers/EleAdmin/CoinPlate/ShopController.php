<?php


namespace App\Http\Controllers\EleAdmin\CoinPlate;


use App\Entitys\App\CoinMenu;
use App\Entitys\App\CoinShopGoods;
use App\Http\Controllers\Controller;
use App\Services\Common\OssCdn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new CoinShopGoods();
    }

    //
    public function getList(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['status', 'title', 'type'];
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$item] = $params[$item];
                }
            }

            $list = $this->model->where($wheres)->orderBy('weight', 'desc')->paginate($limit);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    /**
     * 删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $audit_info = $this->model->where(['id' => $params['id']])->first();
            if (empty($audit_info)) {
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $this->model->where(['id' => $params['id']])->delete();
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
     * 新增或更新栏目ICON
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operate(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'title' => 'required',
                'price' => 'required',
                'coin' => 'required',
                'normal_price' => 'required',
                'express' => 'required',
                'real_weight' => 'required',
                'area' => 'required',
                'custom' => 'required',
                'status' => 'required',
                'type' => 'required',
            ];
            unset($params['s'], $params['area2'], $params['custom2'], $params['created_at'], $params['deleted_at'], $params['updated_at']);
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $file_list = Input::file();
            if(!empty($file_list['little_file'])){
                if ($file_list['little_file']->isValid()) {//检验一下上传的文件是否有效.    
                    $little_file = OssCdn::upload($file_list['little_file'], 'coin/little');
                    if($little_file != false){
                        $params['little_img'] = $little_file;
                    }
                }
                unset($file_list['little_file']);
            }

            $add_images = [];
            foreach ($file_list as $file) {
                if ($file->isValid()) {//检验一下上传的文件是否有效.    
                    $url = OssCdn::upload($file, 'coin/goods');
                    if($url != false){
                        $add_images[] = $url;
                    }
                }
            }
            if (empty($params['id'])) {
                if(count($add_images) > 0){
                    $params['header_img'] = implode(',' , $add_images);
                }
                $this->model->create($params);
            } else {
                $audit_info = $this->model->where(['id' => $params['id']])->first();
                if (empty($audit_info)) {
                    return $this->getInfoResponse(2000, '为查找到该记录');
                } else {
                    if(count($add_images) > 0){
                        $params['header_img'] = $params['header_img'] . ',' . implode(',' , $add_images);
                    }
                    $this->model->where(['id' => $params['id']])->update($params);
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

    public function getById(Request $request)
    {
        try {
            $params = $request->input();
            $id = $params['id'];
            $goods = $this->model->where(['id' => $id])->first();
            return $this->getResponse($goods);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    public function upload(Request $request)
    {
        try {
            $params = $request->input();
            $file_list = Input::file();
            $add_images = [];
            unset($params['s']);
            foreach ($file_list as $file) {
                if ($file->isValid()) {//检验一下上传的文件是否有效.    
                    $url = OssCdn::upload($file, 'coin/goods');
                    if($url != false){
                        $add_images[] = $url;
                    }
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
}