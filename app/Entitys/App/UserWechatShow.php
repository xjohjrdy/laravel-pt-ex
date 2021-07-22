<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWechatShow extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_user_wechat_show';


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

    /**
     * 查
     */
    public function getInfo($app_id)
    {
        return $this->where([
            'app_id' => $app_id
        ])->first();
    }

    /**
     * 加
     */
    public function addInfo($data)
    {
        return $this->create($data);
    }

    /**
     * 更
     */
    public function updateData($app_id, $wechat_info)
    {
        $res = $this->where([
            'app_id' => $app_id
        ])->first();
        if (empty($res)) {
            return $this->create([
                'app_id' => $app_id,
                'wechat_info' => $wechat_info,
            ]);
        }
        return $this->where(['app_id' => $app_id])->update([
            'wechat_info' => $wechat_info
        ]);
    }
}
