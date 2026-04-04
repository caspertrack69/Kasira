<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Policies\Concerns\ChecksPermission;

class PaymentMethodPolicy
{
    use ChecksPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('payment-methods.view');
    }

    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->canForEntity($user, 'payment-methods.view', $paymentMethod->entity_id);
    }

    public function create(User $user): bool
    {
        return $user->can('payment-methods.manage');
    }

    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->canForEntity($user, 'payment-methods.manage', $paymentMethod->entity_id);
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->canForEntity($user, 'payment-methods.manage', $paymentMethod->entity_id);
    }
}
