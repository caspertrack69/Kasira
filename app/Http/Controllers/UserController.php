<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\EntityUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Entity::class);

        return view('users.index', [
            'users' => User::query()->with('entities')->latest()->paginate(20),
            'entities' => Entity::query()->where('is_active', true)->orderBy('name')->get(),
            'roles' => Role::query()->orderBy('name')->pluck('name')->unique()->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Entity::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'entity_id' => ['nullable', 'exists:entities,id'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (! empty($validated['entity_id']) && ! empty($validated['role'])) {
            $entity = Entity::query()->findOrFail($validated['entity_id']);
            $this->assignRole($user, $entity, $validated['role']);
        }

        return back()->with('status', 'User created.');
    }

    public function assign(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', Entity::class);

        $validated = $request->validate([
            'entity_id' => ['required', 'exists:entities,id'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $entity = Entity::query()->findOrFail($validated['entity_id']);
        $this->assignRole($user, $entity, $validated['role']);

        return back()->with('status', 'Role assigned.');
    }

    private function assignRole(User $user, Entity $entity, string $role): void
    {
        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($entity->getKey());
        }

        $user->syncRoles([$role]);

        EntityUser::query()->updateOrCreate(
            ['entity_id' => $entity->getKey(), 'user_id' => $user->getKey()],
            ['role' => $role, 'assigned_by' => request()->user()?->getKey()],
        );

        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId(session('active_entity_id'));
        }
    }
}
