<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Invoice Total</p>
            <p class="mt-2 text-2xl font-bold">{{ number_format((float) $summary['invoice_total'], 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Payment Total</p>
            <p class="mt-2 text-2xl font-bold">{{ number_format((float) $summary['payment_total'], 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Outstanding</p>
            <p class="mt-2 text-2xl font-bold">{{ number_format((float) $summary['outstanding_total'], 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Current Aging</p>
            <p class="mt-2 text-2xl font-bold">{{ number_format((float) $summary['aging']['current'], 2) }}</p>
        </x-ui.card>
    </div>

    <x-ui.card>
        <h3 class="mb-3 text-sm font-semibold">Aging Breakdown</h3>
        <x-ui.table>
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left">Bucket</th>
                    <th class="px-3 py-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-3 py-2">Current</td><td class="px-3 py-2 text-right">{{ number_format((float) $summary['aging']['current'], 2) }}</td></tr>
                <tr><td class="px-3 py-2">1-30 Days</td><td class="px-3 py-2 text-right">{{ number_format((float) $summary['aging']['30'], 2) }}</td></tr>
                <tr><td class="px-3 py-2">31-60 Days</td><td class="px-3 py-2 text-right">{{ number_format((float) $summary['aging']['60'], 2) }}</td></tr>
                <tr><td class="px-3 py-2">90+ Days</td><td class="px-3 py-2 text-right">{{ number_format((float) $summary['aging']['90_plus'], 2) }}</td></tr>
            </tbody>
        </x-ui.table>
    </x-ui.card>
</div>
