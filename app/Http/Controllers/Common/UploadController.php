<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\Common\OssCdn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $file = Input::file();
        if (empty($file['file'])) {
            return $this->getInfoResponse(1000, '上传失败');
        }
        if ($file['file']->isValid()) {//检验一下上传的文件是否有效.    
            $url = OssCdn::upload($file['file'], 'eleAdmin');
            if($url) {
                return $this->getResponse($url);
            }

            return $this->getInfoResponse(1000, '上传失败');
        }

        return $this->getInfoResponse(1000, '无效文件');
    }
}