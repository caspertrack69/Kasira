<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Recurring Templates</h2>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">Template Register</h3>
                <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800"><i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>Tambah Template</button>
            </div>

            <div x-show="createOpen" x-cloak class="border-b border-slate-100 bg-slate-50/50 p-6">
                <form method="POST" action="{{ route('recurring-templates.store') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <x-ui.select name="customer_id" label="Customer" :options="$customers->pluck('name', 'id')->all()" />
                    <x-ui.input name="name" label="Template Name" />
                    <x-ui.select name="frequency" label="Frequency" :options="['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annually' => 'Annually']" />
                    <x-ui.input name="start_date" label="Start Date" type="date" />
                    <x-ui.input name="next_generate_date" label="Next Generate Date" type="date" />
                    <x-ui.input name="end_date" label="End Date" type="date" />
                    <x-ui.input name="occurrences_limit" label="Occurrences Limit" type="number" min="1" />
                    <div class="md:col-span-2"><x-ui.textarea name="template_data" label="Template Data JSON" rows="6" value='{"items":[{"description":"Service fee","quantity":1,"unit_price":0}],"currency":"IDR"}' /></div>
                    <div class="md:col-span-2 flex flex-wrap items-center gap-6 rounded-xl border border-slate-200/60 bg-white p-4">
                        <label class="inline-flex cursor-pointer items-center gap-2.5"><input type="checkbox" name="auto_send" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 shadow-sm transition focus:ring-slate-900"><span class="text-sm font-semibold text-slate-700">Auto Send</span></label>
                        <label class="inline-flex cursor-pointer items-center gap-2.5"><input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 shadow-sm transition focus:ring-slate-900" checked><span class="text-sm font-semibold text-slate-700">Active</span></label>
                    </div>
                    <div class="md:col-span-2"><x-ui.button type="submit" class="rounded-xl px-5 py-2.5">Save Template</x-ui.button></div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <x-ui.table class="w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50"><tr><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Name</th><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Customer</th><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Frequency</th><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Next Run</th><th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">State</th></tr></thead>
                    <tbody class="divide-y divide-slate-100/80 bg-white">
                        @forelse($templates as $template)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-500/20"><i class="ph ph-files text-lg"></i></div><div><p class="font-bold text-slate-900">{{ $template->name }}</p><p class="font-mono text-[10px] uppercase tracking-wider text-slate-500">Generated {{ $template->occurrences_count ?? 0 }} times</p></div></div></td>
                                <td class="px-6 py-4 font-medium text-slate-700">{{ $template->customer?->name ?? '-' }}</td>
                                <td class="px-6 py-4"><span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 capitalize">{{ $template->frequency }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4"><p class="font-bold text-slate-900">{{ $template->next_generate_date?->format('d M Y') ?? ($template->next_generate_date ?? '-') }}</p></td>
                                <td class="whitespace-nowrap px-6 py-4"><x-ui.badge :status="$template->is_active ? 'paid' : 'cancelled'" class="rounded-lg px-2.5 py-1 text-[11px] font-semibold">{{ $template->is_active ? 'active' : 'inactive' }}</x-ui.badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center"><div class="flex flex-col items-center justify-center text-slate-400"><i class="ph ph-arrows-clockwise mb-3 text-4xl text-slate-300"></i><p class="text-sm font-medium text-slate-500">No recurring templates yet.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            @if($templates->hasPages())<div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">{{ $templates->links() }}</div>@endif
        </x-ui.card>
    </div>
</x-app-layout>


