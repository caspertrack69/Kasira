<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Invoice {{ $invoice->invoice_number }}</h2></x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold">{{ $invoice->invoice_number }}</h3>
                        <x-ui.badge :status="$invoice->status">{{ $invoice->status }}</x-ui.badge>
                    </div>
                    <p class="text-sm text-slate-500">{{ $invoice->customer?->name ?? '-' }} - {{ $invoice->currency }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('invoices.send', $invoice) }}">
                        @csrf
                        <x-ui.button type="submit">Send</x-ui.button>
                    </form>
                    <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">Duplicate</x-ui.button>
                    </form>
                    <form method="POST" action="{{ route('invoices.void', $invoice) }}">
                        @csrf
                        <x-ui.button type="submit" variant="danger">Void</x-ui.button>
                    </form>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bill To</p>
                    <p class="mt-2 font-semibold">{{ $invoice->customer?->name ?? '-' }}</p>
                    <p class="text-sm text-slate-600">{{ $invoice->customer?->email ?? '-' }}</p>
                    <p class="text-sm text-slate-600">{{ $invoice->customer?->billing_address ?? '-' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Summary</p>
                    <div class="mt-2 space-y-1 text-sm">
                        <p>Invoice Date: {{ $invoice->invoice_date?->format('Y-m-d') ?? $invoice->invoice_date }}</p>
                        <p>Due Date: {{ $invoice->due_date?->format('Y-m-d') ?? $invoice->due_date }}</p>
                        <p>Amount Paid: {{ number_format((float) $invoice->amount_paid, 2) }}</p>
                        <p>Amount Due: {{ number_format((float) $invoice->amount_due, 2) }}</p>
                        <p>Public Token: <span class="break-all text-xs text-slate-500">{{ $invoice->public_token }}</span></p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="mb-3 text-sm font-semibold">Line Items</h4>
                <x-ui.table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Description</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Unit Price</th>
                            <th class="px-3 py-2 text-right">Tax</th>
                            <th class="px-3 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-3 py-2">
                                    <p class="font-medium">{{ $item->description }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->discount_type ? $item->discount_type.' discount' : 'No discount' }}</p>
                                </td>
                                <td class="px-3 py-2 text-right">{{ $item->quantity }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $item->tax_amount, 2) }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            </div>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card>
                <h4 class="mb-3 text-sm font-semibold">Totals</h4>
                <dl class="space-y-2 text-sm">
                    <div class="flex items-center justify-between"><dt>Subtotal</dt><dd>{{ number_format((float) $invoice->subtotal, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt>Discount</dt><dd>{{ number_format((float) $invoice->discount_total, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt>Tax</dt><dd>{{ number_format((float) $invoice->tax_total, 2) }}</dd></div>
                    <div class="flex items-center justify-between border-t border-slate-200 pt-2 font-semibold"><dt>Grand Total</dt><dd>{{ number_format((float) $invoice->grand_total, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt>Paid</dt><dd>{{ number_format((float) $invoice->amount_paid, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt>Due</dt><dd>{{ number_format((float) $invoice->amount_due, 2) }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h4 class="mb-3 text-sm font-semibold">History</h4>
                <div class="space-y-3">
                    @forelse($invoice->statusHistories as $history)
                        <div class="rounded-md border border-slate-200 p-3 text-sm">
                            <p class="font-medium">{{ $history->from_status ?? 'created' }} -> {{ $history->to_status }}</p>
                            <p class="text-xs text-slate-500">{{ $history->notes ?? 'Status updated' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No history yet.</p>
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card>
                <h4 class="mb-3 text-sm font-semibold">Public View</h4>
                <a class="text-sm text-slate-900 underline" href="{{ route('invoices.public.show', ['token' => $invoice->public_token]) }}" target="_blank">Open public invoice page</a>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
