<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\EntityUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the demo user bootstrap.
     */
    public function run(): void
    {
        $superAdmin = $this->upsertUser(
            email: 'superadmin@kasira.test',
            name: 'Super Admin',
        );

        $this->assignGlobalRole($superAdmin, 'super_admin');

        $entities = Entity::query()->orderBy('name')->get()->keyBy('code');

        $hq = $entities->get('KASIRA-HQ');
        $branch = $entities->get('KASIRA-BR1');

        if ($hq) {
            $hqAdmin = $this->upsertUser(
                email: 'hq.admin@kasira.test',
                name: 'HQ Admin',
            );

            $this->assignEntityRole($hqAdmin, $hq, 'entity_admin', $superAdmin->getKey());
        }

        if ($branch) {
            $branchFinance = $this->upsertUser(
                email: 'branch.finance@kasira.test',
                name: 'Branch Finance',
            );

            $this->assignEntityRole($branchFinance, $branch, 'finance_manager', $superAdmin->getKey());
        }

        if ($hq && $branch) {
            $groupCfo = $this->upsertUser(
                email: 'group.cfo@kasira.test',
                name: 'Group CFO',
            );

            $this->assignEntityRole($groupCfo, $hq, 'finance_manager', $superAdmin->getKey());
            $this->assignEntityRole($groupCfo, $branch, 'finance_manager', $superAdmin->getKey());
        }
    }

    private function upsertUser(string $email, string $name): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );
    }

    private function assignGlobalRole(User $user, string $roleName): void
    {
        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId(null);
        }

        $user->syncRoles([$roleName]);
    }

    private function assignEntityRole(User $user, Entity $entity, string $roleName, int $assignedBy): void
    {
        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($entity->getKey());
        }

        $user->syncRoles([$roleName]);

        EntityUser::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'user_id' => $user->getKey(),
            ],
            [
                'role' => $roleName,
                'assigned_by' => $assignedBy,
            ],
        );

        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId(null);
        }
    }
}
