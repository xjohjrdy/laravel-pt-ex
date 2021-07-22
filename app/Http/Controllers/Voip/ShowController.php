<?php

namespace App\Http\Controllers\Voip;

use App\Entitys\App\VoipGpsInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ShowController extends Controller
{
    /**
     * 头图
     */
    public function getVoipIndexShow()
    {
        $img = 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20191125203957.png';
        return $this->getResponse($img);
    }

    /*
     * 通讯是否开启gps 显示文案
     */
    public function voipIsGpsWord()
    {
        return $this->getResponse('根据通讯相关规定，拨打电话需打开定位GPS');
    }

    /*
     * 通讯存入gps信息
     */
    public function voipSaveGpsInfo(Request $request, VoipGpsInfo $voipGpsInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',  //必须整数
                'gps' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $gps = $arrRequest['gps'];

            /***********************************/
            //开始处理逻辑问题
            $res = $voipGpsInfo->create(['app_id' => $app_id, 'gps' => $gps]);
            if (empty($res)){
                return $this->getInfoResponse('1001', '请求失败!');//错误返回数据
            }
            return $this->getResponse('请求成功!');//正常返回数据

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
