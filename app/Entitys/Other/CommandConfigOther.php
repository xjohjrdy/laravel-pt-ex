<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommandConfigOther extends Model
{
    protected $connection = 'db001';
    protected $table = 'lc_command_execute_config';
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

    private $command = '';

    public function setCommandName($command)
    {
        $this->command = $command;
    }

    /**
     * 初始脚本信息
     * @param $end_time 脚本执行失败后重试的结束时间，如果脚本第一次执行则是脚本结束时间
     *
     */
    public function initCommandInfo($end_time)
    {
        $command = $this->where(['command_name' => $this->command]);
        if ($command->exists()) { // 脚本执行前，需初始化脚本执行的状态
            $entity = $command->first();
            // 判断单前时间段脚本是否全部执行完毕， 如果执行完则重置. 2、断单前时间段脚本是否全部执行完毕， 如果执行完则重置 否则
            if ($entity['status'] == 1 && $entity['end_time'] == 0) {
                $command->update(['status' => 0, 'end_time' => $end_time]);
            } elseif ($entity['end_time'] == 0) {
                $command->update(['end_time' => $end_time]);
            }

        } else { // 如果不存在该脚本则新增一条数据
            $entity = [
                'command_name' => $this->command,
                'start_time' => 0,
                'status' => 0,
                'page_index' => 1,
                'page_size' => 5000,
                'end_time' => $end_time
            ];
            $this->create($entity);
        }
        return $command->first();
    }

    /**
     * 所有分页执行成功后脚本更新脚本状态
     */
    public function pageSuccess($page)
    {
        $this->where(['command_name' => $this->command])->update([
            'page_index' => $page
        ]);
    }

    /**
     * 所有分页执行成功后初始化脚本状态
     */
    public function allSuccess($time)
    {
        $this->where(['command_name' => $this->command])->update([
            'start_time' => $time,
            'status' => 1,
            'msg' => '',
            'page_index' => 1,
            'end_time' => 0,
            'new_column_keys' => '',
        ]);
    }

    /**
     * 脚本执行失败
     */
    public function error($msg)
    {
        $this->where(['command_name' => $this->command])->update(['status' => 0, 'msg' => mb_substr($msg, 0, 499)]);
    }
}
