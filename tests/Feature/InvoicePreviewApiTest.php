<?php

use App\Models\Entity;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('returns server-side invoice totals for the active entity', function (): void {
    $entity = Entity::query()->create([
        'name' => 'Preview Entity',
        'code' => 'PREVIEW',
        'currency' => 'IDR',
        'invoice_prefix' => 'PRV',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $permission = Permission::query()->create(['name' => 'invoices.manage', 'guard_name' => 'web']);

    if (function_exists('setPermissionsTeamId')) {
        setPermissionsTeamId($entity->getKey());
    }

    $role = Role::query()->create([
        'name' => 'finance_manager',
        'guard_name' => 'web',
        'entity_id' => $entity->getKey(),
    ]);
    $role->givePermissionTo($permission);
    $user->entities()->attach($entity->getKey(), [
        'role' => 'finance_manager',
        'assigned_by' => $user->getKey(),
    ]);
    $user->assignRole($role);
    $user->unsetRelation('roles')->unsetRelation('permissions');

    $tax = Tax::query()->withoutGlobalScopes()->create([
        'entity_id' => $entity->getKey(),
        'name' => 'VAT 11%',
        'code' => 'VAT11',
        'type' => 'exclusive',
        'rate' => '11.00',
        'is_default' => false,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_entity_id' => $entity->getKey()])
        ->postJson(route('api.internal.invoices.preview', ['entity' => $entity->getKey()]), [
            'invoice_date' => '2026-04-01',
            'items' => [
                [
                    'description' => 'Preview Service',
                    'quantity' => 2,
                    'unit_price' => 100,
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                    'tax_id' => $tax->getKey(),
                ],
            ],
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'subtotal' => '200.00',
            'discount_total' => '20.00',
            'tax_total' => '19.80',
            'grand_total' => '199.80',
            'due_date' => '2026-04-15',
            'currency' => 'IDR',
        ],
    ]);

    expect($response->json('data.items.0.subtotal'))->toBe('199.80');
});
