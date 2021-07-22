<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_question_type';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 得到全部问题类型
     */
    public function getTypeList()
    {
        return $this->get();

    }

}
