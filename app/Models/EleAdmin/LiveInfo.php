<?php

namespace App\Models\EleAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\LiveInfoScopes;

class LiveInfo extends Model
{
    use LiveInfoScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_live_info';

    const STATUS_NOTICE = 0;
    const STATUS_START = 1;
    const STATUS_END = 3;

    public static $statusList = [
        self::STATUS_NOTICE => '预告',
        self::STATUS_START => '直播中',
        self::STATUS_END => '直播结束',
    ];

//    public function goods()
//    {
//        return $this->hasMany('App\Models\EleAdmin\LiveShopGoods', 'live_id');
//    }

    public function getPlanTimeAttribute($value)
    {
        if ($value > 0) {
            return date('Y-m-d H:i:s', $value);
        }

        return '';
    }
}