<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Payment {{ $payment->payment_number }}</h2></x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold">{{ $payment->payment_number }}</h3>
                    <p class="text-sm text-slate-500">{{ $payment->customer?->name ?? '-' }} - {{ $payment->paymentMethod?->name ?? '-' }}</p>
                </div>
                <form method="POST" action="{{ route('payments.confirm', $payment) }}">
                    @csrf
                    <x-ui.button type="submit">Confirm Payment</x-ui.button>
                </form>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment Details</p>
                    <div class="mt-2 space-y-1 text-sm">
                        <p>Date: {{ $payment->payment_date?->format('Y-m-d') ?? $payment->payment_date }}</p>
                        <p>Amount: {{ number_format((float) $payment->amount, 2) }}</p>
                        <p>Status: <x-ui.badge :status="$payment->status">{{ $payment->status }}</x-ui.badge></p>
                        <p>Reference: {{ $payment->reference ?? '-' }}</p>
                    </div>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Confirmation</p>
                    <div class="mt-2 space-y-1 text-sm">
                        <p>Confirmed By: {{ $payment->confirmed_by ?? '-' }}</p>
                        <p>Confirmed At: {{ $payment->confirmed_at?->format('Y-m-d H:i') ?? '-' }}</p>
                        <p>Proof Path: {{ $payment->proof_path ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="mb-3 text-sm font-semibold">Allocations</h4>
                <x-ui.table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Invoice</th>
                            <th class="px-3 py-2 text-left">Customer</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payment->allocations as $allocation)
                            <tr>
                                <td class="px-3 py-2">{{ $allocation->invoice?->invoice_number ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $allocation->invoice?->customer?->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $allocation->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-slate-500">No allocations recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h4 class="mb-3 text-sm font-semibold">Related Invoice Links</h4>
            <div class="space-y-2 text-sm">
                @forelse($payment->allocations as $allocation)
                    <a href="{{ route('invoices.show', $allocation->invoice) }}" class="block rounded-md border border-slate-200 px-3 py-2 text-slate-900 hover:bg-slate-50">
                        {{ $allocation->invoice?->invoice_number ?? '-' }}
                    </a>
                @empty
                    <p class="text-slate-500">No invoice links available.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
