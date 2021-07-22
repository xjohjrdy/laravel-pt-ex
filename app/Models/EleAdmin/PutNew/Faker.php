<?php

namespace App\Models\EleAdmin\PutNew;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EleAdmin\PutNew\FakerScopes;

class Faker extends Model
{
    use FakerScopes;

    protected $connection = 'app38';
    protected $table = 'lc_put_new_faker';
}