<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\ChecksPermission;

class InvoicePolicy
{
    use ChecksPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->canForEntity($user, 'invoices.view', $invoice->entity_id);
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.manage');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->canForEntity($user, 'invoices.manage', $invoice->entity_id);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->canForEntity($user, 'invoices.manage', $invoice->entity_id);
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $this->canForEntity($user, 'invoices.send', $invoice->entity_id);
    }

    public function void(User $user, Invoice $invoice): bool
    {
        return $this->canForEntity($user, 'invoices.void', $invoice->entity_id);
    }
}
