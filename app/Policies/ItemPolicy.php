<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use App\Policies\Concerns\ChecksPermission;

class ItemPolicy
{
    use ChecksPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('items.view');
    }

    public function view(User $user, Item $item): bool
    {
        return $this->canForEntity($user, 'items.view', $item->entity_id);
    }

    public function create(User $user): bool
    {
        return $user->can('items.manage');
    }

    public function update(User $user, Item $item): bool
    {
        return $this->canForEntity($user, 'items.manage', $item->entity_id);
    }

    public function delete(User $user, Item $item): bool
    {
        return $this->canForEntity($user, 'items.manage', $item->entity_id);
    }
}
