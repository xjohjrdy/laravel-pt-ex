<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_suggestion';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /*
     * 添加建议
     */
    public function addtSuggestion($type, $content, $contact_phone)
    {
        return $this->insert([
            'type' => $type,
            'content' => $content,
            'contact_phone' => $contact_phone,
            'create_time' => time()
        ]);
    }

    /**
     * 新增建议
     */
    public function addNew($data)
    {
        return $this->create($data);
    }

}
