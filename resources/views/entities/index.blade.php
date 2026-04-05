<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Entities</h2></x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4">
            <x-ui.card>
                <button type="button" @click="createOpen = !createOpen" class="flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold">Entity Actions</h3>
                        <p class="text-xs text-slate-500">Kelola struktur entitas dari tombol ini.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">
                        <i class="ph ph-plus"></i>
                        Tambah Entity
                    </span>
                </button>
            </x-ui.card>

            <x-ui.card x-show="createOpen" x-cloak x-transition>
                <h3 class="mb-3 text-sm font-semibold">Create Entity</h3>
                <form method="POST" action="{{ route('entities.store') }}" class="space-y-3">
                    @csrf
                    <x-ui.input name="name" label="Name" />
                    <x-ui.input name="code" label="Code" />
                    <x-ui.input name="email" label="Email" type="email" />
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.input name="currency" label="Currency" value="IDR" />
                        <x-ui.input name="invoice_prefix" label="Invoice Prefix" />
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.input name="default_payment_terms" label="Payment Terms" type="number" value="30" />
                        <x-ui.input name="reminder_days" label="Reminder Days" value="1,3,7" />
                    </div>
                    <x-ui.button type="submit">Create</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-4 lg:col-span-2">
            @foreach($entities as $entity)
                <x-ui.card>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold">{{ $entity->name }}</h3>
                                <p class="text-xs text-slate-500">{{ $entity->code }}</p>
                            </div>
                            <x-ui.badge :status="$entity->is_active ? 'paid' : 'cancelled'">{{ $entity->is_active ? 'active' : 'inactive' }}</x-ui.badge>
                        </div>

                        <form method="POST" action="{{ route('entities.update', $entity) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-3 md:grid-cols-2">
                                <x-ui.input name="name" label="Name" :value="$entity->name" />
                                <x-ui.input name="email" label="Email" type="email" :value="$entity->email" />
                                <x-ui.input name="currency" label="Currency" :value="$entity->currency" />
                                <x-ui.input name="invoice_prefix" label="Invoice Prefix" :value="$entity->invoice_prefix" />
                                <x-ui.input name="default_payment_terms" label="Payment Terms" type="number" :value="$entity->default_payment_terms" />
                                <x-ui.input name="reminder_days" label="Reminder Days" :value="implode(',', $entity->reminder_days ?? [])" />
                            </div>

                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-500" @checked($entity->is_active)>
                                Active entity
                            </label>

                            <div class="flex gap-2">
                                <x-ui.button type="submit">Save</x-ui.button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('entities.destroy', $entity) }}">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="danger">Deactivate</x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @endforeach

            <div>{{ $entities->links() }}</div>
        </div>
    </div>
</x-app-layout>
