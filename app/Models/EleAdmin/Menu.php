<?php

namespace App\Models\EleAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\MenuScopes;

class Menu extends Model
{
    use MenuScopes;

    public $timestamps = false;

    protected $connection = 'app38';
    protected $table = 'lc_admins_menus';

    protected $columns = ['id', 'title', 'name', 'path', 'component', 'redirect', 'parent_id', 'icon'];

    public $childrenMap = [];

    const ACCOUNT_STATUS_DISPLAY = 1;
    const ACCOUNT_STATUS_HIDE = 2;
    const ACCOUNT_STATUS_IN_DEV = 3;

    public static $statusList = [
        self::ACCOUNT_STATUS_DISPLAY => '显示',
        self::ACCOUNT_STATUS_HIDE => '隐藏',
        self::ACCOUNT_STATUS_IN_DEV => '开发中',
    ];

    public function parent()
    {
        return $this->hasOne('App\Models\EleAdmin\Menu', 'id', 'parent_id');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Models\EleAdmin\Role', 'lc_admins_role_menu');
    }

    public function children()
    {
        return $this->hasMany(get_class($this), 'parent_id', 'id');
    }

    public function allChildren()
    {
        $query = $this->children()->with('allChildren');
        if ($this->childrenMap) {
            $query->ofConditions($this->childrenMap);
        }

        $query->select($this->columns);

        return $query;
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
