<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">System Settings</h2>
    </x-slot>

    @php
        $settingRows = old('settings', $settings->map(fn ($setting) => [
            'group' => $setting->group,
            'key' => $setting->key,
            'value' => $setting->value,
        ])->values()->all());
        if (empty($settingRows)) {
            $settingRows = [['group' => '', 'key' => '', 'value' => '']];
        }

        $missingGatewayItems = collect($gatewayStatuses)
            ->flatMap(fn (array $gateway) => collect($gateway['items'])
                ->filter(fn (array $item) => ! $item['configured'])
                ->map(fn (array $item) => [
                    'gateway' => $gateway['label'],
                    'label' => $item['label'],
                    'env' => $item['env'],
                ])
            )
            ->values();
    @endphp

    <div class="space-y-6">
        <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900">Gateway Secrets</h3>
                    <p class="mt-1.5 max-w-2xl text-sm font-medium text-slate-500">
                        Status konfigurasi environment untuk gateway pembayaran dan mail.
                    </p>
                </div>
            </div>

            <p class="mt-4 text-xs font-medium text-slate-400">
                Nilai rahasia tidak ditampilkan di halaman ini.
            </p>

            <div class="mt-4 flex flex-wrap gap-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                @foreach($protectedSettingKeys as $key)
                    <span class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5">{{ $key }}</span>
                @endforeach
            </div>

            <div class="mt-8 grid gap-5 lg:grid-cols-2">
                @foreach($gatewayStatuses as $gateway)
                    @php
                        $missingCount = collect($gateway['items'])
                            ->filter(fn (array $item): bool => ! $item['configured'])
                            ->count();
                    @endphp
                    <article class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm transition hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="text-base font-bold tracking-tight text-slate-900">{{ $gateway['label'] }}</h4>
                                <p class="mt-1 text-xs font-medium text-slate-500">{{ $gateway['summary'] }}</p>
                            </div>
                            <x-ui.badge :status="$gateway['ready'] ? 'paid' : 'overdue'" class="shrink-0 rounded-lg px-2.5 py-1 text-[11px] font-semibold">
                                {{ $gateway['ready'] ? 'Ready' : 'Missing env' }}
                            </x-ui.badge>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-2.5 text-[10px] font-bold uppercase tracking-wider">
                            <span class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-slate-600">{{ $gateway['mode'] }}</span>
                            <span class="{{ $missingCount === 0 ? 'rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 px-2.5 py-1.5' : 'rounded-lg border border-amber-200 bg-amber-50 text-amber-700 px-2.5 py-1.5' }}">
                                {{ $missingCount === 0 ? 'All required keys present' : $missingCount.' missing' }}
                            </span>
                        </div>

                        <div class="mt-5 space-y-2.5">
                            @foreach($gateway['items'] as $item)
                                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 transition hover:bg-slate-50">
                                    <div>
                                        <p class="text-[13px] font-bold text-slate-900">{{ $item['label'] }}</p>
                                        <p class="mt-0.5 font-mono text-[10px] uppercase tracking-wider text-slate-500">{{ $item['env'] }}</p>
                                    </div>
                                    <span class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider {{ $item['configured'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                        @if($item['configured'])
                                            <i class="ph ph-check-circle text-sm"></i> Configured
                                        @else
                                            <i class="ph ph-warning-circle text-sm"></i> Missing
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            @if($missingGatewayItems->isNotEmpty())
                <div class="mt-8 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            <i class="ph ph-warning text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-amber-900">Action required</p>
                            <p class="text-xs font-medium text-amber-700">Lengkapi variable berikut di <code class="rounded bg-amber-100 px-1 py-0.5">.env</code>.</p>
                        </div>
                    </div>
                    <ul class="mt-5 space-y-2.5">
                        @foreach($missingGatewayItems as $item)
                            <li class="flex items-center justify-between rounded-xl border border-amber-200/60 bg-white px-5 py-3 shadow-sm">
                                <div>
                                    <p class="text-[13px] font-bold text-amber-900">{{ $item['gateway'] }} <span class="text-amber-400">&bull;</span> {{ $item['label'] }}</p>
                                    <p class="mt-0.5 font-mono text-[10px] uppercase tracking-wider text-amber-600">{{ $item['env'] }}</p>
                                </div>
                                <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-700">Missing</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="mt-8 flex items-center gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <i class="ph ph-check-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-900">All critical gateway secrets are configured.</p>
                        <p class="text-xs font-medium text-emerald-700">Semua variable yang dibutuhkan sudah tersedia.</p>
                    </div>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <form method="POST" action="{{ route('settings.update') }}" x-data="settingsRows(@js($settingRows))" x-cloak class="flex flex-col">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-6">
                    <div>
                        <h3 class="text-base font-bold tracking-tight text-slate-900">Application Settings</h3>
                        <p class="mt-1 text-xs font-medium text-slate-500">Pengaturan aplikasi non-rahasia.</p>
                    </div>
                    <x-ui.button type="button" variant="secondary" @click="addRow()" class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold shadow-sm">
                        <i class="ph ph-plus mr-1.5"></i> Add Row
                    </x-ui.button>
                </div>

                <div class="overflow-x-auto p-6 pt-0">
                    <x-ui.table class="mt-6 w-full text-sm">
                        <thead class="border-b border-slate-100 bg-slate-50/50">
                            <tr>
                                <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Group</th>
                                <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Key</th>
                                <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Value</th>
                                <th class="px-4 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/80 bg-white">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="transition-colors hover:bg-slate-50/50">
                                    <td class="p-3">
                                        <input :name="'settings[' + index + '][group]'" x-model="row.group" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                    </td>
                                    <td class="p-3">
                                        <input :name="'settings[' + index + '][key]'" x-model="row.key" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                    </td>
                                    <td class="p-3">
                                        <input :name="'settings[' + index + '][value]'" x-model="row.value" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                    </td>
                                    <td class="p-3 text-right">
                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 hover:text-rose-600" @click="removeRow(index)" x-show="rows.length > 1" title="Remove row">
                                            <i class="ph ph-trash text-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </x-ui.table>
                </div>

                <div class="border-t border-slate-100 bg-slate-50/30 p-6">
                    <x-ui.button type="submit" class="rounded-xl px-6 py-2.5 font-semibold">Save Settings</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    @once
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('settingsRows', (initialRows = []) => ({
                    rows: Array.isArray(initialRows) ? initialRows : [],
                    addRow() {
                        this.rows.push({ group: '', key: '', value: '' });
                    },
                    removeRow(index) {
                        if (this.rows.length <= 1) {
                            return;
                        }
                        this.rows.splice(index, 1);
                    },
                }));
            });
        </script>
    @endonce
</x-app-layout>


