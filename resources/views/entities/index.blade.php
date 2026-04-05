<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Entities</h2>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">Entity Directory</h3>
                <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                    <span x-text="createOpen ? 'Batal' : 'Tambah Entity'"></span>
                </button>
            </div>

            <div x-show="createOpen" x-collapse x-cloak class="border-b border-slate-200 bg-slate-50/50 p-6">
                <form method="POST" action="{{ route('entities.store') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <x-ui.input name="name" label="Name" required />
                    <x-ui.input name="code" label="Code" required />
                    <x-ui.input name="email" label="Email" type="email" />
                    <x-ui.input name="currency" label="Currency" value="IDR" />
                    <x-ui.input name="invoice_prefix" label="Invoice Prefix" />
                    <x-ui.input name="default_payment_terms" label="Payment Terms (Days)" type="number" value="30" />
                    <x-ui.input name="reminder_days" label="Reminder Days" value="1,3,7" placeholder="e.g. 1,3,7" />
                    <div class="md:col-span-2 mt-2">
                        <x-ui.button type="submit" class="rounded-xl px-5 py-2.5">Create Entity</x-ui.button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Entity Info</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Contact Email</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Billing Setup</th>
                            <th scope="col" class="px-6 py-4 text-center font-semibold">Status</th>
                            <th scope="col" class="px-6 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    
                    @forelse($entities as $entity)
                        <tbody x-data="{ editOpen: false }" class="border-b border-slate-100 last:border-0 hover:bg-slate-50/30 transition-colors">
                            
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 align-top">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-500/20">
                                            <i class="ph ph-buildings text-xl"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $entity->name }}</div>
                                            <div class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-500">{{ $entity->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 align-top">
                                    <span class="text-slate-700">{{ $entity->email ?: '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 align-top">
                                    <div class="font-medium text-slate-900">{{ $entity->currency }}</div>
                                    <div class="mt-0.5 text-xs text-slate-500">{{ $entity->default_payment_terms }} Days • Pre: {{ $entity->invoice_prefix ?: '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center align-top">
                                    <x-ui.badge :status="$entity->is_active ? 'paid' : 'cancelled'" class="rounded-lg px-2.5 py-1 text-[11px] font-semibold">
                                        {{ $entity->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right align-top">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editOpen = !editOpen" class="inline-flex h-8 items-center gap-1.5 rounded-lg px-3 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50">
                                            <i class="ph ph-pencil-simple"></i> <span x-text="editOpen ? 'Tutup' : 'Edit'"></span>
                                        </button>
                                        <form method="POST" action="{{ route('entities.destroy', $entity) }}" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan/menghapus entitas ini?');">
                                            @csrf @method('DELETE') 
                                            <button type="submit" class="inline-flex h-8 items-center gap-1.5 rounded-lg px-3 text-xs font-medium text-rose-600 transition hover:bg-rose-50">
                                                <i class="ph ph-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <tr x-show="editOpen" x-cloak>
                                <td colspan="5" class="p-0">
                                    <div x-show="editOpen" x-collapse>
                                        <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-6 shadow-inner">
                                            <h4 class="mb-4 text-sm font-semibold text-slate-900">Edit Entity Settings</h4>
                                            
                                            <form id="update-form-{{ $entity->id }}" method="POST" action="{{ route('entities.update', $entity) }}" class="space-y-5">
                                                @csrf @method('PUT')
                                                <div class="grid gap-5 md:grid-cols-3">
                                                    <x-ui.input name="name" label="Name" :value="$entity->name" required />
                                                    <x-ui.input name="email" label="Email" type="email" :value="$entity->email" />
                                                    <x-ui.input name="currency" label="Currency" :value="$entity->currency" required />
                                                    <x-ui.input name="invoice_prefix" label="Invoice Prefix" :value="$entity->invoice_prefix" />
                                                    <x-ui.input name="default_payment_terms" label="Payment Terms" type="number" :value="$entity->default_payment_terms" />
                                                    <x-ui.input name="reminder_days" label="Reminder Days" :value="implode(',', $entity->reminder_days ?? [])" />
                                                </div>
                                            </form>

                                            <div class="mt-6 flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                                <label class="inline-flex cursor-pointer items-center gap-2.5">
                                                    <input type="hidden" form="update-form-{{ $entity->id }}" name="is_active" value="0">
                                                    <input type="checkbox" form="update-form-{{ $entity->id }}" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600 shadow-sm transition focus:ring-indigo-600" @checked($entity->is_active)>
                                                    <span class="text-sm font-medium text-slate-700">Set as Active Entity</span>
                                                </label>

                                                <x-ui.button type="submit" form="update-form-{{ $entity->id }}" class="rounded-xl px-6 py-2 shadow-sm">
                                                    Save Changes
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 mb-3 text-slate-400">
                                        <i class="ph ph-buildings text-2xl"></i>
                                    </div>
                                    <h3 class="text-sm font-medium text-slate-900">Belum ada Entitas</h3>
                                    <p class="mt-1 text-sm text-slate-500">Mulai dengan menambahkan entitas baru di atas.</p>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            @if($entities->hasPages())
                <div class="border-t border-slate-100 bg-white px-6 py-4">
                    {{ $entities->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>