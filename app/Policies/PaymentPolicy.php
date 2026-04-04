<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksPermission;

class PaymentPolicy
{
    use ChecksPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->canForEntity($user, 'payments.view', $payment->entity_id);
    }

    public function create(User $user): bool
    {
        return $user->can('payments.manage');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->canForEntity($user, 'payments.manage', $payment->entity_id);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->canForEntity($user, 'payments.manage', $payment->entity_id);
    }

    public function confirm(User $user, Payment $payment): bool
    {
        return $this->canForEntity($user, 'payments.confirm', $payment->entity_id);
    }
}
