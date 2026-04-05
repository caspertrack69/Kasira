<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
                <i class="ph ph-receipt text-xl text-slate-600"></i>
            </div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Credit Notes</h2>
        </div>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid items-start gap-6 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-1">
            <x-ui.card class="rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
                <button type="button" @click="createOpen = !createOpen" class="group flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold tracking-tight text-slate-900">Credit Note Actions</h3>
                        <p class="mt-0.5 text-[11px] font-medium text-slate-500">Issue nota kredit baru</p>
                    </div>
                    <span class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm shadow-slate-900/20 transition-all hover:bg-slate-800">
                        <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                        Issue Note
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
                    <h3 class="mb-5 text-sm font-bold tracking-tight text-slate-900">Issue Credit Note</h3>
                    <form method="POST" action="{{ route('credit-notes.store') }}" class="space-y-4">
                        @csrf
                        <div class="space-y-4">
                            <x-ui.select name="invoice_id" label="Invoice" :options="$invoices->mapWithKeys(fn ($invoice) => [$invoice->id => $invoice->invoice_number.' - '.$invoice->customer?->name])->all()" />
                            <x-ui.input name="amount" label="Amount" type="number" step="0.01" min="0" />
                            <x-ui.textarea name="reason" label="Reason" rows="4" />
                        </div>
                        <div class="pt-2">
                            <x-ui.button type="submit" class="w-full justify-center rounded-xl py-2.5">Issue Credit Note</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>

        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm xl:col-span-2">
            <div class="border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">Credit Note Register</h3>
            </div>
            
            <div class="overflow-x-auto">
                <x-ui.table class="w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Credit Note</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Invoice Ref</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Reason</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Amount</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80 bg-white">
                        @forelse($creditNotes as $creditNote)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-500/20">
                                            <i class="ph ph-file-text text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $creditNote->credit_note_number }}</p>
                                            <p class="text-[11px] font-medium text-slate-500">{{ $creditNote->customer?->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-700">
                                    {{ $creditNote->invoice?->invoice_number ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <span class="line-clamp-2 max-w-xs" title="{{ $creditNote->reason }}">
                                        {{ $creditNote->reason }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <x-ui.badge :status="$creditNote->status" class="rounded-lg px-2.5 py-1 text-[11px] font-semibold">
                                        {{ $creditNote->status }}
                                    </x-ui.badge>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-slate-900">
                                    {{ number_format((float) $creditNote->amount, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a href="{{ route('credit-notes.pdf', $creditNote) }}" class="inline-flex h-8 items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-200">
                                        <i class="ph ph-download-simple mr-1.5 text-sm"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="ph ph-folder-open mb-3 text-4xl text-slate-300"></i>
                                        <p class="text-sm font-medium text-slate-500">No credit notes issued yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            @if($creditNotes->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">
                    {{ $creditNotes->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>