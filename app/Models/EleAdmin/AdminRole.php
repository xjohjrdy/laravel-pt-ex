<?php

namespace App\Models\EleAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\AdminRoleScopes;

class AdminRole extends Model
{
    use AdminRoleScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_admins_admin_role';

    const ACCOUNT_STATUS_EFFECTIVE = 1;
    const ACCOUNT_STATUS_FAILURE = 2;

    public static $statusList = [
        self::ACCOUNT_STATUS_EFFECTIVE => '生效',
        self::ACCOUNT_STATUS_FAILURE => '失效',
    ];
}
