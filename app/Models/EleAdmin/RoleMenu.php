<?php

namespace App\Models\EleAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\RoleMenuScopes;

class RoleMenu extends Model
{
    use RoleMenuScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_admins_role_menu';

    const ACCOUNT_STATUS_EFFECTIVE = 1;
    const ACCOUNT_STATUS_FAILURE = 2;

    public static $statusList = [
        self::ACCOUNT_STATUS_EFFECTIVE => '生效',
        self::ACCOUNT_STATUS_FAILURE => '失效',
    ];
}
