<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60 transition hover:bg-slate-50">
                <i class="ph ph-arrow-left text-xl text-slate-600"></i>
            </a>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Invoice Details</h2>
        </div>
    </x-slot>

    <div class="grid items-start gap-6 xl:grid-cols-3">
        <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm xl:col-span-2">
            <div class="flex flex-col gap-6 border-b border-slate-100 pb-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-4">
                        <h3 class="text-2xl font-bold tracking-tight text-slate-900">{{ $invoice->invoice_number }}</h3>
                        <x-ui.badge :status="$invoice->status" class="rounded-lg px-2.5 py-1 text-xs font-bold">{{ $invoice->status }}</x-ui.badge>
                    </div>
                    <p class="mt-1.5 text-sm font-medium text-slate-500">{{ $invoice->customer?->name ?? '-' }} <span class="mx-1 text-slate-300">&bull;</span> {{ $invoice->currency }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('invoices.pdf', $invoice) }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-white px-3.5 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50 hover:shadow">
                        <i class="ph ph-download-simple text-sm"></i> PDF
                    </a>
                    @if($invoice->status === 'draft')
                    <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="m-0">
                        @csrf
                        <x-ui.button type="submit" class="h-9 rounded-xl px-3.5 text-xs font-bold shadow-sm">
                            <i class="ph ph-paper-plane-tilt mr-1.5 text-sm"></i> Send
                        </x-ui.button>
                    </form>
                    @endif
                    @can('create', \App\Models\Invoice::class)
                    <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}" class="m-0">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" class="h-9 rounded-xl px-3.5 text-xs font-bold shadow-sm">
                            <i class="ph ph-copy mr-1.5 text-sm"></i> Duplicate
                        </x-ui.button>
                    </form>
                    @endcan
                    @if(!in_array($invoice->status, ['paid', 'void', 'cancelled']))
                    <form method="POST" action="{{ route('invoices.void', $invoice) }}" class="m-0">
                        @csrf
                        <x-ui.button type="submit" variant="danger" class="h-9 rounded-xl px-3.5 text-xs font-bold shadow-sm">
                            Void
                        </x-ui.button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bill To</p>
                    <p class="mt-3 text-base font-bold text-slate-900">{{ $invoice->customer?->name ?? '-' }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-600">{{ $invoice->customer?->email ?? '-' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $invoice->customer?->billing_address ?? '-' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Summary</p>
                    <div class="mt-3 space-y-2 text-sm font-medium">
                        <div class="flex items-center justify-between text-slate-600"><dt>Invoice Date</dt><dd class="text-slate-900">{{ $invoice->invoice_date?->format('d M Y') ?? $invoice->invoice_date }}</dd></div>
                        <div class="flex items-center justify-between text-slate-600"><dt>Due Date</dt><dd class="text-slate-900">{{ $invoice->due_date?->format('d M Y') ?? $invoice->due_date }}</dd></div>
                        <div class="flex items-center justify-between text-slate-600"><dt>Amount Paid</dt><dd class="text-slate-900">{{ number_format((float) $invoice->amount_paid, 2) }}</dd></div>
                        <div class="flex items-center justify-between text-slate-600"><dt>Amount Due</dt><dd class="font-bold text-slate-900">{{ number_format((float) $invoice->amount_due, 2) }}</dd></div>
                        <div class="mt-2 border-t border-slate-200/60 pt-2 text-xs text-slate-500"><span class="block mb-1">Public Token:</span><span class="break-all font-mono">{{ $invoice->public_token }}</span></div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h4 class="mb-4 text-sm font-bold tracking-tight text-slate-900">Line Items</h4>
                <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white">
                    <div class="overflow-x-auto">
                        <x-ui.table class="w-full text-sm">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Description</th>
                                    <th class="px-5 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Qty</th>
                                    <th class="px-5 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Unit Price</th>
                                    <th class="px-5 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Tax</th>
                                    <th class="px-5 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/80">
                                @foreach($invoice->items as $item)
                                    <tr class="transition hover:bg-slate-50/50">
                                        <td class="px-5 py-3.5">
                                            <p class="font-bold text-slate-900">{{ $item->description }}</p>
                                            <p class="mt-0.5 text-[11px] font-medium text-slate-500">{{ $item->discount_type ? ucfirst($item->discount_type).' discount' : 'No discount' }}</p>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-medium text-slate-700">{{ $item->quantity }}</td>
                                        <td class="px-5 py-3.5 text-right font-medium text-slate-700">{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="px-5 py-3.5 text-right font-medium text-slate-700">{{ number_format((float) $item->tax_amount, 2) }}</td>
                                        <td class="px-5 py-3.5 text-right font-bold text-slate-900">{{ number_format((float) $item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <h4 class="mb-4 text-sm font-bold tracking-tight text-slate-900">Totals Calculation</h4>
                <dl class="space-y-3 text-sm font-medium text-slate-600">
                    <div class="flex items-center justify-between"><dt>Subtotal</dt><dd class="text-slate-900">{{ number_format((float) $invoice->subtotal, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt>Discount</dt><dd class="text-rose-600">-{{ number_format((float) $invoice->discount_total, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt>Tax</dt><dd class="text-slate-900">{{ number_format((float) $invoice->tax_total, 2) }}</dd></div>
                    <div class="flex items-center justify-between border-t border-slate-200/60 pt-3 text-base font-bold text-slate-900"><dt>Grand Total</dt><dd>{{ number_format((float) $invoice->grand_total, 2) }}</dd></div>
                    <div class="mt-4 flex items-center justify-between rounded-lg bg-emerald-50 px-3 py-2 text-emerald-700"><dt>Paid</dt><dd class="font-bold">{{ number_format((float) $invoice->amount_paid, 2) }}</dd></div>
                    <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2 text-amber-700"><dt>Due</dt><dd class="font-bold">{{ number_format((float) $invoice->amount_due, 2) }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <h4 class="mb-4 text-sm font-bold tracking-tight text-slate-900">Activity History</h4>
                <div class="space-y-3">
                    @forelse($invoice->statusHistories as $history)
                        <div class="relative pl-4">
                            <div class="absolute left-0 top-1.5 h-1.5 w-1.5 rounded-full bg-indigo-400 ring-4 ring-indigo-50"></div>
                            <p class="text-sm font-bold text-slate-900">{{ ucfirst($history->from_status ?? 'created') }} &rarr; {{ ucfirst($history->to_status) }}</p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $history->notes ?? 'Status updated automatically' }}</p>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center rounded-xl border border-slate-100 bg-slate-50 py-6 text-center">
                            <i class="ph ph-clock-counter-clockwise mb-2 text-2xl text-slate-300"></i>
                            <p class="text-xs font-medium text-slate-500">No history available yet.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <h4 class="mb-4 text-sm font-bold tracking-tight text-slate-900">Public Access</h4>
                <div class="space-y-2">
                    <a href="{{ route('invoices.public.show', ['token' => $invoice->public_token]) }}" target="_blank" class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                        View Public Page
                        <i class="ph ph-arrow-up-right text-slate-400"></i>
                    </a>
                    <a href="{{ route('invoices.public.download', ['token' => $invoice->public_token]) }}" target="_blank" class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                        Download Public PDF
                        <i class="ph ph-download-simple text-slate-400"></i>
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>