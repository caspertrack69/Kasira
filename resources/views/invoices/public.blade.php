<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Invoice {{ $invoice->invoice_number }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    </head>
    <body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-slate-900 selection:text-white">
        @php($isPayable = in_array($invoice->status, ['sent', 'partial', 'overdue'], true) && (float) $invoice->amount_due > 0)

        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
            <div class="overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-xl shadow-slate-200/40 sm:p-10">
                @if(session('status'))
                    <div class="mb-8 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="flex flex-wrap items-start justify-between gap-6 border-b border-slate-100 pb-8">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $invoice->entity->name ?? config('app.name') }}</p>
                        <h1 class="mt-2 text-4xl font-bold tracking-tight text-slate-900">Invoice {{ $invoice->invoice_number }}</h1>
                        <p class="mt-2 text-sm font-medium text-slate-500">{{ $invoice->entity->email ?? '' }}</p>
                    </div>
                    <div class="text-right">
                        <x-ui.badge :status="$invoice->status" class="rounded-lg px-3 py-1.5 text-xs font-bold">{{ $invoice->status }}</x-ui.badge>
                        <p class="mt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Invoice Date</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $invoice->invoice_date?->format('d M Y') ?? $invoice->invoice_date }}</p>
                        <p class="mt-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Due Date</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $invoice->due_date?->format('d M Y') ?? $invoice->due_date }}</p>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6 transition hover:bg-slate-50/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bill To</p>
                        <p class="mt-3 text-lg font-bold text-slate-900">{{ $invoice->customer->name ?? '-' }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-600">{{ $invoice->customer->email ?? '-' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $invoice->customer->billing_address ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6 transition hover:bg-slate-50/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Payment Summary</p>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex items-center justify-between font-medium text-slate-600"><dt>Currency</dt><dd class="text-slate-900">{{ $invoice->currency }}</dd></div>
                            <div class="flex items-center justify-between font-medium text-slate-600"><dt>Amount Paid</dt><dd class="text-slate-900">{{ number_format((float) $invoice->amount_paid, 2) }}</dd></div>
                            <div class="mt-3 flex items-center justify-between border-t border-slate-200/60 pt-3 text-base font-bold text-slate-900"><dt>Amount Due</dt><dd>{{ number_format((float) $invoice->amount_due, 2) }}</dd></div>
                        </dl>
                    </div>
                </div>

                @if($isPayable)
                    <div class="mt-8 rounded-3xl border border-indigo-100 bg-indigo-50/50 p-6 shadow-sm sm:p-8">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-500">Online Payment</p>
                                <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900">QRIS Checkout</h2>
                                <p class="mt-2 max-w-xl text-sm font-medium text-slate-600">Start a payment request for the current outstanding balance and refresh the status until the invoice is settled.</p>
                            </div>
                            @if($paymentData)
                                <x-ui.badge :status="$paymentData['status']" class="rounded-lg px-3 py-1.5 text-xs font-bold">{{ $paymentData['status'] }}</x-ui.badge>
                            @endif
                        </div>

                        @if($paymentData && $paymentData['qr_string'])
                            <div class="mt-6 grid gap-8 lg:grid-cols-[240px,1fr] lg:items-start">
                                <div class="overflow-hidden rounded-2xl bg-white p-4 shadow-md shadow-slate-200/50 ring-1 ring-slate-200/60">
                                    {!! QrCode::size(208)->margin(1)->generate($paymentData['qr_string']) !!}
                                </div>
                                <div class="space-y-4 text-sm font-medium text-slate-700">
                                    <div class="rounded-xl border border-white/40 bg-white/60 p-4 backdrop-blur-sm">
                                        <p id="payment-status-text" class="text-base font-bold text-slate-900">Status: {{ $paymentData['status'] }} @if($paymentData['gateway_status']) <span class="text-slate-400">/</span> {{ $paymentData['gateway_status'] }} @endif</p>
                                        <div class="mt-3 space-y-1.5">
                                            <p class="flex items-center gap-2"><span class="text-slate-500">Reference:</span> <span class="font-mono text-xs font-semibold">{{ $paymentData['reference'] }}</span></p>
                                            <p class="flex items-center gap-2"><span class="text-slate-500">Gateway:</span> <span class="font-bold">{{ strtoupper($paymentData['gateway']) }}</span></p>
                                            @if($paymentData['expires_at'])
                                                <p class="flex items-center gap-2"><span class="text-slate-500">Expires At:</span> <span class="font-bold">{{ \Illuminate\Support\Carbon::parse($paymentData['expires_at'])->format('d M Y, H:i') }}</span></p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        @if($paymentData['qr_url'])
                                            <a href="{{ $paymentData['qr_url'] }}" target="_blank" class="inline-flex h-10 items-center rounded-xl bg-white px-5 font-bold text-slate-900 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50 hover:shadow">Open Provider QR</a>
                                        @endif
                                        <button id="refresh-payment-status" type="button" class="inline-flex h-10 items-center rounded-xl bg-slate-900 px-5 font-bold text-white shadow-sm transition hover:bg-slate-800 hover:shadow">Refresh Status</button>
                                    </div>
                                </div>
                            </div>
                        @elseif($paymentData && $paymentData['checkout_url'])
                            <div class="mt-6 rounded-2xl border border-white/40 bg-white/60 p-5 shadow-sm backdrop-blur-sm">
                                <p class="text-sm font-medium text-slate-700">This gateway returns a hosted checkout page for the payment. Open it and complete the QRIS flow there.</p>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <a href="{{ $paymentData['checkout_url'] }}" target="_blank" class="inline-flex h-10 items-center rounded-xl bg-slate-900 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800">Open Checkout</a>
                                    <button id="refresh-payment-status" type="button" class="inline-flex h-10 items-center rounded-xl bg-white px-5 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">Refresh Status</button>
                                </div>
                            </div>
                        @else
                            <form method="POST" action="{{ route('invoices.public.payments.store', ['token' => $invoice->public_token]) }}" class="mt-6">
                                @csrf
                                <button class="inline-flex h-11 items-center rounded-xl bg-slate-900 px-6 font-bold text-white shadow-sm transition hover:bg-slate-800 hover:shadow-md">
                                    Generate QRIS Payment
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                <div class="mt-10 overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <x-ui.table class="w-full text-sm">
                            <thead class="border-b border-slate-100 bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Description</th>
                                    <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Qty</th>
                                    <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Unit Price</th>
                                    <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/80">
                                @foreach($invoice->items as $item)
                                    <tr class="transition hover:bg-slate-50/50">
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $item->description }}</td>
                                        <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 text-right font-medium text-slate-700">{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ number_format((float) $item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </div>
                </div>

                <div class="mt-8 grid gap-8 lg:grid-cols-2">
                    <div>
                        @if($invoice->notes)
                            <div class="rounded-2xl border border-slate-200/60 bg-slate-50 p-6">
                                <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Notes</h2>
                                <p class="mt-3 text-sm font-medium leading-relaxed text-slate-700">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="rounded-2xl border border-slate-200/60 bg-slate-50 p-6">
                        <dl class="space-y-3 text-sm font-medium text-slate-600">
                            <div class="flex items-center justify-between"><dt>Subtotal</dt><dd class="text-slate-900">{{ number_format((float) $invoice->subtotal, 2) }}</dd></div>
                            <div class="flex items-center justify-between"><dt>Discount</dt><dd class="text-rose-600">-{{ number_format((float) $invoice->discount_total, 2) }}</dd></div>
                            <div class="flex items-center justify-between"><dt>Tax</dt><dd class="text-slate-900">{{ number_format((float) $invoice->tax_total, 2) }}</dd></div>
                            <div class="flex items-center justify-between border-t border-slate-200/60 pt-4 text-lg font-bold text-slate-900"><dt>Total</dt><dd>{{ number_format((float) $invoice->grand_total, 2) }}</dd></div>
                        </dl>
                    </div>
                </div>

                <div class="mt-10 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-8">
                    <button onclick="window.print()" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-white px-6 font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50 hover:shadow">
                        <i class="ph ph-printer text-lg"></i> Print
                    </button>
                    <a href="{{ route('invoices.public.download', ['token' => $invoice->public_token]) }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-6 font-bold text-indigo-700 shadow-sm transition hover:bg-indigo-100">
                        <i class="ph ph-download-simple text-lg"></i> Download PDF
                    </a>
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
                                statusText.innerHTML = `Status: ${payment.status}${payment.gateway_status ? ' <span class="text-slate-400">/</span> ' + payment.gateway_status : ''}`;
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