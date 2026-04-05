<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Credit Notes</h2>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">Credit Note Register</h3>
                <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800"><i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>Issue Credit Note</button>
            </div>

            <div x-show="createOpen" x-cloak class="border-b border-slate-100 bg-slate-50/50 p-6">
                <form method="POST" action="{{ route('credit-notes.store') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <div class="md:col-span-2"><x-ui.select name="invoice_id" label="Invoice" :options="$invoices->mapWithKeys(fn ($invoice) => [$invoice->id => $invoice->invoice_number.' - '.$invoice->customer?->name])->all()" /></div>
                    <x-ui.input name="amount" label="Amount" type="number" step="0.01" min="0" />
                    <div class="md:col-span-2"><x-ui.textarea name="reason" label="Reason" rows="3" /></div>
                    <div class="md:col-span-2"><x-ui.button type="submit" class="rounded-xl px-5 py-2.5">Issue Credit Note</x-ui.button></div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <x-ui.table class="w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50"><tr><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Credit Note</th><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Invoice Ref</th><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Reason</th><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th><th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Amount</th><th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Action</th></tr></thead>
                    <tbody class="divide-y divide-slate-100/80 bg-white">
                        @forelse($creditNotes as $creditNote)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-500/20"><i class="ph ph-file-text text-lg"></i></div><div><p class="font-bold text-slate-900">{{ $creditNote->credit_note_number }}</p><p class="text-[11px] font-medium text-slate-500">{{ $creditNote->customer?->name ?? '-' }}</p></div></div></td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-700">{{ $creditNote->invoice?->invoice_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-600"><span class="line-clamp-2 max-w-xs" title="{{ $creditNote->reason }}">{{ $creditNote->reason }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4"><x-ui.badge :status="$creditNote->status" class="rounded-lg px-2.5 py-1 text-[11px] font-semibold">{{ $creditNote->status }}</x-ui.badge></td>
                                <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-slate-900">{{ number_format((float) $creditNote->amount, 2) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right"><a href="{{ route('credit-notes.pdf', $creditNote) }}" class="inline-flex h-8 items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-200"><i class="ph ph-download-simple mr-1.5 text-sm"></i> PDF</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center"><div class="flex flex-col items-center justify-center text-slate-400"><i class="ph ph-folder-open mb-3 text-4xl text-slate-300"></i><p class="text-sm font-medium text-slate-500">No credit notes issued yet.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            @if($creditNotes->hasPages())<div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">{{ $creditNotes->links() }}</div>@endif
        </x-ui.card>
    </div>
</x-app-layout>


