<div class="space-y-6">
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.card class="relative overflow-hidden rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Invoice Total</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format((float) $summary['invoice_total'], 2) }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-500/20">
                    <i class="ph ph-file-text text-xl"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="relative overflow-hidden rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Payment Total</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format((float) $summary['payment_total'], 2) }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
                    <i class="ph ph-wallet text-xl"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="relative overflow-hidden rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Outstanding</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format((float) $summary['outstanding_total'], 2) }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-500/20">
                    <i class="ph ph-receipt text-xl"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="relative overflow-hidden rounded-2xl border border-rose-100 bg-rose-50/30 p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-rose-500/80">Current Aging</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-rose-600">{{ number_format((float) $summary['aging']['current'], 2) }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 ring-1 ring-rose-500/20">
                    <i class="ph ph-chart-polar text-xl"></i>
                </div>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <div class="flex items-center gap-2 text-slate-900">
                <i class="ph ph-chart-bar text-lg text-slate-500"></i>
                <h3 class="text-sm font-bold tracking-tight">Aging Breakdown</h3>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <x-ui.table class="w-full text-sm">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Bucket</th>
                        <th class="px-6 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80 bg-white">
                    <tr class="transition-colors hover:bg-slate-50/50">
                        <td class="px-6 py-3.5 font-medium text-slate-900">Current</td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-900">{{ number_format((float) $summary['aging']['current'], 2) }}</td>
                    </tr>
                    <tr class="transition-colors hover:bg-slate-50/50">
                        <td class="px-6 py-3.5 font-medium text-slate-700">1-30 Days</td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-700">{{ number_format((float) $summary['aging']['30'], 2) }}</td>
                    </tr>
                    <tr class="transition-colors hover:bg-slate-50/50">
                        <td class="px-6 py-3.5 font-medium text-amber-700">31-60 Days</td>
                        <td class="px-6 py-3.5 text-right font-bold text-amber-700">{{ number_format((float) $summary['aging']['60'], 2) }}</td>
                    </tr>
                    <tr class="transition-colors hover:bg-slate-50/50">
                        <td class="px-6 py-3.5 font-medium text-rose-600">90+ Days</td>
                        <td class="px-6 py-3.5 text-right font-bold text-rose-600">{{ number_format((float) $summary['aging']['90_plus'], 2) }}</td>
                    </tr>
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.card>
</div>