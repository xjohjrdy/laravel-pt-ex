<?php

namespace App\Http\Controllers\Common;

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Core\OssUtil;

class Oss
{
    protected static function init()
    {
        ini_set("memory_limit", "1024M");

        set_time_limit(0);

//        $accessKeyId = "sH3QuftroyHhKtst";
//        $accessKeySecret = 'mIrpOxeCBN3o2AbJoCWY7UdAB13sSk';
        //大权限
        $accessKeyId = "LTAI4Fzg25DR5i3vVReYWQJH";
        $accessKeySecret = 'gIFsHl6wa5F9AT1l3ioEv9nmjaS7Ls';

        $endpoint = 'oss-cn-zhangjiakou.aliyuncs.com//';
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        } catch (OssException $e) {
            print $e->getMessage();
            return '';
        }
        return $ossClient;
    }

    protected static function getSignedUrlForGettingObject($ossClient, $bucket, $object, $timeout)
    {
        ini_set("memory_limit", "1024M");

        set_time_limit(0);
        try {
            $signedUrl = $ossClient->signUrl($bucket, $object, $timeout);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return '';
        }
        return $signedUrl;

    }

    public static function getSignedUrl($object = '', $timeout = '300')
    {
        ini_set("memory_limit", "1024M");

        set_time_limit(0);
        $bucket = "putaoliulanqi1";
        $ossClient = self::init();
        return self::getSignedUrlForGettingObject($ossClient, $bucket, $object, $timeout);
    }

    protected static function uploadFile($ossClient, $bucket, $object, $filePath)
    {
        ini_set("memory_limit", "1024M");

        set_time_limit(0);

        try {
            $ossClient->uploadFile($bucket, $object, $filePath);

        } catch (OssException $e) {
            return '0';
        }
        return '1';
    }

    public static function upload($file, $dir)
    {

        ini_set("memory_limit", "1024M");

        set_time_limit(0);

        $bucket = 'putaoliulanqi1';
        $endpoint = 'oss-cn-zhangjiakou.aliyuncs.com/';
        $filePath = $file->getRealPath();

        $name = $file->getClientOriginalName();

        $type = strtolower(substr(strrchr($name, '.'), 1));
        $pic_name = time() . rand(10000, 99999) . "." . $type;
        $object = $dir . '/' .  urlencode($pic_name);

        $ossClient = self::init();

        $result = self::uploadFile($ossClient, $bucket, $object, $filePath);

        if ($result) {
            return ['error' => '0', 'object' => 'http://' . $bucket . '.' . $endpoint . '' . $object];
        }
        return ['error' => '上传失败', 'object' => ''];
    }

    public static function max_upload($file)
    {
        $bucket = 'putaoliulanqi1';
        $endpoint = 'oss-cn-zhangjiakou.aliyuncs.com/';
        $filePath = $file['tmp_name'];
        $name = $file['name'];
        $type = strtolower(substr(strrchr($name, '.'), 1));
        $pic_name = time() . rand(10000, 99999) . "." . $type;
        $object = "putao" . DS . date("Ymd") . DS . $pic_name;
        $object = urlencode($object);
        $ossClient = self::init();

        $result = self::putObjectByRawApis($ossClient, $bucket, $object, $filePath);

        if ($result) {
            return ['error' => '0', 'object' => 'http://' . $bucket . '.' . $endpoint . DS . $object];
        }
        return ['error' => '上传失败', 'object' => ''];

    }

    public function multiuploadFile($ossClient, $bucket, $object, $filePath)
    {
        try {
            $ossClient->multiuploadFile($bucket, $object, $filePath);
        } catch (OssException $e) {
            return 0;
        }
        return 1;
    }

    public function putObjectByRawApis($ossClient, $bucket, $object, $uploadFile)
    {
        /**
         *  step 1. 初始化一个分块上传事件, 也就是初始化上传Multipart, 获取upload id
         */
        try {
            $uploadId = $ossClient->initiateMultipartUpload($bucket, $object);
        } catch (OssException $e) {
            return 0;
        }
        /*
         * step 2. 上传分片
         */
        $partSize = 10 * 1024 * 1024;
        $uploadFileSize = filesize($uploadFile);
        $pieces = $ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
        $responseUploadPart = array();
        $uploadPosition = 0;
        $isCheckMd5 = true;
        foreach ($pieces as $i => $piece) {
            $fromPos = $uploadPosition + (integer)$piece[$ossClient::OSS_SEEK_TO];
            $toPos = (integer)$piece[$ossClient::OSS_LENGTH] + $fromPos - 1;
            $upOptions = array(
                $ossClient::OSS_FILE_UPLOAD => $uploadFile,
                $ossClient::OSS_PART_NUM => ($i + 1),
                $ossClient::OSS_SEEK_TO => $fromPos,
                $ossClient::OSS_LENGTH => $toPos - $fromPos + 1,
                $ossClient::OSS_CHECK_MD5 => $isCheckMd5,
            );
            if ($isCheckMd5) {
                $contentMd5 = OssUtil::getMd5SumForFile($uploadFile, $fromPos, $toPos);
                $upOptions[$ossClient::OSS_CONTENT_MD5] = $contentMd5;
            }
            try {
                $responseUploadPart[] = $ossClient->uploadPart($bucket, $object, $uploadId, $upOptions);
            } catch (OssException $e) {

                return 0;
            }
        }
        $uploadParts = array();
        foreach ($responseUploadPart as $i => $eTag) {
            $uploadParts[] = array(
                'PartNumber' => ($i + 1),
                'ETag' => $eTag,
            );
        }
        /**
         * step 3. 完成上传
         */
        try {
            $ossClient->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
        } catch (OssException $e) {
            return 0;
        }
        return 1;
    }

}