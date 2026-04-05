<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
                <i class="ph ph-users-three text-xl text-slate-600"></i>
            </div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Users</h2>
        </div>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-1">
            <x-ui.card class="rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
                <button type="button" @click="createOpen = !createOpen" class="group flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold tracking-tight text-slate-900">User Actions</h3>
                        <p class="mt-0.5 text-[11px] font-medium text-slate-500">Form create user dibuka via tombol aksi</p>
                    </div>
                    <span class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm shadow-slate-900/20 transition-all hover:bg-slate-800">
                        <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                        Tambah
                    </span>
                </button>
            </x-ui.card>

            <div x-show="createOpen" x-cloak 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-4 scale-95">
                 
                <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-xl shadow-slate-200/40">
                    <h3 class="mb-5 text-sm font-bold tracking-tight text-slate-900">Create User</h3>
                    <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                        @csrf
                        <div class="space-y-4">
                            <x-ui.input name="name" label="Name" />
                            <x-ui.input name="email" label="Email" type="email" />
                            <x-ui.input name="password" label="Password" type="password" />
                            <x-ui.select name="entity_id" label="Initial Entity" :options="$entities->pluck('name','id')->all()" />
                            <x-ui.select name="role" label="Role" :options="$roles->mapWithKeys(fn($role) => [$role => $role])->all()" />
                        </div>
                        <div class="pt-2">
                            <x-ui.button type="submit" class="w-full justify-center rounded-xl py-2.5">Create User</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>

        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">User Directory</h3>
            </div>
            
            <div class="overflow-x-auto">
                <x-ui.table class="w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Name</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Email</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Entities</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80 bg-white">
                        @forelse($users as $user)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-500/20">
                                            <i class="ph ph-user text-lg"></i>
                                        </div>
                                        <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-700">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->entities->isNotEmpty())
                                        <p class="text-sm font-medium text-slate-600">{{ $user->entities->pluck('name')->join(', ') }}</p>
                                    @else
                                        <span class="text-xs font-medium text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="ph ph-users-three mb-3 text-4xl text-slate-300"></i>
                                        <p class="text-sm font-medium text-slate-500">No users found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            @if($users->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>