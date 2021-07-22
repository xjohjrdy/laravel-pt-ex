<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;

class MiniWechatInfo extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_mini_wechat_info';
    use SoftDeletes;

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    public function validOpenId($openId = ''){
        return $this->where(['openid' => $openId])->first();
    }

    public function relateAuthInfo($openId = '', $app_id = '')
    {
        $this->where(['openid' => $openId])->update(['app_id' => $app_id]);
    }
}
