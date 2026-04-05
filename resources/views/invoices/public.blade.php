<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-shadow-none { shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased selection:bg-indigo-100">

    @php($isPayable = in_array($invoice->status, ['sent', 'partial', 'overdue'], true) && (float) $invoice->amount_due > 0)

    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        
        <div class="no-print mb-6 flex items-center justify-between">
            <a href="#" class="group flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
                <i class="ph ph-arrow-left transition group-hover:-translate-x-1"></i>
                Back to Portal
            </a>
            <div class="flex gap-3">
                <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                    <i class="ph ph-printer"></i> Print
                </button>
                <a href="{{ route('invoices.public.download', ['token' => $invoice->public_token]) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
                    <i class="ph ph-download-simple"></i> Download PDF
                </a>
            </div>
        </div>

        <main class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-200/50">
            
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Invoice Status</span>
                    <x-ui.badge :status="$invoice->status" class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-tighter">{{ $invoice->status }}</x-ui.badge>
                </div>
                <div class="text-right">
                    <span class="text-xs font-medium text-slate-500 italic">Generated on {{ now()->format('d M Y') }}</span>
                </div>
            </div>

            <div class="p-8 sm:p-12">
                <div class="flex flex-col justify-between gap-8 md:flex-row md:items-start">
                    <div>
                        <div class="mb-6 h-12 w-12 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold">
                            {{ substr($invoice->entity->name ?? config('app.name'), 0, 1) }}
                        </div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">
                            {{ $invoice->invoice_number }}
                        </h1>
                        <p class="mt-1 font-medium text-slate-500">{{ $invoice->entity->name ?? config('app.name') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-x-12 gap-y-6 md:text-right">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Date Issued</p>
                            <p class="text-sm font-bold text-slate-900">{{ $invoice->invoice_date?->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Due Date</p>
                            <p class="text-sm font-bold text-rose-600">{{ $invoice->due_date?->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <hr class="my-10 border-slate-100">

                <div class="grid gap-12 md:grid-cols-2">
                    <div>
                        <p class="mb-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Billed To</p>
                        <h3 class="text-lg font-bold text-slate-900">{{ $invoice->customer->name ?? '-' }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-slate-500">
                            {{ $invoice->customer->email }}<br>
                            {{ $invoice->customer->billing_address ?? 'No address provided' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100">
                        <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-indigo-600">Total Amount Due</p>
                        <h2 class="text-3xl font-black text-indigo-900">
                            <span class="text-lg font-medium tracking-tight">{{ $invoice->currency }}</span>
                            {{ number_format((float) $invoice->amount_due, 2) }}
                        </h2>
                        <div class="mt-4 flex flex-col gap-2 border-t border-indigo-200/50 pt-4 text-xs font-bold text-indigo-700/70">
                            <div class="flex justify-between">
                                <span>Total Invoiced</span>
                                <span>{{ number_format((float) $invoice->grand_total, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Paid</span>
                                <span class="text-emerald-600">- {{ number_format((float) $invoice->amount_paid, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 overflow-hidden rounded-xl border border-slate-100">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Description</th>
                                <th class="px-6 py-4 text-center">Qty</th>
                                <th class="px-6 py-4 text-right">Price</th>
                                <th class="px-6 py-4 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($invoice->items as $item)
                            <tr class="group transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-5">
                                    <p class="font-bold text-slate-900">{{ $item->description }}</p>
                                </td>
                                <td class="px-6 py-5 text-center font-medium text-slate-600">{{ $item->quantity }}</td>
                                <td class="px-6 py-5 text-right font-medium text-slate-600">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-6 py-5 text-right font-bold text-slate-900">{{ number_format((float) $item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex flex-col gap-8 lg:flex-row lg:justify-between">
                    <div class="max-w-md">
                        @if($invoice->notes)
                            <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Notes & Terms</h4>
                            <p class="mt-2 text-xs leading-relaxed text-slate-500">{{ $invoice->notes }}</p>
                        @endif
                    </div>
                    <div class="w-full lg:w-64">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-slate-500">
                                <span>Subtotal</span>
                                <span class="font-semibold text-slate-900">{{ number_format((float) $invoice->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>Tax</span>
                                <span class="font-semibold text-slate-900">{{ number_format((float) $invoice->tax_total, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-slate-100 pt-3 text-base font-black text-slate-900">
                                <span>Grand Total</span>
                                <span>{{ number_format((float) $invoice->grand_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($isPayable)
            <div class="no-print border-t border-slate-100 bg-slate-50/50 p-8 sm:p-12">
                <div class="mb-8 flex items-center gap-4">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secure Payment Options</h3>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="flex flex-col rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-600">
                                <i class="ph ph-qr-code text-lg"></i> Online Payment
                            </span>
                        </div>
                        
                        @if($paymentData && $paymentData['qr_string'])
                            <div class="flex flex-col items-center sm:flex-row sm:items-start gap-6">
                                <div class="shrink-0 rounded-xl border border-slate-100 p-2 shadow-inner">
                                    {!! QrCode::size(140)->margin(1)->generate($paymentData['qr_string']) !!}
                                </div>
                                <div class="flex-1 space-y-3">
                                    <p class="text-xs font-medium text-slate-500">Scan QRIS menggunakan Mobile Banking atau E-Wallet favorit Anda.</p>
                                    <div class="rounded-lg bg-slate-50 p-3 text-[11px] font-mono leading-tight">
                                        <p class="text-slate-400">Ref: {{ $paymentData['reference'] }}</p>
                                        <p class="mt-1 font-bold text-slate-700">Status: {{ strtoupper($paymentData['status']) }}</p>
                                    </div>
                                    <button id="refresh-payment-status" class="w-full rounded-lg bg-slate-900 py-2 text-xs font-bold text-white transition hover:bg-slate-800">
                                        Check Payment Status
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="mt-auto">
                                <p class="mb-4 text-sm font-medium text-slate-600 text-center lg:text-left">Instantly pay using QRIS or Credit Card for faster processing.</p>
                                <form method="POST" action="{{ route('invoices.public.payments.store', ['token' => $invoice->public_token]) }}">
                                    @csrf
                                    <button class="w-full rounded-xl bg-indigo-600 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 hover:shadow-none">
                                        Generate Payment Request
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <span class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-600">
                            <i class="ph ph-bank text-lg"></i> Manual Transfer
                        </span>
                        
                        @if($bankTransferMethods->isNotEmpty())
                            <div class="space-y-4">
                                @foreach($bankTransferMethods as $method)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                    <div class="flex justify-between">
                                        <p class="text-xs font-bold text-slate-900">{{ $method->name }}</p>
                                        <span class="text-[10px] font-black uppercase text-slate-400 italic">Account Detail</span>
                                    </div>
                                    <p class="mt-1 font-mono text-sm font-black text-indigo-600 tracking-wider">{{ $method->account_number }}</p>
                                    <p class="text-[10px] font-medium text-slate-500 uppercase">A/N: {{ $method->account_name }}</p>
                                </div>
                                @endforeach

                                <button onclick="document.getElementById('transfer-modal').showModal()" class="w-full rounded-xl border-2 border-dashed border-slate-200 py-3 text-sm font-bold text-slate-500 transition hover:border-emerald-500 hover:text-emerald-600">
                                    Submit Transfer Proof
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </main>
        
        <footer class="mt-8 text-center no-print">
            <p class="text-sm font-medium text-slate-400">
                Powered by <span class="font-bold text-slate-600">{{ config('app.name') }}</span> &bull; Secure Invoice System
            </p>
        </footer>
    </div>

    <dialog id="transfer-modal" class="rounded-3xl p-0 shadow-2xl backdrop:backdrop-blur-sm">
        <div class="w-[500px] max-w-full bg-white p-8">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-900">Upload Proof</h3>
                <button onclick="document.getElementById('transfer-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <i class="ph ph-x text-2xl"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('invoices.public.payments.bank-transfer.store', ['token' => $invoice->public_token]) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid gap-4">
                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase text-slate-400 tracking-widest">Bank Destination</label>
                        <select name="payment_method_id" class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-emerald-500">
                            @foreach($bankTransferMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase text-slate-400 tracking-widest">Upload Receipt</label>
                        <input type="file" name="proof" class="w-full text-sm font-medium text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-xs file:font-bold file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                </div>
                <button class="mt-4 w-full rounded-xl bg-emerald-600 py-3 font-bold text-white shadow-lg shadow-emerald-100">Confirm Transfer</button>
            </form>
        </div>
    </dialog>

    @if($paymentData && $paymentData['status'] === 'pending')
    <script>
        // ... (Script auto-refresh Anda sudah bagus, bisa dipertahankan)
    </script>
    @endif
</body>
</html>