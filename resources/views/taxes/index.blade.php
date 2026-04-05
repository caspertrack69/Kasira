<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Taxes</h2></x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4">
            <x-ui.card>
                <button type="button" @click="createOpen = !createOpen" class="flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold">Tax Actions</h3>
                        <p class="text-xs text-slate-500">Buka form saat mau menambah pajak.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">
                        <i class="ph ph-plus"></i>
                        Tambah Tax
                    </span>
                </button>
            </x-ui.card>

            <x-ui.card x-show="createOpen" x-cloak x-transition>
                <h3 class="mb-3 text-sm font-semibold">Add Tax</h3>
                <form method="POST" action="{{ route('taxes.store') }}" class="space-y-3">
                    @csrf
                    <x-ui.input name="name" label="Name" />
                    <x-ui.input name="code" label="Code" />
                    <x-ui.select name="type" label="Type" :options="['exclusive' => 'Exclusive', 'inclusive' => 'Inclusive']" />
                    <x-ui.input name="rate" label="Rate (%)" type="number" step="0.01" />
                    <x-ui.button type="submit">Save</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <x-ui.card class="lg:col-span-2">
            <x-ui.table>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-right">Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($taxes as $tax)
                        <tr>
                            <td class="px-3 py-2">{{ $tax->name }}</td>
                            <td class="px-3 py-2">{{ $tax->type }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($tax->rate, 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
            <div class="mt-3">{{ $taxes->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
