<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Entities</h2></x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold">Create Entity</h3>
            <form method="POST" action="{{ route('entities.store') }}" class="space-y-3">
                @csrf
                <x-ui.input name="name" label="Name" />
                <x-ui.input name="code" label="Code" />
                <x-ui.input name="email" label="Email" type="email" />
                <x-ui.input name="currency" label="Currency" value="IDR" />
                <x-ui.button type="submit">Create</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card class="lg:col-span-2">
            <x-ui.table>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Code</th>
                        <th class="px-3 py-2 text-left">Currency</th>
                        <th class="px-3 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($entities as $entity)
                        <tr>
                            <td class="px-3 py-2">{{ $entity->name }}</td>
                            <td class="px-3 py-2">{{ $entity->code }}</td>
                            <td class="px-3 py-2">{{ $entity->currency }}</td>
                            <td class="px-3 py-2"><x-ui.badge :status="$entity->is_active ? 'paid' : 'cancelled'">{{ $entity->is_active ? 'active' : 'inactive' }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <div class="mt-3">{{ $entities->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
