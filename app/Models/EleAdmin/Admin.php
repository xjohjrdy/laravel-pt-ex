<?php

namespace App\Models\EleAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\AdminScopes;
use Illuminate\Support\Facades\Hash;

class Admin extends Model
{
    use AdminScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_admins';

    const ACCOUNT_STATUS_ENABLE = 1;
    const ACCOUNT_STATUS_DISABLE = 2;

    const IS_MASTER = 1; //主管理员

    public static $statusList = [
        self::ACCOUNT_STATUS_ENABLE => '启用',
        self::ACCOUNT_STATUS_DISABLE => '禁用',
    ];

    public function roles()
    {
        return $this->belongsToMany('App\Models\EleAdmin\Role', 'lc_admins_admin_role');
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

    public function getLoginedAtAttribute($value)
    {
        if ($value > 0) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }
}
