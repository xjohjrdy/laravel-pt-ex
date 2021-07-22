<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class Poster extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_poster';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 得到海报主题、内容、url
     */
    public function getPoster()
    {
        return $this->orderBy('order')->get(['id as poster_id','title','content','img_url']);

    }
}
