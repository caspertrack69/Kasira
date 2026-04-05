<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Dashboard Overview</h2>
    </x-slot>

    <!-- Compact Stats Grid -->
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        
        <!-- Outstanding Invoices -->
        <x-ui.card class="relative overflow-hidden rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Outstanding</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($outstandingTotal, 2) }}</p>
                    <p class="mt-1 flex items-center gap-1 text-xs font-medium text-slate-500">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        {{ $outstandingCount }} invoices
                    </p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-500/20">
                    <i class="ph ph-receipt text-xl"></i>
                </div>
            </div>
        </x-ui.card>

        <!-- Overdue Amount -->
        <x-ui.card class="relative overflow-hidden rounded-2xl border border-rose-100 bg-rose-50/30 p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-rose-500/80">Overdue</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-rose-600">{{ number_format($overdueTotal, 2) }}</p>
                    <p class="mt-1 flex items-center gap-1 text-xs font-medium text-rose-500/80">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        {{ $overdueCount }} invoices
                    </p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 ring-1 ring-rose-500/20">
                    <i class="ph ph-warning-circle text-xl"></i>
                </div>
            </div>
        </x-ui.card>

        <!-- Payments Recorded -->
        <x-ui.card class="relative overflow-hidden rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Payments</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($paymentTotal, 2) }}</p>
                    <p class="mt-1 flex items-center gap-1 text-xs font-medium text-slate-500">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        {{ $paymentCount }} recorded
                    </p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
                    <i class="ph ph-wallet text-xl"></i>
                </div>
            </div>
        </x-ui.card>

        <!-- Quick Actions -->
        <x-ui.card class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <i class="ph ph-lightning text-lg text-amber-500"></i>
                        <h3 class="text-sm font-semibold tracking-wide text-slate-900">Quick Actions</h3>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Aksi cepat untuk operasional harian.</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-500/20">
                    <i class="ph ph-cursor-click text-xl"></i>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('invoices.index') }}" class="flex items-center justify-center rounded-xl bg-slate-900 py-2 text-xs font-semibold text-white transition hover:bg-slate-800">
                    New Invoice
                </a>
                <a href="{{ route('payments.index') }}" class="flex items-center justify-center rounded-xl bg-slate-100 py-2 text-xs font-semibold text-slate-800 ring-1 ring-slate-200 transition hover:bg-slate-200">
                    Receive Payment
                </a>
            </div>
        </x-ui.card>

    </div>

    <!-- Data Table Section -->
    <div class="mt-8 flex items-center justify-between">
        <h3 class="text-base font-bold tracking-tight text-slate-900">Recent Payments</h3>
        <a href="{{ route('payments.index') }}" class="text-sm font-medium text-slate-500 transition hover:text-slate-900">View all &rarr;</a>
    </div>

    <div class="hidden overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm lg:block">
        <x-ui.table class="w-full text-sm">
            <thead class="border-b border-slate-100 bg-slate-50/50">
                <tr>
                    <th class="px-5 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Number</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Date</th>
                    <th class="px-5 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 bg-white">
                @forelse($recentPayments as $payment)
                    <tr class="transition hover:bg-slate-50/50">
                        <td class="whitespace-nowrap px-5 py-3.5 font-medium text-slate-900">{{ $payment->payment_number }}</td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-slate-500">{{ $payment->payment_date?->format('d M, Y') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-right font-bold text-slate-900">{{ number_format($payment->amount, 2) }}</td>
                        <td class="whitespace-nowrap px-5 py-3.5">
                            <x-ui.badge :status="$payment->status" class="rounded-lg px-2.5 py-1 text-[11px] font-bold">{{ $payment->status }}</x-ui.badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-sm font-medium text-slate-500">No recent payments recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="space-y-3 lg:hidden">
        @forelse($recentPayments as $payment)
            <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $payment->payment_number }}</p>
                        <p class="text-xs font-medium text-slate-500">{{ $payment->payment_date?->format('d M, Y') ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900">{{ number_format($payment->amount, 2) }}</p>
                        <x-ui.badge :status="$payment->status" class="mt-1 rounded-lg px-2 py-0.5 text-[10px] font-bold">{{ $payment->status }}</x-ui.badge>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white/50 p-8 text-center text-sm font-medium text-slate-500">
                No recent payments recorded.
            </div>
        @endforelse
    </div>
</x-app-layout>
