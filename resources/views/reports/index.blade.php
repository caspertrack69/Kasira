<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Reports</h2>
    </x-slot>

    <div class="space-y-6">
        <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    @if($entities->isNotEmpty())
                        <div class="min-w-56">
                            <x-ui.select name="entity_id" label="Entity Scope" :options="['all' => 'All Entities'] + $entities->pluck('name', 'id')->all()" :selected="$selectedEntityId" />
                        </div>
                    @endif
                    <div class="min-w-40">
                        <x-ui.input name="from" label="From" type="date" :value="$from" />
                    </div>
                    <div class="min-w-40">
                        <x-ui.input name="to" label="To" type="date" :value="$to" />
                    </div>
                    <x-ui.button type="submit" class="rounded-xl px-5 py-2.5 font-semibold">Run Report</x-ui.button>
                </form>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('reports.export.csv', ['from' => $from, 'to' => $to, 'entity_id' => $selectedEntityId]) }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="ph ph-file-csv text-lg"></i>
                        Export CSV
                    </a>

                    <form method="POST" action="{{ route('reports.export.pdf') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="from" value="{{ $from }}">
                        <input type="hidden" name="to" value="{{ $to }}">
                        <input type="hidden" name="entity_id" value="{{ $selectedEntityId }}">
                        <x-ui.button type="submit" variant="secondary" class="h-10 rounded-xl px-4 text-sm font-semibold shadow-sm">
                            <i class="ph ph-file-pdf mr-1.5 text-lg"></i>
                            Queue PDF
                        </x-ui.button>
                    </form>

                    @if($pdfDownloadUrl)
                        <a href="{{ $pdfDownloadUrl }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                            <i class="ph ph-download-simple text-lg"></i>
                            Download PDF
                        </a>
                    @endif
                </div>
            </div>
        </x-ui.card>

        @include('reports.partials.summary', ['summary' => $summary])
    </div>
</x-app-layout>

