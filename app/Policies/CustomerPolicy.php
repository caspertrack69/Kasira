<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Policies\Concerns\ChecksPermission;

class CustomerPolicy
{
    use ChecksPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->canForEntity($user, 'customers.view', $customer->entity_id);
    }

    public function create(User $user): bool
    {
        return $user->can('customers.manage');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->canForEntity($user, 'customers.manage', $customer->entity_id);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->canForEntity($user, 'customers.manage', $customer->entity_id);
    }
}
