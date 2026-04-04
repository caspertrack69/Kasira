<?php

namespace App\Policies;

use App\Models\RecurringTemplate;
use App\Models\User;
use App\Policies\Concerns\ChecksPermission;

class RecurringTemplatePolicy
{
    use ChecksPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('recurring.view');
    }

    public function view(User $user, RecurringTemplate $recurringTemplate): bool
    {
        return $this->canForEntity($user, 'recurring.view', $recurringTemplate->entity_id);
    }

    public function create(User $user): bool
    {
        return $user->can('recurring.manage');
    }

    public function update(User $user, RecurringTemplate $recurringTemplate): bool
    {
        return $this->canForEntity($user, 'recurring.manage', $recurringTemplate->entity_id);
    }

    public function delete(User $user, RecurringTemplate $recurringTemplate): bool
    {
        return $this->canForEntity($user, 'recurring.manage', $recurringTemplate->entity_id);
    }
}
