<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'entity_users')
            ->using(EntityUser::class)
            ->withPivot(['role', 'assigned_by'])
            ->withTimestamps();
    }

    public function hasEntityAccess(Entity|string $entity): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $entityId = $entity instanceof Entity ? $entity->getKey() : $entity;

        return $this->entities()->whereKey($entityId)->exists();
    }

    public function isSuperAdmin(): bool
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';

        return DB::table($tableNames['model_has_roles'].' as mhr')
            ->join($tableNames['roles'].' as roles', 'roles.id', '=', 'mhr.'.$rolePivotKey)
            ->where('mhr.model_type', self::class)
            ->where('mhr.model_id', $this->getKey())
            ->where('roles.name', 'super_admin')
            ->exists();
    }
}
