<?php

use App\Models\Entity;
use App\Models\SystemSetting;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('rejects protected gateway credentials in the settings database form', function (): void {
    $entity = Entity::query()->create([
        'name' => 'Settings Entity',
        'code' => 'SET-ENT',
        'currency' => 'IDR',
        'invoice_prefix' => 'SET',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $permission = Permission::query()->create(['name' => 'settings.manage', 'guard_name' => 'web']);

    if (function_exists('setPermissionsTeamId')) {
        setPermissionsTeamId($entity->getKey());
    }

    $role = Role::query()->create([
        'name' => 'entity_admin',
        'guard_name' => 'web',
        'entity_id' => $entity->getKey(),
    ]);
    $role->givePermissionTo($permission);
    $user->entities()->attach($entity->getKey(), [
        'role' => 'entity_admin',
        'assigned_by' => $user->getKey(),
    ]);
    $user->assignRole($role);
    $user->unsetRelation('roles')->unsetRelation('permissions');

    $response = $this
        ->actingAs($user)
        ->withSession(['active_entity_id' => $entity->getKey()])
        ->from(route('settings.index'))
        ->put(route('settings.update'), [
            'settings' => [
                [
                    'group' => 'payments',
                    'key' => 'midtrans_server_key',
                    'value' => 'secret-demo-value',
                ],
            ],
        ]);

    $response->assertRedirect(route('settings.index'));
    $response->assertSessionHasErrors(['settings.0.key']);
    $this->assertDatabaseMissing('system_settings', ['key' => 'midtrans_server_key']);
});

it('stores non-sensitive settings rows normally', function (): void {
    $entity = Entity::query()->create([
        'name' => 'Settings Safe Entity',
        'code' => 'SET-SAFE',
        'currency' => 'IDR',
        'invoice_prefix' => 'SSF',
        'default_payment_terms' => 30,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $permission = Permission::query()->create(['name' => 'settings.manage', 'guard_name' => 'web']);

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

    $response = $this
        ->actingAs($user)
        ->withSession(['active_entity_id' => $entity->getKey()])
        ->put(route('settings.update'), [
            'settings' => [
                [
                    'group' => 'branding',
                    'key' => 'invoice_footer_note',
                    'value' => 'Thank you for your business.',
                ],
            ],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    expect(SystemSetting::value('invoice_footer_note'))->toBe('Thank you for your business.');
    $this->assertDatabaseHas('system_settings', [
        'group' => 'branding',
        'key' => 'invoice_footer_note',
        'value' => 'Thank you for your business.',
    ]);
});
