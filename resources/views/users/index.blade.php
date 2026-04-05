<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Users</h2></x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4">
            <x-ui.card>
                <button type="button" @click="createOpen = !createOpen" class="flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold">User Actions</h3>
                        <p class="text-xs text-slate-500">Form create user dibuka via tombol aksi.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">
                        <i class="ph ph-plus"></i>
                        Tambah User
                    </span>
                </button>
            </x-ui.card>

            <x-ui.card x-show="createOpen" x-cloak x-transition>
                <h3 class="mb-3 text-sm font-semibold">Create User</h3>
                <form method="POST" action="{{ route('users.store') }}" class="space-y-3">
                    @csrf
                    <x-ui.input name="name" label="Name" />
                    <x-ui.input name="email" label="Email" type="email" />
                    <x-ui.input name="password" label="Password" type="password" />
                    <x-ui.select name="entity_id" label="Initial Entity" :options="$entities->pluck('name','id')->all()" />
                    <x-ui.select name="role" label="Role" :options="$roles->mapWithKeys(fn($role) => [$role => $role])->all()" />
                    <x-ui.button type="submit">Create</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <x-ui.card class="lg:col-span-2">
            <x-ui.table>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">Entities</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-3 py-2">{{ $user->name }}</td>
                            <td class="px-3 py-2">{{ $user->email }}</td>
                            <td class="px-3 py-2">
                                {{ $user->entities->pluck('name')->join(', ') ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
            <div class="mt-3">{{ $users->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
