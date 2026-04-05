<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('invoices.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                    <i class="ph ph-arrow-left text-lg"></i>
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Invoice</p>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">{{ $invoice->invoice_number }}</h2>
                </div>
            </div>

            <x-ui.badge :status="$invoice->status" class="rounded-lg px-3 py-1 text-xs font-bold uppercase">{{ $invoice->status }}</x-ui.badge>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('invoices.pdf', $invoice) }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <i class="ph ph-file-pdf text-base"></i>
                    Download PDF
                </a>

                @if($invoice->status === 'draft')
                    <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="m-0">
                        @csrf
                        <x-ui.button type="submit" class="h-10 rounded-xl px-4 text-sm font-semibold">
                            <i class="ph ph-paper-plane-tilt mr-2 text-base"></i>
                            Finalize & Send
                        </x-ui.button>
                    </form>
                @endif

                @can('create', \App\Models\Invoice::class)
                    <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}" class="m-0">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" class="h-10 rounded-xl px-4 text-sm font-semibold">
                            <i class="ph ph-copy mr-2 text-base"></i>
                            Duplicate
                        </x-ui.button>
                    </form>
                @endcan

                @if(! in_array($invoice->status, ['paid', 'void', 'cancelled'], true))
                    <form method="POST" action="{{ route('invoices.void', $invoice) }}" class="m-0" onsubmit="return confirm('Are you sure you want to void this invoice?')">
                        @csrf
                        <x-ui.button type="submit" variant="danger" class="h-10 rounded-xl px-4 text-sm font-semibold">
                            Void Invoice
                        </x-ui.button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-10">
                        @include('invoices.partials.document')
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Summary</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500">Invoice Date</dt>
                            <dd class="font-semibold text-slate-900">{{ $invoice->invoice_date?->format('d M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500">Due Date</dt>
                            <dd class="font-semibold text-slate-900">{{ $invoice->due_date?->format('d M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500">Currency</dt>
                            <dd class="font-semibold text-slate-900">{{ $invoice->currency }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-3">
                            <dt class="text-slate-500">Amount Due</dt>
                            <dd class="text-base font-bold text-slate-900">{{ number_format((float) $invoice->amount_due, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Public Payment Link</h3>
                    <p class="mt-1 text-xs text-slate-500">Share this link with the customer for payment and proof upload.</p>
                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="truncate font-mono text-xs text-slate-600">{{ route('invoices.public.show', ['token' => $invoice->public_token]) }}</p>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ route('invoices.public.show', ['token' => $invoice->public_token]) }}'); this.innerText='Copied'; setTimeout(() => this.innerText='Copy Link', 1200);"
                            class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Copy Link
                        </button>
                        <a href="{{ route('invoices.public.show', ['token' => $invoice->public_token]) }}" target="_blank" class="inline-flex h-9 items-center justify-center rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white transition hover:bg-slate-800">
                            Open Page
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Status History</h3>
                    <div class="mt-4 space-y-4">
                        @forelse($invoice->statusHistories as $history)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-700">{{ ucfirst($history->to_status) }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $history->created_at->format('d M Y H:i') }}</p>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">By {{ $history->changedByUser?->name ?? 'System' }}</p>
                                @if($history->notes)
                                    <p class="mt-2 text-xs text-slate-600">{{ $history->notes }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">No history yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
