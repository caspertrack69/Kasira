<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Credit Notes</h2></x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-1">
            <h3 class="mb-3 text-sm font-semibold">Issue Credit Note</h3>
            <form method="POST" action="{{ route('credit-notes.store') }}" class="space-y-4">
                @csrf
                <x-ui.select name="invoice_id" label="Invoice" :options="$invoices->mapWithKeys(fn ($invoice) => [$invoice->id => $invoice->invoice_number.' - '.$invoice->customer?->name])->all()" />
                <x-ui.input name="amount" label="Amount" type="number" step="0.01" min="0" />
                <x-ui.textarea name="reason" label="Reason" rows="4" />
                <x-ui.button type="submit">Issue Credit Note</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card class="xl:col-span-2">
            <h3 class="text-sm font-semibold">Credit Note Register</h3>
            <div class="mt-4">
                <x-ui.table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Credit Note</th>
                            <th class="px-3 py-2 text-left">Invoice</th>
                            <th class="px-3 py-2 text-left">Reason</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($creditNotes as $creditNote)
                            <tr>
                                <td class="px-3 py-2">{{ $creditNote->credit_note_number }}</td>
                                <td class="px-3 py-2">{{ $creditNote->invoice?->invoice_number ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $creditNote->reason }}</td>
                                <td class="px-3 py-2"><x-ui.badge :status="$creditNote->status">{{ $creditNote->status }}</x-ui.badge></td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $creditNote->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-slate-500">No credit notes issued yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            <div class="mt-4">{{ $creditNotes->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
