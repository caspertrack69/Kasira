<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Customers</h2></x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold">Create Customer</h3>
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-3">
                @csrf
                <x-ui.input name="customer_number" label="Customer Number" />
                <x-ui.input name="name" label="Name" />
                <x-ui.input name="email" label="Email" type="email" />
                <x-ui.input name="phone" label="Phone" />
                <x-ui.textarea name="billing_address" label="Billing Address" rows="3" />
                <x-ui.button type="submit">Save</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card class="lg:col-span-2">
            <x-ui.table>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Number</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-right">Credit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($customers as $customer)
                        <tr>
                            <td class="px-3 py-2">{{ $customer->customer_number }}</td>
                            <td class="px-3 py-2">{{ $customer->name }}</td>
                            <td class="px-3 py-2">{{ $customer->email }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($customer->credit_balance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
            <div class="mt-3">{{ $customers->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
