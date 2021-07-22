<?php

namespace App\Models\EleAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\RoleScopes;

class Role extends Model
{
    use RoleScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_admins_roles';

    const ACCOUNT_STATUS_DISPLAY = 1;
    const ACCOUNT_STATUS_HIDE = 2;

    public static $statusList = [
        self::ACCOUNT_STATUS_DISPLAY => '显示',
        self::ACCOUNT_STATUS_HIDE => '隐藏',
    ];

    public function admins()
    {
        return $this->belongsToMany('App\Models\EleAdmin\Admin', 'lc_admins_admin_role');
    }

    public function menus()
    {
        return $this->belongsToMany('App\Models\EleAdmin\Menu', 'lc_admins_role_menu');
    }

    public function getCreatedAtAttribute($value)
    {
        if ($value > 0) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }

    public function getUpdatedAtAttribute($value)
    {
        if ($value > 0) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }
}
