<?php

namespace App\Models\EleAdmin\PutNew;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\PutNew\RankScopes;

class Rank extends Model
{
    use RankScopes;

    protected $connection = 'app38';
    protected $table = 'lc_put_new_rank_list';

    const CHANGE_SCRIPT = 0;
    const CHANGE_ADMIN = 1;

    public static $changeList = [
        self::CHANGE_SCRIPT => '脚本',
        self::CHANGE_ADMIN => '后台',
    ];
}