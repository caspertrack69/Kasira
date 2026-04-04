<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Recurring Templates</h2></x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-1">
            <h3 class="mb-3 text-sm font-semibold">Create Recurring Template</h3>
            <form method="POST" action="{{ route('recurring-templates.store') }}" class="space-y-4">
                @csrf
                <x-ui.select name="customer_id" label="Customer" :options="$customers->pluck('name', 'id')->all()" />
                <x-ui.input name="name" label="Template Name" />
                <x-ui.select name="frequency" label="Frequency" :options="['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annually' => 'Annually']" />
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.input name="start_date" label="Start Date" type="date" />
                    <x-ui.input name="next_generate_date" label="Next Generate Date" type="date" />
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.input name="end_date" label="End Date" type="date" />
                    <x-ui.input name="occurrences_limit" label="Occurrences Limit" type="number" min="1" />
                </div>
                <x-ui.textarea name="template_data" label="Template Data JSON" rows="8" value='{"items":[{"description":"Service fee","quantity":1,"unit_price":0}],"currency":"IDR"}' />
                <div class="flex gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="auto_send" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"> Auto send</label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-500" checked> Active</label>
                </div>
                <x-ui.button type="submit">Save Template</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card class="xl:col-span-2">
            <h3 class="text-sm font-semibold">Template Register</h3>
            <div class="mt-4">
                <x-ui.table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Customer</th>
                            <th class="px-3 py-2 text-left">Frequency</th>
                            <th class="px-3 py-2 text-left">Next Run</th>
                            <th class="px-3 py-2 text-left">State</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($templates as $template)
                            <tr>
                                <td class="px-3 py-2">
                                    <p class="font-medium">{{ $template->name }}</p>
                                    <p class="text-xs text-slate-500">Generated {{ $template->occurrences_count ?? 0 }} times</p>
                                </td>
                                <td class="px-3 py-2">{{ $template->customer?->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $template->frequency }}</td>
                                <td class="px-3 py-2">{{ $template->next_generate_date?->format('Y-m-d') ?? $template->next_generate_date }}</td>
                                <td class="px-3 py-2">
                                    <x-ui.badge :status="$template->is_active ? 'paid' : 'cancelled'">{{ $template->is_active ? 'active' : 'inactive' }}</x-ui.badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-slate-500">No recurring templates yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            <div class="mt-4">{{ $templates->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
