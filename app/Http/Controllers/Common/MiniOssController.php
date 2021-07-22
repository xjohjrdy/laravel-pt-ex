<?php

namespace App\Http\Controllers\Common;

use App\Exceptions\ApiException;
use App\Services\Common\OssCdn;
use App\Services\Tools\WebApiRsa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MiniOssController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request_uri = $request->getRequestUri();
            $request_timestamp = $request->header('Accept-Timestamp');
            $request_token = $request->header('Accept-Token');
            $request_content = $request->header('Accept-Sign');

            $tool_rsa = new WebApiRsa();

            if (abs(time() - $request_timestamp) > 60) {
                return response($tool_rsa->response_encrypt([
                    'code' => 525,
                    'msg' => '接口请求超时！！！',
                    'time' => time(),
                ]));
            }

            $this_token = hash('sha256', $request_uri . $request_timestamp . $request_content);

            if (strcasecmp($request_token, $this_token)) {
                return response([
                    'code' => 500,
                    'msg' => '异常操作！！',
                    'time' => time(),
                ]);
            }
            $request->data = $tool_rsa->private_decrypt($request_content);

            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'size' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $file_img = $request->file('img');
            $load_size = $arrRequest['size'];

            $size = $file_img->getSize();

            if ($load_size != $size) {
                return $this->getInfoResponse('3005', '上传错误！');
            }


            $img_url = OssCdn::upload($file_img, 'mini');
            if (empty($img_url)) {
                return $this->getInfoResponse('3002', '上传错误！');
            }

            return $this->getResponse($img_url);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }
}
