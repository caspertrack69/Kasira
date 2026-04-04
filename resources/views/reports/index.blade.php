<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Reports</h2></x-slot>

    <div class="space-y-6">
        <x-ui.card>
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    @if($entities->isNotEmpty())
                        <div class="min-w-56">
                            <x-ui.select name="entity_id" label="Entity Scope" :options="['all' => 'All Entities'] + $entities->pluck('name', 'id')->all()" :selected="$selectedEntityId" />
                        </div>
                    @endif
                    <x-ui.input name="from" label="From" type="date" :value="$from" />
                    <x-ui.input name="to" label="To" type="date" :value="$to" />
                    <x-ui.button type="submit">Run Report</x-ui.button>
                </form>

                <a class="rounded-md bg-slate-200 px-3 py-2 text-sm font-medium text-slate-900" href="{{ route('reports.export.csv', ['from' => $from, 'to' => $to, 'entity_id' => $selectedEntityId]) }}">CSV Export</a>

                <form method="POST" action="{{ route('reports.export.pdf') }}">
                    @csrf
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="to" value="{{ $to }}">
                    <input type="hidden" name="entity_id" value="{{ $selectedEntityId }}">
                    <x-ui.button type="submit" variant="secondary">Queue PDF Export</x-ui.button>
                </form>

                @if($pdfDownloadUrl)
                    <a class="rounded-md bg-emerald-100 px-3 py-2 text-sm font-medium text-emerald-900" href="{{ $pdfDownloadUrl }}">Download Latest PDF</a>
                @endif
            </div>
        </x-ui.card>

        @include('reports.partials.summary', ['summary' => $summary])
    </div>
</x-app-layout>
