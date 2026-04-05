<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Invoice {{ $invoice->invoice_number }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 text-slate-900">
        @php($isPayable = in_array($invoice->status, ['sent', 'partial', 'overdue'], true) && (float) $invoice->amount_due > 0)

        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @if(session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

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

                @if($isPayable)
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Online Payment</p>
                                <h2 class="mt-1 text-lg font-semibold text-slate-900">QRIS Checkout</h2>
                                <p class="mt-1 text-sm text-slate-600">Start a payment request for the current outstanding balance and refresh the status until the invoice is settled.</p>
                            </div>
                            @if($paymentData)
                                <x-ui.badge :status="$paymentData['status']">{{ $paymentData['status'] }}</x-ui.badge>
                            @endif
                        </div>

                        @if($paymentData && $paymentData['qr_string'])
                            <div class="mt-4 grid gap-6 lg:grid-cols-[240px,1fr] lg:items-start">
                                <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                                    {!! QrCode::size(220)->margin(1)->generate($paymentData['qr_string']) !!}
                                </div>
                                <div class="space-y-3 text-sm text-slate-700">
                                    <p id="payment-status-text">Status: {{ $paymentData['status'] }} @if($paymentData['gateway_status']) / {{ $paymentData['gateway_status'] }} @endif</p>
                                    <p>Reference: <span class="font-mono text-xs">{{ $paymentData['reference'] }}</span></p>
                                    <p>Gateway: {{ strtoupper($paymentData['gateway']) }}</p>
                                    @if($paymentData['expires_at'])
                                        <p>Expires At: {{ \Illuminate\Support\Carbon::parse($paymentData['expires_at'])->format('Y-m-d H:i') }}</p>
                                    @endif
                                    <div class="flex flex-wrap gap-3">
                                        @if($paymentData['qr_url'])
                                            <a href="{{ $paymentData['qr_url'] }}" target="_blank" class="rounded-md bg-slate-100 px-4 py-2 font-medium text-slate-900">Open Provider QR</a>
                                        @endif
                                        <button id="refresh-payment-status" type="button" class="rounded-md bg-slate-900 px-4 py-2 font-medium text-white">Refresh Status</button>
                                    </div>
                                </div>
                            </div>
                        @elseif($paymentData && $paymentData['checkout_url'])
                            <div class="mt-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                                <p class="text-sm text-slate-700">This gateway returns a hosted checkout page for the payment. Open it and complete the QRIS flow there.</p>
                                <div class="mt-3 flex flex-wrap gap-3">
                                    <a href="{{ $paymentData['checkout_url'] }}" target="_blank" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Open Checkout</a>
                                    <button id="refresh-payment-status" type="button" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-medium text-slate-900">Refresh Status</button>
                                </div>
                            </div>
                        @else
                            <form method="POST" action="{{ route('invoices.public.payments.store', ['token' => $invoice->public_token]) }}" class="mt-4">
                                @csrf
                                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Generate QRIS Payment</button>
                            </form>
                        @endif
                    </div>
                @endif

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
                    <a href="{{ route('invoices.public.download', ['token' => $invoice->public_token]) }}" class="rounded-md bg-emerald-100 px-4 py-2 text-sm font-medium text-emerald-900">Download PDF</a>
                    <button onclick="window.print()" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Print</button>
                </div>
            </div>
        </div>

        @if($paymentData && $paymentData['status'] === 'pending')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const statusUrl = @json(route('invoices.public.payments.status', ['token' => $invoice->public_token]));
                    const refreshButton = document.getElementById('refresh-payment-status');
                    const statusText = document.getElementById('payment-status-text');

                    const syncStatus = async () => {
                        try {
                            const response = await fetch(statusUrl, {
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });

                            if (!response.ok) {
                                return;
                            }

                            const payload = await response.json();
                            const payment = payload.data.payment;

                            if (payment && statusText) {
                                statusText.textContent = `Status: ${payment.status}${payment.gateway_status ? ' / ' + payment.gateway_status : ''}`;
                            }

                            if (payload.data.invoice_status === 'paid' || (payment && payment.status !== 'pending')) {
                                window.location.reload();
                            }
                        } catch (error) {
                            console.debug('Payment status sync failed', error);
                        }
                    };

                    refreshButton?.addEventListener('click', syncStatus);
                    window.setInterval(syncStatus, 15000);
                });
            </script>
        @endif
    </body>
</html>
