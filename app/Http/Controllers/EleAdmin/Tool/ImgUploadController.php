<?php

namespace App\Http\Controllers\EleAdmin\Tool;

use App\Http\Controllers\EleAdmin\BaseController;
use App\Models\EleAdmin\Tool\ImgUpload as ImgUploadModel;
use App\Services\Common\OssCdn;
use Illuminate\Http\Request;

class ImgUploadController extends BaseController
{
    private $maxSize = 8388608;
    private $mimeType = ['jpg', 'jpeg', 'png', 'gif'];

    public function upload(Request $request)
    {
        $user = $this->getUser($request);

        $file = $request->file('file');
        $cmpType = $request->input('cmp_type', 0);

        if (empty($file)) {
            return $this->getInfoResponse(1000, '上传失败');
        }
        if ($file->isValid()) {//检验一下上传的文件是否有效
            $fileSize = $file->getSize();
            $fileMimeType = explode('.', $file->getClientOriginalName())[1] ?? '';

            if ($fileSize > $this->maxSize) {
                return $this->getInfoResponse(1000, '图片大小不可超过8M');
            }
            if (!in_array($fileMimeType, $this->mimeType)) {
                return $this->getInfoResponse(1000, '只支持' . implode('、', $this->mimeType) . '的图片格式');
            }

            $url = OssCdn::upload($file, 'eleAdmin/imgUpload');
            if($url) {
                $cmpTypeName = ImgUploadModel::$cmpTypeList[$cmpType] ?? '';
                $url .= $cmpTypeName;

                $this->log($url, $cmpType, $fileSize, $user->id);

                return $this->getResponse($url);
            }

            return $this->getInfoResponse(1000, '上传失败');
        }

        return $this->getInfoResponse(1000, '无效文件');
    }

    private function log($url, $cmpType, $size, $opId)
    {
        $save['img_url'] = $url;
        $save['cmp_type'] = $cmpType;
        $save['size'] = $size;
        $save['op_id'] = $opId;
        $save['created_at'] = time();

        ImgUploadModel::insert($save);
    }

    public function getCmpTypeTextList()
    {
        $types = ImgUploadModel::$cmpTypeTextList;

        return $this->getResponse($types);
    }

    public function logs(Request $request)
    {
        $params = $request->all();

        $query = ImgUploadModel::with(['admin' => function ($query) {
            $columns = ['id', 'admin_name'];

            return $query->select($columns);
        }])->ofConditions($params)->orderBy('id', 'desc');

        list($logs, $pagination) = $this->paginate($query);

        $records = [];
        if ($logs) {
            foreach ($logs as &$log) {
                $log->cmp_type_text = ImgUploadModel::$cmpTypeTextList[$log->cmp_type] ?? '未压缩';
                $log->size = bcdiv($log->size, 1024, 0);
                $log->op_name = $log->admin->admin_name;
                unset($log->op_id);
                unset($log->admin);
            }

            $records = $logs->toArray();
        }

        $data['records'] = $records;
        $data['pagination'] = $pagination;

        return $this->getResponse($data);
    }
}