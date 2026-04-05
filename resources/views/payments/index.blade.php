<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
                <i class="ph ph-hand-coins text-xl text-slate-600"></i>
            </div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Payments</h2>
        </div>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid items-start gap-6 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-1">
            <x-ui.card class="rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
                <button type="button" @click="createOpen = !createOpen" class="group flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold tracking-tight text-slate-900">Payment Actions</h3>
                        <p class="mt-0.5 text-[11px] font-medium text-slate-500">Record penerimaan pembayaran</p>
                    </div>
                    <span class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm shadow-slate-900/20 transition-all hover:bg-slate-800">
                        <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                        Record
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
                    <h3 class="mb-5 text-sm font-bold tracking-tight text-slate-900">Record Payment</h3>
                    <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="space-y-4">
                            <x-ui.select name="customer_id" label="Customer" :options="$customers->pluck('name', 'id')->all()" />
                            <x-ui.select name="payment_method_id" label="Payment Method" :options="$paymentMethods->pluck('name', 'id')->all()" />
                            <x-ui.input name="payment_number" label="Payment Number" />
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.input name="amount" label="Amount" type="number" step="0.01" min="0" />
                                <x-ui.input name="payment_date" label="Payment Date" type="date" :value="now()->toDateString()" />
                            </div>
                            <x-ui.input name="reference" label="Reference" />
                            <x-ui.textarea name="notes" label="Notes" rows="3" />
                            
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Payment Proof</label>
                                <input type="file" name="proof" class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                            </div>
                        </div>

                        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200/60 bg-white">
                            <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-3">
                                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Allocate to Invoices</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <x-ui.table class="w-full text-sm">
                                    <thead class="border-b border-slate-100 bg-white">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">Invoice</th>
                                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-400">Due</th>
                                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-400">Allocate</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100/80 bg-white">
                                        @forelse($invoices as $invoice)
                                            <tr class="transition-colors hover:bg-slate-50/50">
                                                <td class="px-4 py-3">
                                                    <p class="font-bold text-slate-900">{{ $invoice->invoice_number }}</p>
                                                    <p class="mt-0.5 text-[10px] font-medium text-slate-500">{{ $invoice->customer?->name ?? '-' }}</p>
                                                </td>
                                                <td class="px-4 py-3 text-right font-medium text-rose-600">{{ number_format((float) $invoice->amount_due, 2) }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    <input type="number" step="0.01" min="0" name="allocations[{{ $invoice->id }}]" value="{{ old('allocations.'.$invoice->id) }}" class="w-24 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400 text-right">
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-4 py-8 text-center">
                                                    <p class="text-xs font-medium text-slate-500">No open invoices available.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </x-ui.table>
                            </div>
                        </div>

                        <div class="pt-4">
                            <x-ui.button type="submit" class="w-full justify-center rounded-xl py-2.5">Save Payment</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>

        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm xl:col-span-2">
            <div class="border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">Payment Register</h3>
            </div>
            
            <div class="overflow-x-auto">
                <x-ui.table class="w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Payment Ref</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Customer</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Method</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80 bg-white">
                        @forelse($payments as $payment)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
                                            <i class="ph ph-receipt text-lg"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('payments.show', $payment) }}" class="font-bold text-slate-900 transition hover:text-indigo-600 hover:underline">{{ $payment->payment_number }}</a>
                                            <p class="mt-0.5 text-[10px] font-medium text-slate-500">{{ $payment->payment_date?->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    {{ $payment->customer?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                                        {{ $payment->paymentMethod?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <x-ui.badge :status="$payment->status" class="rounded-lg px-2.5 py-1 text-[11px] font-semibold">{{ $payment->status }}</x-ui.badge>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-slate-900">
                                    {{ number_format((float) $payment->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="ph ph-wallet mb-3 text-4xl text-slate-300"></i>
                                        <p class="text-sm font-medium text-slate-500">No payments recorded yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            @if($payments->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>