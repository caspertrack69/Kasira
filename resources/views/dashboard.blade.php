<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard</h2>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.card>
            <p class="text-sm text-slate-500">Outstanding Invoices</p>
            <p class="mt-2 text-2xl font-bold">{{ number_format($outstandingTotal, 2) }}</p>
            <p class="text-xs text-slate-500">{{ $outstandingCount }} invoices</p>
        </x-ui.card>

        <x-ui.card>
            <p class="text-sm text-slate-500">Overdue Amount</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ number_format($overdueTotal, 2) }}</p>
            <p class="text-xs text-slate-500">{{ $overdueCount }} invoices</p>
        </x-ui.card>

        <x-ui.card>
            <p class="text-sm text-slate-500">Payments Recorded</p>
            <p class="mt-2 text-2xl font-bold">{{ number_format($paymentTotal, 2) }}</p>
            <p class="text-xs text-slate-500">{{ $paymentCount }} payments</p>
        </x-ui.card>

        <x-ui.card>
            <p class="text-sm text-slate-500">Quick Actions</p>
            <div class="mt-2 flex gap-2">
                <x-ui.button :href="route('invoices.index')">New Invoice</x-ui.button>
                <x-ui.button :href="route('payments.index')" variant="secondary">Record Payment</x-ui.button>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6">
        <h3 class="mb-3 text-sm font-semibold">Recent Payments</h3>
        <x-ui.table>
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left">Number</th>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2 text-right">Amount</th>
                    <th class="px-3 py-2 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentPayments as $payment)
                    <tr>
                        <td class="px-3 py-2">{{ $payment->payment_number }}</td>
                        <td class="px-3 py-2">{{ $payment->payment_date?->format('Y-m-d') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-3 py-2"><x-ui.badge :status="$payment->status">{{ $payment->status }}</x-ui.badge></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-3 text-center text-slate-500">No payment data yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>
</x-app-layout>
