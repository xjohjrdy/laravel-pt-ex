<?php

namespace App\Services\Common;


use OSS\Core\OssException;
use OSS\OssClient;

class OssCdn
{

    protected static $accessKeyId = "LTAI4Fzg25DR5i3vVReYWQJH";
    protected static $accessKeySecret = "gIFsHl6wa5F9AT1l3ioEv9nmjaS7Ls";
    protected static $endpoint = "http://oss-cn-zhangjiakou.aliyuncs.com";
    protected static $cdnHost = "http://cdn01.36qq.com"; //cdn地址
    protected static $bucket = "putaoliulanqi1";

    public static function upload($file, $dir = 'default')
    {
        ini_set("memory_limit", "1024M");
        set_time_limit(0);
        $name = $file->getClientOriginalName();
        $type = strtolower(substr(strrchr($name, '.'), 1));
        $pic_name = time() . rand(10000, 99999) . "." . $type;
        // 设置文件名称。
        $object = 'cdn01/' . $dir . '/' . date('Ymd') . '/' . $pic_name;
        $filePath = $file->getRealPath();
        try {
            $ossClient = new OssClient(self::$accessKeyId, self::$accessKeySecret, self::$endpoint);

            $result = $ossClient->uploadFile(self::$bucket, $object, $filePath);

        } catch (OssException $e) {
//            printf(__FUNCTION__ . ": FAILED\n");
//            printf($e->getMessage() . "\n");
            return false;
        }
        if (empty($result)) {
            return false;
        }

        return self::$cdnHost . '/' . $object;
    }
}
