<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorkOrder extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_work_order';
    public $timestamps = false;
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /*
     * 用户已回复工单的总数
     */
    public function getReplyUnread($app_id)
    {
        return $total = $this->where(['user_id' => $app_id, 'status' => 2, 'read_status' => 1])->count('id');
    }

    /*
     * 根据id修改用户问题的已读状态
     */
    public function setReplyWorkOrderReadStatus($app_id)
    {
        return $this->where(['user_id' => $app_id, 'status' => 2])->update(['read_status' => 2]);
    }

    /*
     * 得到问题列表
     */
    public function getApiPageList($app_id)
    {
        return $this->where('user_id', $app_id)
            ->orderBy('create_time')
            ->paginate(10, ['id as work_order_id', 'title', 'status', 'read_status', 'create_time']);
    }

    /*
     * 新增提交问题
     */
    public function saveWorkOrder($data)
    {
        if (!empty($data['img'])) {
            $data['img'] = explode(',', $data['img']);
            foreach ($data['img'] as $value) {
                $image_data[] = ['img' => $value];
            }
        }
        DB::beginTransaction();
          try{
        $this->user_id = $data['app_id'];
        $this->title = $data['title'];
        $this->content = $data['content'];
        $this->create_time = time();
        $this->save();
        if (!empty($image_data)) {
            $this->imageRelevance()->createMany($image_data);
        }
        Db::commit();
        return true;
          } catch (\Exception $e) {
           Db::rollback();
           return false;
        }
    }

    /*
     * 图片关联
     */
    public function imageRelevance()
    {
        return $this->hasMany("App\Entitys\Xin\WorkOrderImg", 'work_order_id', "id");
    }
    /*
     * 根据主键得到数据
     */
    public function getWorkOrderDetails($id)
    {
        $data = $this->find($id);
        if (empty($data)){
            return false;
        }
        $data->image = $this->imageRelevance()->where('work_order_id',$id)->get(['img']);
        return $data;
    }
}