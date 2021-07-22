<?php

namespace App\Http\Controllers\Index;

use App\Entitys\Xin\Config;
use App\Exceptions\ApiException;
use App\Services\Common\HomeConfigService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index(Request $request)
    {

        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];
            //取IOS需要隐藏版本号
            $obj_config = new Config();
            $ios_hide_version = $obj_config->getHideConfigValue('ios_hide_version');

            //取客户端类型
            $request_device = $request->header('Accept-Device'); //设备类型  ios 或 android
            $request_appversion = $request->header('Accept-Appversion'); //版本号

            $obj_home = new HomeConfigService($request_device, $request_appversion, $ios_hide_version);

//            $home_data = $obj_home->getHomeConfigData();
            $home_data = $obj_home->getHomeConfigCache(); //读取发布的缓存数据


            $category_list = &$home_data['category_list']; // 导航栏集合
            $banners = &$home_data['banners']; // banners 广告栏


            if ($request_device != 'ios' && $request_device != 'android') {
                throw new ApiException('设备信息错误！', 3002);
            }

            foreach ($category_list as $key => &$item) {

                if ($request_device == 'ios') {
                    // ios 版本审核需要隐藏爆款
                    if ($item['id'] == 4 && $request_appversion == $ios_hide_version) {
                        unset($category_list[$key]);
                        continue;
                    }
                }

                $sub_categorys = &$item['sub_categorys'];

//                dd($item);

                foreach ($sub_categorys as $key_sub => &$sub_category) {
                    if (!$obj_home->collateItem($sub_category)) {
                        unset($sub_categorys[$key_sub]);
                    }
                }

                $sub_categorys = array_merge($sub_categorys);

                $sub_categorys = array_pad($sub_categorys, 10, new \stdClass());
//            $item = [];
            }

            $category_list = array_merge($category_list);

            foreach ($banners as $key => &$banner) {
                if (!$obj_home->collateItem($banner)) {
                    unset($banner[$key]);
                }
            }
            $banners = array_merge($banners);

            $home_data['shop_img'] = 'https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/webImages/iconShop.gif';

            $resq = [
                'code' => 200,
                'msg' => '请求成功',
                'data' => $home_data
            ];

            $data_sign = md5(json_encode($resq) . $app_id . $request_appversion);

            return response($resq)->header('X-Header-Sign', $data_sign);

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
