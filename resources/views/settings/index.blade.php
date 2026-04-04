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

    <x-ui.card>
        <form method="POST" action="{{ route('settings.update') }}" x-data='{"rows": @js($settingRows), addRow(){ this.rows.push({group:"", key:"", value:""}); }, removeRow(index){ if (this.rows.length > 1) { this.rows.splice(index, 1); } }}' class="space-y-4">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold">Configuration Rows</h3>
                    <p class="text-xs text-slate-500">Edit existing values or add new key/value pairs.</p>
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
</x-app-layout>
