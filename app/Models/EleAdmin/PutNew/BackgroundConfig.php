<?php

namespace App\Models\EleAdmin\PutNew;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\PutNew\BackgroundConfigScopes;

class BackgroundConfig extends Model
{
    use BackgroundConfigScopes;

    protected $connection = 'app38';
    protected $table = 'lc_put_new_background_all';
}