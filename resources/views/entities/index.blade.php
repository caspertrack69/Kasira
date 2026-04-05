<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Entities</h2>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">Entity Directory</h3>
                <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800"><i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>Tambah Entity</button>
            </div>

            <div x-show="createOpen" x-cloak class="border-b border-slate-100 bg-slate-50/50 p-6">
                <form method="POST" action="{{ route('entities.store') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <x-ui.input name="name" label="Name" />
                    <x-ui.input name="code" label="Code" />
                    <x-ui.input name="email" label="Email" type="email" />
                    <x-ui.input name="currency" label="Currency" value="IDR" />
                    <x-ui.input name="invoice_prefix" label="Invoice Prefix" />
                    <x-ui.input name="default_payment_terms" label="Payment Terms" type="number" value="30" />
                    <x-ui.input name="reminder_days" label="Reminder Days" value="1,3,7" />
                    <div class="md:col-span-2"><x-ui.button type="submit" class="rounded-xl px-5 py-2.5">Create Entity</x-ui.button></div>
                </form>
            </div>

            <div class="space-y-5 p-6">
                @foreach($entities as $entity)
                    <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm transition hover:shadow-md">
                        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-500/20"><i class="ph ph-buildings text-xl"></i></div><div><h3 class="text-base font-bold tracking-tight text-slate-900">{{ $entity->name }}</h3><p class="text-xs font-medium text-slate-500">Code: <span class="uppercase tracking-wider text-slate-700">{{ $entity->code }}</span></p></div></div>
                                <x-ui.badge :status="$entity->is_active ? 'paid' : 'cancelled'" class="rounded-lg px-2.5 py-1 text-[11px] font-semibold">{{ $entity->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                            </div>
                        </div>

                        <div class="px-6 py-5">
                            <form id="update-form-{{ $entity->id }}" method="POST" action="{{ route('entities.update', $entity) }}" class="space-y-5">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-5 md:grid-cols-2">
                                    <x-ui.input name="name" label="Name" :value="$entity->name" />
                                    <x-ui.input name="email" label="Email" type="email" :value="$entity->email" />
                                    <x-ui.input name="currency" label="Currency" :value="$entity->currency" />
                                    <x-ui.input name="invoice_prefix" label="Invoice Prefix" :value="$entity->invoice_prefix" />
                                    <x-ui.input name="default_payment_terms" label="Payment Terms" type="number" :value="$entity->default_payment_terms" />
                                    <x-ui.input name="reminder_days" label="Reminder Days" :value="implode(',', $entity->reminder_days ?? [])" />
                                </div>
                            </form>

                            <div class="mt-6 border-t border-slate-100 pt-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <label class="inline-flex cursor-pointer items-center gap-2.5">
                                        <input type="hidden" form="update-form-{{ $entity->id }}" name="is_active" value="0">
                                        <input type="checkbox" form="update-form-{{ $entity->id }}" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 shadow-sm transition focus:ring-slate-900" @checked($entity->is_active)>
                                        <span class="text-sm font-medium text-slate-700">Active Entity</span>
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('entities.destroy', $entity) }}" class="m-0">@csrf @method('DELETE') <x-ui.button type="submit" variant="danger" class="rounded-xl bg-rose-50 px-4 py-2 text-rose-600 hover:bg-rose-100">Deactivate</x-ui.button></form>
                                        <x-ui.button type="submit" form="update-form-{{ $entity->id }}" class="rounded-xl px-5 py-2">Save Changes</x-ui.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            @if($entities->hasPages())<div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">{{ $entities->links() }}</div>@endif
        </x-ui.card>
    </div>
</x-app-layout>
