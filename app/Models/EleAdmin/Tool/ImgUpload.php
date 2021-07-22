<?php

namespace App\Models\EleAdmin\Tool;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\Tool\ImgUploadScopes;

class ImgUpload extends Model
{
    use ImgUploadScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_tool_img_upload_records';

    const CMP_80 = 1;
    const CMP_H100 = 2;
    const CMP_W100 = 3;
    const CMP_H200 = 4;
    const CMP_W200 = 5;

    public static $cmpTypeList = [
        self::CMP_80 => '/80.webp',
        self::CMP_H100 => '/h100.webp',
        self::CMP_W100 => '/w100.webp',
        self::CMP_H200 => '/h200.webp',
        self::CMP_W200 => '/w200.webp',
    ];

    public static $cmpTypeTextList = [
        self::CMP_80 => '内容压缩至80%',
        self::CMP_H100 => '限制高度100px，内容压缩90%',
        self::CMP_W100 => '限制宽度100px，内容压缩至90%',
        self::CMP_H200 => '限制高度200px，内容压缩90%',
        self::CMP_W200 => '限制宽度200px，内容压缩至90%',
    ];

    public function admin()
    {
        return $this->belongsTo('App\Models\EleAdmin\Admin', 'op_id');
    }

    public function getCreatedAtAttribute($value)
    {
        if ($value > 0) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }
}