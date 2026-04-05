<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">System Settings</h2></x-slot>

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
        <x-ui.card>
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h3 class="text-sm font-semibold">Gateway Secrets</h3>
                    <p class="mt-1 max-w-2xl text-sm text-slate-500">
                        Payment gateway and mail secrets stay in <code>.env</code> as required by the PRD. The status cards below report whether each required environment variable exists.
                    </p>
                </div>
            </div>

            <p class="mt-3 text-xs text-slate-500">
                Protected keys must remain environment-backed. The table and cards below only show env presence, never the actual secret.
            </p>

            <div class="mt-3 flex flex-wrap gap-2 text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">
                @foreach($protectedSettingKeys as $key)
                    <span class="rounded-full border border-slate-200 bg-white/80 px-2 py-1">{{ $key }}</span>
                @endforeach
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                @foreach($gatewayStatuses as $gateway)
                    @php
                        $missingCount = collect($gateway['items'])
                            ->filter(fn (array $item): bool => ! $item['configured'])
                            ->count();
                    @endphp
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-semibold text-slate-900">{{ $gateway['label'] }}</h4>
                                <p class="mt-1 text-sm text-slate-500">{{ $gateway['summary'] }}</p>
                            </div>
                            <x-ui.badge :status="$gateway['ready'] ? 'paid' : 'overdue'">
                                {{ $gateway['ready'] ? 'Ready' : 'Missing env' }}
                            </x-ui.badge>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">
                            <span class="rounded-full border border-slate-200 px-3 py-1">{{ $gateway['mode'] }}</span>
                            <span class="{{ $missingCount === 0 ? 'rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 px-3 py-1' : 'rounded-full border border-amber-200 bg-amber-50 text-amber-700 px-3 py-1' }}">
                                {{ $missingCount === 0 ? 'All required keys present' : $missingCount.' missing' }}
                            </span>
                        </div>

                        <div class="mt-4 space-y-3">
                            @foreach($gateway['items'] as $item)
                                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ $item['label'] }}</p>
                                        <p class="font-mono text-[0.65rem] uppercase tracking-wider text-slate-500">{{ $item['env'] }}</p>
                                    </div>
                                    <span class="text-sm font-semibold {{ $item['configured'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $item['configured'] ? 'Configured' : 'Missing' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            @if($missingGatewayItems->isNotEmpty())
                <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                    <p class="text-sm font-semibold">Action required</p>
                    <p class="mt-1 text-xs text-amber-700">Define these environment variables in <code>.env</code> so gateway integrations can initialize safely.</p>
                    <ul class="mt-3 space-y-2">
                        @foreach($missingGatewayItems as $item)
                            <li class="flex items-center justify-between rounded-lg border border-amber-100 bg-white/80 px-4 py-3">
                                <div>
                                    <p class="font-semibold text-amber-900">{{ $item['gateway'] }} · {{ $item['label'] }}</p>
                                    <p class="font-mono text-[0.65rem] uppercase tracking-wider text-amber-700">{{ $item['env'] }}</p>
                                </div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Missing</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
                    <p class="text-sm font-semibold">All critical gateway secrets are configured.</p>
                    <p class="mt-1 text-xs text-emerald-700">Keep these variables protected inside <code>.env</code>.</p>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card>
            <form method="POST" action="{{ route('settings.update') }}" x-data="settingsRows(@js($settingRows))" x-cloak class="space-y-4">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold">Application Settings</h3>
                        <p class="text-xs text-slate-500">Use this table for non-sensitive settings only. Protected keys are rejected and must remain environment-backed.</p>
                    </div>
                    <x-ui.button type="button" variant="secondary" @click="addRow()">Add Row</x-ui.button>
                </div>

                <x-ui.table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Group</th>
                            <th class="px-3 py-2 text-left">Key</th>
                            <th class="px-3 py-2 text-left">Value</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td class="px-3 py-2">
                                    <input :name="'settings[' + index + '][group]'" x-model="row.group" type="text" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                </td>
                                <td class="px-3 py-2">
                                    <input :name="'settings[' + index + '][key]'" x-model="row.key" type="text" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                </td>
                                <td class="px-3 py-2">
                                    <input :name="'settings[' + index + '][value]'" x-model="row.value" type="text" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" class="text-sm font-medium text-red-600" @click="removeRow(index)" x-show="rows.length > 1">Remove</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </x-ui.table>

                <x-ui.button type="submit">Save Settings</x-ui.button>
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
