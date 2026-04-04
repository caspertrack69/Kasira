<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EntityUser extends Pivot
{
    protected $table = 'entity_users';

    protected $fillable = [
        'entity_id',
        'user_id',
        'role',
        'assigned_by',
    ];
}
