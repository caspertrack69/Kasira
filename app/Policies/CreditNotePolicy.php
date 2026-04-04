<?php

namespace App\Policies;

use App\Models\CreditNote;
use App\Models\User;
use App\Policies\Concerns\ChecksPermission;

class CreditNotePolicy
{
    use ChecksPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('credit-notes.view');
    }

    public function view(User $user, CreditNote $creditNote): bool
    {
        return $this->canForEntity($user, 'credit-notes.view', $creditNote->entity_id);
    }

    public function create(User $user): bool
    {
        return $user->can('credit-notes.manage');
    }

    public function update(User $user, CreditNote $creditNote): bool
    {
        return $this->canForEntity($user, 'credit-notes.manage', $creditNote->entity_id);
    }

    public function delete(User $user, CreditNote $creditNote): bool
    {
        return $this->canForEntity($user, 'credit-notes.manage', $creditNote->entity_id);
    }
}
