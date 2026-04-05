<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Audit Logs</h2>
    </x-slot>

    <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
        <div class="overflow-x-auto">
            <x-ui.table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Event</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Subject</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">User</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Changed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80 bg-white">
                    @forelse($logs as $log)
                        <tr class="transition-colors hover:bg-slate-50/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-500/20">
                                        <i class="ph ph-activity text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold capitalize text-slate-900">{{ $log->event }}</p>
                                        <p class="text-[11px] font-medium tracking-wide text-slate-500">Entity: {{ $log->entity_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ class_basename($log->auditable_type) }}</p>
                                <p class="text-[11px] font-medium tracking-wide text-slate-500">ID: {{ $log->auditable_id }}</p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    <i class="ph ph-user"></i>
                                    {{ $log->user_id ?? 'System' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <p class="text-sm font-medium text-slate-900">{{ $log->created_at?->format('d M, Y') }}</p>
                                <p class="text-[11px] font-medium text-slate-500">{{ $log->created_at?->format('H:i') }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <i class="ph ph-clock-counter-clockwise mb-3 text-4xl text-slate-300"></i>
                                    <p class="text-sm font-medium text-slate-500">No audit entries available.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        @if($logs->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </x-ui.card>
</x-app-layout>

