<?php

use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\User;
use App\Support\EntityContext;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('allows super admins to run consolidated reports across all entities', function (): void {
    $entityA = Entity::query()->create([
        'name' => 'Kasira A',
        'code' => 'KSA',
        'currency' => 'IDR',
        'invoice_prefix' => 'KSA',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    $entityB = Entity::query()->create([
        'name' => 'Kasira B',
        'code' => 'KSB',
        'currency' => 'IDR',
        'invoice_prefix' => 'KSB',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $permission = Permission::query()->create(['name' => 'reports.view', 'guard_name' => 'web']);

    if (function_exists('setPermissionsTeamId')) {
        setPermissionsTeamId($entityA->getKey());
    }

    $role = Role::query()->create([
        'name' => 'super_admin',
        'guard_name' => 'web',
        'entity_id' => $entityA->getKey(),
    ]);
    $role->givePermissionTo($permission);
    $user->assignRole($role);
    $user->unsetRelation('roles')->unsetRelation('permissions');

    app(EntityContext::class)->clear();

    $customerA = Customer::query()->withoutGlobalScopes()->create([
        'entity_id' => $entityA->getKey(),
        'name' => 'Customer A',
        'email' => 'a@example.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $customerB = Customer::query()->withoutGlobalScopes()->create([
        'entity_id' => $entityB->getKey(),
        'name' => 'Customer B',
        'email' => 'b@example.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    Invoice::query()->withoutGlobalScopes()->create([
        'entity_id' => $entityA->getKey(),
        'customer_id' => $customerA->getKey(),
        'invoice_number' => 'KSA-001',
        'status' => 'paid',
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
        'subtotal' => '100.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '100.00',
        'amount_paid' => '100.00',
        'amount_due' => '0.00',
        'currency' => 'IDR',
        'public_token' => (string) Str::ulid(),
        'created_by' => $user->getKey(),
    ]);

    Invoice::query()->withoutGlobalScopes()->create([
        'entity_id' => $entityB->getKey(),
        'customer_id' => $customerB->getKey(),
        'invoice_number' => 'KSB-001',
        'status' => 'paid',
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
        'subtotal' => '50.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '50.00',
        'amount_paid' => '50.00',
        'amount_due' => '0.00',
        'currency' => 'IDR',
        'public_token' => (string) Str::ulid(),
        'created_by' => $user->getKey(),
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_entity_id' => $entityA->getKey()])
        ->get(route('reports.index', ['entity_id' => 'all', 'from' => now()->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString()]));

    $response->assertOk();
    $response->assertSee('150.00');
});
