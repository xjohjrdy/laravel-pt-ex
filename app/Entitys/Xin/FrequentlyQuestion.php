<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class FrequentlyQuestion extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_frequently_question';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 获取随机的4个数据
     */
    public function getRandomFour()
    {
        $data = $this->limit(100)->get(['id','question']);
        foreach ($data as $item){
            $need[] = [
                'id'=>$item['id'],
                'question'=>$item['question']
            ];
        }
        shuffle($need);
        return array_slice($need, 0, 6);
    }
    /*
     * 根据问题类型得到问题列表
     */
    public function getListByType($type)
    {
        return $this->where(['type'=>$type])
            ->select(['id','question'])
            ->paginate(20);
    }
    /*
     * 根据id得到详情页数据
     */
    public function getFrequentlyQuestion($id)
    {
        return $this->where('id',$id)->first();
    }
}
