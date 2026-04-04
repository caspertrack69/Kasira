<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Invoice {{ $invoice->invoice_number }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 text-slate-900">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-6">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $invoice->entity->name ?? config('app.name') }}</p>
                        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Invoice {{ $invoice->invoice_number }}</h1>
                        <p class="mt-1 text-sm text-slate-500">{{ $invoice->entity->email ?? '' }}</p>
                    </div>
                    <div class="text-right">
                        <x-ui.badge :status="$invoice->status">{{ $invoice->status }}</x-ui.badge>
                        <p class="mt-3 text-sm text-slate-500">Invoice Date</p>
                        <p class="font-semibold">{{ $invoice->invoice_date?->format('Y-m-d') ?? $invoice->invoice_date }}</p>
                        <p class="mt-2 text-sm text-slate-500">Due Date</p>
                        <p class="font-semibold">{{ $invoice->due_date?->format('Y-m-d') ?? $invoice->due_date }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bill To</p>
                        <p class="mt-2 text-lg font-semibold">{{ $invoice->customer->name ?? '-' }}</p>
                        <p class="text-sm text-slate-600">{{ $invoice->customer->email ?? '-' }}</p>
                        <p class="text-sm text-slate-600">{{ $invoice->customer->billing_address ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment Summary</p>
                        <dl class="mt-2 space-y-1 text-sm">
                            <div class="flex items-center justify-between"><dt>Currency</dt><dd>{{ $invoice->currency }}</dd></div>
                            <div class="flex items-center justify-between"><dt>Amount Paid</dt><dd>{{ number_format((float) $invoice->amount_paid, 2) }}</dd></div>
                            <div class="flex items-center justify-between"><dt>Amount Due</dt><dd>{{ number_format((float) $invoice->amount_due, 2) }}</dd></div>
                        </dl>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
                    <x-ui.table>
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Description</th>
                                <th class="px-3 py-2 text-right">Qty</th>
                                <th class="px-3 py-2 text-right">Unit Price</th>
                                <th class="px-3 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-3 py-2">{{ $item->description }}</td>
                                    <td class="px-3 py-2 text-right">{{ $item->quantity }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) $item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div>
                        @if($invoice->notes)
                            <div class="rounded-xl border border-slate-200 p-4">
                                <h2 class="text-sm font-semibold">Notes</h2>
                                <p class="mt-2 text-sm text-slate-600">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <dl class="space-y-2 text-sm">
                            <div class="flex items-center justify-between"><dt>Subtotal</dt><dd>{{ number_format((float) $invoice->subtotal, 2) }}</dd></div>
                            <div class="flex items-center justify-between"><dt>Discount</dt><dd>{{ number_format((float) $invoice->discount_total, 2) }}</dd></div>
                            <div class="flex items-center justify-between"><dt>Tax</dt><dd>{{ number_format((float) $invoice->tax_total, 2) }}</dd></div>
                            <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-base font-semibold"><dt>Total</dt><dd>{{ number_format((float) $invoice->grand_total, 2) }}</dd></div>
                        </dl>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button onclick="window.print()" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Print</button>
                </div>
            </div>
        </div>
    </body>
</html>
