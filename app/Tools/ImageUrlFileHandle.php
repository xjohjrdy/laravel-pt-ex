<?php

namespace App\Tools;

class ImageUrlFileHandle
{
    protected static $imgTypes = ['jpg', 'jpeg', 'png'];

    public static function setImgUrlData($imgUrl = '', $filedName = 'img', $imgType = 'jpg')
    {
        if ($imgUrl) {
            $imgData = explode('.', $imgUrl);
            $imgUrlType = array_pop($imgData);
            $imgType = in_array($imgUrlType, self::$imgTypes) ? $imgUrlType : $imgType;
            $imgUrl = [
                'name' => "{$filedName}.{$imgType}",
                'url' => $imgUrl,
            ];

            return $imgUrl;
        }

        return '';
    }
}