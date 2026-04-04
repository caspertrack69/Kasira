<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Notification Logs</h2></x-slot>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left">Event</th>
                    <th class="px-3 py-2 text-left">Recipient</th>
                    <th class="px-3 py-2 text-left">Channel</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-right">Sent</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-3 py-2">
                            <p class="font-medium">{{ $log->event_type }}</p>
                            <p class="text-xs text-slate-500">{{ $log->entity_id }}</p>
                        </td>
                        <td class="px-3 py-2">{{ $log->recipient }}</td>
                        <td class="px-3 py-2">{{ $log->channel }}</td>
                        <td class="px-3 py-2"><x-ui.badge :status="$log->status">{{ $log->status }}</x-ui.badge></td>
                        <td class="px-3 py-2 text-right">{{ $log->sent_at?->format('Y-m-d H:i') ?? $log->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-slate-500">No notification logs available.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="mt-4">{{ $logs->links() }}</div>
    </x-ui.card>
</x-app-layout>
