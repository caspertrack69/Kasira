<?php

namespace App\Policies;

use App\Models\Tax;
use App\Models\User;
use App\Policies\Concerns\ChecksPermission;

class TaxPolicy
{
    use ChecksPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('taxes.view');
    }

    public function view(User $user, Tax $tax): bool
    {
        return $this->canForEntity($user, 'taxes.view', $tax->entity_id);
    }

    public function create(User $user): bool
    {
        return $user->can('taxes.manage');
    }

    public function update(User $user, Tax $tax): bool
    {
        return $this->canForEntity($user, 'taxes.manage', $tax->entity_id);
    }

    public function delete(User $user, Tax $tax): bool
    {
        return $this->canForEntity($user, 'taxes.manage', $tax->entity_id);
    }
}
