<?php

namespace App\Models\EleAdmin\PutNew;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\PutNew\RewardScopes;

class Reward extends Model
{
    use RewardScopes;

    protected $connection = 'app38';
    protected $table = 'lc_put_new_reward';

    const FOR_ONE_FIRST = 1;
    const FOR_ONE_SECOND = 2;
    const FOR_ONE_THIRD = 3;
    const FOR_ONE_OTHER = 4;

    public static $forOneList = [
        self::FOR_ONE_FIRST => '第1名',
        self::FOR_ONE_SECOND => '第2名',
        self::FOR_ONE_THIRD => '第3名',
        self::FOR_ONE_OTHER => '第4-50名',
    ];
}