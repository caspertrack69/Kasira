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
    @endphp

    <div class="space-y-6">
        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold">Gateway Secrets</h3>
                    <p class="mt-1 max-w-2xl text-sm text-slate-500">
                        Payment gateway and mail secrets stay in <code>.env</code> as required by the PRD. The table below only shows whether each required environment variable is present.
                    </p>
                </div>
                <div class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                    Protected Keys: {{ implode(', ', $protectedSettingKeys) }}
                </div>
            </div>

            <div class="mt-4 grid gap-4 xl:grid-cols-2">
                @foreach($gatewayStatuses as $gateway)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900">{{ $gateway['label'] }}</h4>
                                <p class="mt-1 text-xs text-slate-500">{{ $gateway['summary'] }}</p>
                            </div>
                            <x-ui.badge :status="$gateway['ready'] ? 'paid' : 'overdue'">
                                {{ $gateway['ready'] ? 'Configured' : 'Missing env' }}
                            </x-ui.badge>
                        </div>

                        <div class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                            Mode: <span class="font-semibold text-slate-900">{{ $gateway['mode'] }}</span>
                        </div>

                        <div class="mt-3 space-y-2">
                            @foreach($gateway['items'] as $item)
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                                    <div>
                                        <p class="text-sm font-medium text-slate-700">{{ $item['label'] }}</p>
                                        <p class="font-mono text-xs text-slate-500">{{ $item['env'] }}</p>
                                    </div>
                                    <span class="text-sm font-semibold {{ $item['configured'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $item['configured'] ? 'Present' : 'Missing' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card>
            <form method="POST" action="{{ route('settings.update') }}" x-data='{"rows": @js($settingRows), addRow(){ this.rows.push({group:"", key:"", value:""}); }, removeRow(index){ if (this.rows.length > 1) { this.rows.splice(index, 1); } }}' class="space-y-4">
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
</x-app-layout>
