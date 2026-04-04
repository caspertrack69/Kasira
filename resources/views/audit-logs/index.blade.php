<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Audit Logs</h2></x-slot>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left">Event</th>
                    <th class="px-3 py-2 text-left">Subject</th>
                    <th class="px-3 py-2 text-left">User</th>
                    <th class="px-3 py-2 text-left">Changed At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-3 py-2">
                            <p class="font-medium">{{ $log->event }}</p>
                            <p class="text-xs text-slate-500">Entity: {{ $log->entity_id }}</p>
                        </td>
                        <td class="px-3 py-2">
                            <p class="font-medium">{{ $log->auditable_type }}</p>
                            <p class="text-xs text-slate-500">{{ $log->auditable_id }}</p>
                        </td>
                        <td class="px-3 py-2">{{ $log->user_id ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-slate-500">No audit entries available.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="mt-4">{{ $logs->links() }}</div>
    </x-ui.card>
</x-app-layout>
