<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class AdMenu extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_strong_find_menu';
    public $timestamps = false;

    /**
     * 返回菜单
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getMenu()
    {
        $res = $this->orderBy('displayorder','asc')->get(['icon','title','id']);

        return $res;
    }
}
