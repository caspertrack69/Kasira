<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Payment Methods</h2></x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4">
            <x-ui.card>
                <button type="button" @click="createOpen = !createOpen" class="flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold">Method Actions</h3>
                        <p class="text-xs text-slate-500">Tambah channel pembayaran sesuai kebutuhan.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">
                        <i class="ph ph-plus"></i>
                        Tambah Method
                    </span>
                </button>
            </x-ui.card>

            <x-ui.card x-show="createOpen" x-cloak x-transition>
                <h3 class="mb-3 text-sm font-semibold">Add Method</h3>
                <form method="POST" action="{{ route('payment-methods.store') }}" class="space-y-3">
                    @csrf
                    <x-ui.input name="name" label="Name" />
                    <x-ui.select name="type" label="Type" :options="['bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'virtual_account' => 'Virtual Account', 'qris' => 'QRIS', 'cheque' => 'Cheque', 'other' => 'Other']" />
                    <x-ui.input name="account_name" label="Account Name" />
                    <x-ui.input name="account_number" label="Account Number" />
                    <x-ui.button type="submit">Save</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <x-ui.card class="lg:col-span-2">
            <x-ui.table>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Account</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($paymentMethods as $method)
                        <tr>
                            <td class="px-3 py-2">{{ $method->name }}</td>
                            <td class="px-3 py-2">{{ $method->type }}</td>
                            <td class="px-3 py-2">{{ $method->account_name }} {{ $method->account_number }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
            <div class="mt-3">{{ $paymentMethods->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
