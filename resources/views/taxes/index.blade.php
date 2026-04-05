<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Taxes</h2>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">Tax Master</h3>
                <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                    Tambah Tax
                </button>
            </div>

            <div x-show="createOpen" x-cloak class="border-b border-slate-100 bg-slate-50/50 p-6">
                <form method="POST" action="{{ route('taxes.store') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <x-ui.input name="name" label="Name" />
                    <x-ui.input name="code" label="Code" />
                    <x-ui.select name="type" label="Type" :options="['exclusive' => 'Exclusive', 'inclusive' => 'Inclusive']" />
                    <x-ui.input name="rate" label="Rate (%)" type="number" step="0.01" />
                    <div class="md:col-span-2"><x-ui.button type="submit" class="rounded-xl px-5 py-2.5">Save Tax</x-ui.button></div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <x-ui.table class="w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50"><tr><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Tax Info</th><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Type</th><th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Rate</th></tr></thead>
                    <tbody class="divide-y divide-slate-100/80 bg-white">
                        @forelse($taxes as $tax)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-500/20"><i class="ph ph-receipt text-lg"></i></div><div><p class="font-bold text-slate-900">{{ $tax->name }}</p><p class="font-mono text-[10px] uppercase tracking-wider text-slate-500">{{ $tax->code ?? '-' }}</p></div></div></td>
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold capitalize {{ $tax->type === 'inclusive' ? 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' }}">{{ $tax->type }}</span></td>
                                <td class="px-6 py-4 text-right"><span class="text-sm font-bold text-slate-900">{{ number_format($tax->rate, 2) }}%</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center"><div class="flex flex-col items-center justify-center text-slate-400"><i class="ph ph-folder-open mb-3 text-4xl text-slate-300"></i><p class="text-sm font-medium text-slate-500">No tax records found.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            @if($taxes->hasPages())<div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">{{ $taxes->links() }}</div>@endif
        </x-ui.card>
    </div>
</x-app-layout>


