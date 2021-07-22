<?php

namespace App\Http\Controllers\FuLu;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\FuluGoodsInfo;
use App\Entitys\App\FuluGoodsType;
use App\Exceptions\ApiException;
use App\Services\FuLu\FuLuServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FuLuController extends Controller
{
    /*
     * 获取商品列表
     */
    public function getGoodsList(Request $request, FuLuServices $fuLuServices, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            $json_goods = $fuLuServices->getGoodsList();
            $arr_goods = json_decode($json_goods, true);
            if ($arr_goods['code'] != 0) {
                return $this->getInfoResponse('1002', '商品列表拉取失败:' . $arr_goods['message']);
            }
            $result = json_decode($arr_goods['result'], true);
            foreach ($result as &$v) {
                $v['purchase_price'] = (string)$v['purchase_price'];
            }
            return $this->getResponse($result);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到商品信息详情
     */
    public function getGoodsInfo(Request $request, FuLuServices $fuLuServices, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
                'product_id' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $product_id = $arrRequest['product_id'];
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            $json_goods = $fuLuServices->getGoodsInfo($product_id);
            $arr_goods = json_decode($json_goods, true);
            if ($arr_goods['code'] != 0) {
                return $this->getInfoResponse('1002', '商品信息拉取失败:' . $arr_goods['message']);
            }
            $result = json_decode($arr_goods['result'], true);
            $result['face_value'] = (string)$result['face_value'];
            $result['purchase_price'] = (string)$result['purchase_price'];

            return $this->getResponse($result);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到商品模板信息
     */
    public function getGoodsTemplate(Request $request, FuLuServices $fuLuServices, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
                'template_id' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $template_id = $arrRequest['template_id'];
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            $json_goods = $fuLuServices->getGoodsTemplate($template_id);
            $arr_goods = json_decode($json_goods, true);
            if ($arr_goods['code'] != 0) {
                return $this->getInfoResponse('1002', '商品模板信息拉取失败:' . $arr_goods['message']);
            }
            $result = json_decode($arr_goods['result'], true);

            return $this->getResponse($result);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
    * 获取商品一级分类
    */
    public function getGoodsOneClassify(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //得到全部一级分类
            $fuluGoodsInfo = new FuluGoodsInfo();
            $obj_one_type = $fuluGoodsInfo->pluck('one_type');
            $arr_one_type = $obj_one_type->toArray();
            $only_arr_one_type = array_unique($arr_one_type);

            $fuluGoodsType = new FuluGoodsType();
            $type_name = $fuluGoodsType->whereIn('type_id', $only_arr_one_type)->orderBy('type_soft')->get(['type_id', 'type_name', 'img']);

            return $this->getResponse($type_name);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 获取商品二级分类
     */
    public function getGoodsTwoClassify(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
                'type' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type'];
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //得到全部二级分类
            $fuluGoodsInfo = new FuluGoodsInfo();
            $obj_one_type = $fuluGoodsInfo->where('one_type', $type)->pluck('two_type');
            $arr_one_type = $obj_one_type->toArray();
            $only_arr_one_type = array_unique($arr_one_type);

            $fuluGoodsType = new FuluGoodsType();
            $type_name = $fuluGoodsType->whereIn('type_id', $only_arr_one_type)->orderBy('type_soft')->get(['type_id', 'type_name', 'img']);

            //得到二级分类金额
            foreach ($type_name as &$v) {
                $v->purchase_price = $fuluGoodsInfo->where('two_type', $v->type_id)->min('purchase_price');
            }
            
            return $this->getResponse($type_name);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 获取商品三级
     */
    public function getGoodsThreeClassify(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
                'type' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type'];
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //得到全部三级分类
            $fuluGoodsInfo = new FuluGoodsInfo();
            $obj_one_type = $fuluGoodsInfo->where('two_type', $type)->get();

            return $this->getResponse($obj_one_type);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

}
