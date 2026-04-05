<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Items & Services</h2>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900">Item Directory</h3>
                    <p class="mt-0.5 text-xs font-medium text-slate-500">Tambah item langsung dari wrapper tabel.</p>
                </div>
                <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm shadow-slate-900/20 transition hover:bg-slate-800">
                    <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                    Tambah Item
                </button>
            </div>

            <div x-show="createOpen" x-cloak class="border-b border-slate-100 bg-slate-50/50 p-6">
                <form method="POST" action="{{ route('items.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ui.input name="sku" label="SKU" />
                        <x-ui.input name="name" label="Name" />
                        <x-ui.input name="default_price" label="Price" type="number" step="0.01" />
                        <x-ui.input name="unit" label="Unit" value="unit" />
                        <div class="md:col-span-2"><x-ui.select name="tax_id" label="Tax" :options="$taxes->pluck('name','id')->all()" /></div>
                    </div>
                    <x-ui.button type="submit" class="rounded-xl px-5 py-2.5">Save Item</x-ui.button>
                </form>
            </div>

            <div class="divide-y divide-slate-100/80">
                @forelse($items as $item)
                    <div x-data="{ editOpen: false }" class="bg-white">
                        <div class="flex items-center justify-between gap-4 px-6 py-4 transition-colors hover:bg-slate-50/30">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-500/20"><i class="ph ph-box-cube text-lg"></i></div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $item->name }}</p>
                                    <p class="font-mono text-[10px] uppercase tracking-wider text-slate-500">{{ $item->sku ?: 'NO SKU' }} <span class="mx-1 font-sans text-slate-300">&bull;</span> {{ $item->unit }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($item->tax)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-600/20">{{ $item->tax->name }}</span>
                                @endif
                                <span class="text-sm font-bold text-slate-900">{{ number_format($item->default_price, 2) }}</span>
                                <button type="button" @click="editOpen = !editOpen" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                    <i class="ph ph-pencil-simple text-base"></i>
                                </button>
                                <form method="POST" action="{{ route('items.destroy', $item) }}" class="m-0" onsubmit="return confirm('Hapus item {{ addslashes($item->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                        <i class="ph ph-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div x-show="editOpen" x-cloak class="border-t border-slate-100 bg-slate-50/50 px-6 py-5">
                            <form method="POST" action="{{ route('items.update', $item) }}" class="space-y-4">
                                @csrf @method('PUT')
                                <div class="grid gap-4 md:grid-cols-2">
                                    <x-ui.input name="sku" label="SKU" :value="$item->sku" />
                                    <x-ui.input name="name" label="Name" :value="$item->name" />
                                    <x-ui.input name="default_price" label="Price" type="number" step="0.01" :value="$item->default_price" />
                                    <x-ui.input name="unit" label="Unit" :value="$item->unit" />
                                    <div class="md:col-span-2"><x-ui.select name="tax_id" label="Tax" :options="$taxes->pluck('name','id')->all()" :selected="$item->tax_id" /></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-ui.button type="submit" class="rounded-xl px-5 py-2">Save Changes</x-ui.button>
                                    <x-ui.button type="button" variant="secondary" @click="editOpen = false" class="rounded-xl px-5 py-2">Cancel</x-ui.button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-12 text-slate-400">
                        <i class="ph ph-package mb-3 text-4xl text-slate-300"></i>
                        <p class="text-sm font-medium text-slate-500">No items or services found.</p>
                    </div>
                @endforelse
            </div>

            @if($items->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">{{ $items->links() }}</div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
