<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the permission bootstrap.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions() as $permission) {
            Permission::query()->updateOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                [],
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function permissions(): array
    {
        return [
            'entities.manage',
            'customers.view',
            'customers.manage',
            'taxes.view',
            'taxes.manage',
            'items.view',
            'items.manage',
            'payment-methods.view',
            'payment-methods.manage',
            'invoices.view',
            'invoices.manage',
            'invoices.send',
            'invoices.void',
            'payments.view',
            'payments.manage',
            'payments.confirm',
            'credit-notes.view',
            'credit-notes.manage',
            'recurring.view',
            'recurring.manage',
            'reports.view',
            'notifications.view',
            'audit.view',
            'settings.manage',
        ];
    }
}
