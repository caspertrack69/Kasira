<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('payments.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60 transition hover:bg-slate-50">
                <i class="ph ph-arrow-left text-xl text-slate-600"></i>
            </a>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Payment Details</h2>
        </div>
    </x-slot>

    <div class="grid items-start gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-6 border-b border-slate-100 pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-4">
                            <h3 class="text-2xl font-bold tracking-tight text-slate-900">{{ $payment->payment_number }}</h3>
                            <x-ui.badge :status="$payment->status" class="rounded-lg px-2.5 py-1 text-xs font-bold">{{ $payment->status }}</x-ui.badge>
                        </div>
                        <p class="mt-1.5 text-sm font-medium text-slate-500">{{ $payment->customer?->name ?? '-' }} <span class="mx-1 text-slate-300">&bull;</span> {{ $payment->paymentMethod?->name ?? '-' }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($payment->proof_path)
                            <a href="{{ route('payments.proof', $payment) }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-white px-3.5 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50 hover:shadow">
                                <i class="ph ph-download-simple text-sm"></i> Proof
                            </a>
                        @endif
                        <form method="POST" action="{{ route('payments.confirm', $payment) }}" class="m-0">
                            @csrf
                            <x-ui.button type="submit" class="h-9 rounded-xl px-4 text-xs font-bold shadow-sm">
                                Confirm Payment
                            </x-ui.button>
                        </form>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Payment Details</p>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between"><dt class="font-medium text-slate-500">Date</dt><dd class="font-bold text-slate-900">{{ $payment->payment_date?->format('d M Y') ?? $payment->payment_date }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="font-medium text-slate-500">Amount</dt><dd class="font-bold text-slate-900">{{ number_format((float) $payment->amount, 2) }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="font-medium text-slate-500">Reference</dt><dd class="font-bold text-slate-900">{{ $payment->reference ?? '-' }}</dd></div>
                            @if($payment->notes)
                                <div class="flex flex-col gap-1 border-t border-slate-200/60 pt-3"><dt class="font-medium text-slate-500">Notes</dt><dd class="text-slate-700 leading-relaxed">{{ $payment->notes }}</dd></div>
                            @endif
                        </dl>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Confirmation</p>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between"><dt class="font-medium text-slate-500">Confirmed By</dt><dd class="font-bold text-slate-900">{{ $payment->confirmed_by ?? 'Pending' }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="font-medium text-slate-500">Confirmed At</dt><dd class="font-bold text-slate-900">{{ $payment->confirmed_at?->format('d M Y, H:i') ?? '-' }}</dd></div>
                            @if($payment->proof_path)
                                <div class="flex flex-col gap-1 border-t border-slate-200/60 pt-3"><dt class="font-medium text-slate-500">Proof Path</dt><dd class="break-all font-mono text-[10px] text-slate-600">{{ $payment->proof_path }}</dd></div>
                            @endif
                        </dl>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <h4 class="text-sm font-bold tracking-tight text-slate-900">Allocations</h4>
                </div>
                <div class="overflow-x-auto">
                    <x-ui.table class="w-full text-sm">
                        <thead class="bg-slate-50/50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Invoice Ref</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Customer</th>
                                <th class="px-6 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Allocated Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/80 bg-white">
                            @forelse($payment->allocations as $allocation)
                                <tr class="transition hover:bg-slate-50/50">
                                    <td class="px-6 py-3.5 font-bold text-slate-900">{{ $allocation->invoice?->invoice_number ?? '-' }}</td>
                                    <td class="px-6 py-3.5 font-medium text-slate-700">{{ $allocation->invoice?->customer?->name ?? '-' }}</td>
                                    <td class="px-6 py-3.5 text-right font-bold text-slate-900">{{ number_format((float) $allocation->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center">
                                        <p class="text-xs font-medium text-slate-500">No allocations recorded.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6 xl:col-span-1">
            <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <h4 class="mb-4 text-sm font-bold tracking-tight text-slate-900">Related Invoice Links</h4>
                <div class="space-y-2">
                    @forelse($payment->allocations as $allocation)
                        @if($allocation->invoice)
                            <a href="{{ route('invoices.show', $allocation->invoice) }}" class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                                {{ $allocation->invoice->invoice_number }}
                                <i class="ph ph-arrow-right text-slate-400"></i>
                            </a>
                        @endif
                    @empty
                        <div class="flex items-center justify-center rounded-xl border border-slate-100 bg-slate-50 py-4 text-center">
                            <p class="text-xs font-medium text-slate-500">No invoice links available.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>