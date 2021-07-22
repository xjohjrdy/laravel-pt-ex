<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApplyUpgrade extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_apply_upgrade';
    public $timestamps = false;

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /*
     * 请求升级信息
     */
    public function getUserApplyUpgradeInfo($app_id)
    {
        $data = $this->where('user_id', $app_id)
            ->where('is_user_read', 1)
            ->where('status', '>', 0)
            ->orderByDesc('id')
            ->first(['id as apply_upgrade_id', 'status', 'reason']);
        return $data ? $data : '';
    }

    /*
     * 查看是否有未处理的请求
     */
    public function checkHasApplyUpgrade($app_id)
    {
        return $this->where('user_id', $app_id)
            ->whereNull('handle_time')
            ->first(['id']);
    }

    /*
     * 关联新增升级请求表
     */
    public function saveApplyUpgrade($data, $upgrade_images)
    {
        $image_data = [];
        foreach ($upgrade_images as $value) {
            $image_data[] = ['image_url' => $value];
        }
        DB::beginTransaction();
        try{
            $this->user_id = $data['app_id'];
            $this->status = 0;
            $this->is_user_read = 1;
            $this->reason = "";
            $this->wx_account = isset($data['wx_account'])?$data['wx_account']:"";
            $this->create_time = time();
            $this->save();
            if (!empty($image_data)) {
                $this->imageRelevance()->createMany($image_data);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    /*
     * 升级图片关联
     */
    public function imageRelevance()
    {
        return $this->hasMany("App\Entitys\Xin\ApplyUpgradeImage", 'apply_upgrade_id', "id");
    }
}
