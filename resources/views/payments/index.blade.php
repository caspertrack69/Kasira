<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Payments</h2></x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-1">
            <h3 class="mb-3 text-sm font-semibold">Record Payment</h3>
            <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <x-ui.select name="customer_id" label="Customer" :options="$customers->pluck('name', 'id')->all()" />
                <x-ui.select name="payment_method_id" label="Payment Method" :options="$paymentMethods->pluck('name', 'id')->all()" />
                <x-ui.input name="payment_number" label="Payment Number" />
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.input name="amount" label="Amount" type="number" step="0.01" min="0" />
                    <x-ui.input name="payment_date" label="Payment Date" type="date" :value="now()->toDateString()" />
                </div>
                <x-ui.input name="reference" label="Reference" />
                <x-ui.textarea name="notes" label="Notes" rows="3" />
                <x-ui.input name="proof" label="Payment Proof" type="file" />

                <div class="space-y-3">
                    <h4 class="text-sm font-semibold">Allocate to Invoices</h4>
                    <x-ui.table>
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Invoice</th>
                                <th class="px-3 py-2 text-right">Due</th>
                                <th class="px-3 py-2 text-right">Allocate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td class="px-3 py-2">
                                        <p class="font-medium">{{ $invoice->invoice_number }}</p>
                                        <p class="text-xs text-slate-500">{{ $invoice->customer?->name ?? '-' }}</p>
                                    </td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) $invoice->amount_due, 2) }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="number" step="0.01" min="0" name="allocations[{{ $invoice->id }}]" value="{{ old('allocations.'.$invoice->id) }}" class="w-28 rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-6 text-center text-slate-500">No open invoices available for allocation.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>

                <x-ui.button type="submit">Save Payment</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card class="xl:col-span-2">
            <h3 class="text-sm font-semibold">Payment Register</h3>
            <div class="mt-4">
                <x-ui.table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Payment</th>
                            <th class="px-3 py-2 text-left">Customer</th>
                            <th class="px-3 py-2 text-left">Method</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $payment)
                            <tr>
                                <td class="px-3 py-2">
                                    <a href="{{ route('payments.show', $payment) }}" class="font-medium text-slate-900 hover:underline">{{ $payment->payment_number }}</a>
                                    <div class="text-xs text-slate-500">{{ $payment->payment_date?->format('Y-m-d') }}</div>
                                </td>
                                <td class="px-3 py-2">{{ $payment->customer?->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $payment->paymentMethod?->name ?? '-' }}</td>
                                <td class="px-3 py-2"><x-ui.badge :status="$payment->status">{{ $payment->status }}</x-ui.badge></td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-slate-500">No payments yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            <div class="mt-4">{{ $payments->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
