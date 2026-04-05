<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Items & Services</h2></x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4">
            <x-ui.card>
                <button type="button" @click="createOpen = !createOpen" class="flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold">Item Actions</h3>
                        <p class="text-xs text-slate-500">Tambah item atau service baru.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">
                        <i class="ph ph-plus"></i>
                        Tambah Item
                    </span>
                </button>
            </x-ui.card>

            <x-ui.card x-show="createOpen" x-cloak x-transition>
                <h3 class="mb-3 text-sm font-semibold">Create Item</h3>
                <form method="POST" action="{{ route('items.store') }}" class="space-y-3">
                    @csrf
                    <x-ui.input name="sku" label="SKU" />
                    <x-ui.input name="name" label="Name" />
                    <x-ui.input name="default_price" label="Price" type="number" step="0.01" />
                    <x-ui.input name="unit" label="Unit" value="unit" />
                    <x-ui.select name="tax_id" label="Tax" :options="$taxes->pluck('name','id')->all()" />
                    <x-ui.button type="submit">Save</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <x-ui.card class="lg:col-span-2">
            <x-ui.table>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">SKU</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-right">Price</th>
                        <th class="px-3 py-2 text-left">Tax</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $item)
                        <tr>
                            <td class="px-3 py-2">{{ $item->sku }}</td>
                            <td class="px-3 py-2">{{ $item->name }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($item->default_price, 2) }}</td>
                            <td class="px-3 py-2">{{ $item->tax?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
            <div class="mt-3">{{ $items->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
