<?php

namespace App\Policies\Concerns;

use App\Models\Entity;
use App\Models\User;

trait ChecksPermission
{
    protected function canForEntity(User $user, string $permission, Entity|string|null $entity = null): bool
    {
        if ($entity !== null && ! $user->hasEntityAccess($entity)) {
            return false;
        }

        return $user->can($permission);
    }
}
