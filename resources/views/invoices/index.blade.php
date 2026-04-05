<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Invoices</h2></x-slot>

    @php
        $draftItems = old('items', [[
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount_type' => '',
            'discount_value' => 0,
            'tax_id' => '',
        ]]);
    @endphp

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-1">
            <x-ui.card>
                <button type="button" @click="createOpen = !createOpen" class="flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold">Invoice Actions</h3>
                        <p class="text-xs text-slate-500">Form pembuatan invoice dibuka lewat action button.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">
                        <i class="ph ph-plus"></i>
                        Tambah Invoice
                    </span>
                </button>
            </x-ui.card>

            <x-ui.card x-show="createOpen" x-cloak x-transition>
                <h3 class="mb-3 text-sm font-semibold">Create Invoice</h3>

                <form method="POST" action="{{ route('invoices.store') }}" class="space-y-4" x-data='{"items": @js($draftItems), addItem(){ this.items.push({description:"", quantity:1, unit_price:0, discount_type:"", discount_value:0, tax_id:""}); }, removeItem(index){ if (this.items.length > 1) { this.items.splice(index, 1); } }}'>
                    @csrf

                    <div class="space-y-3">
                        <x-ui.select name="customer_id" label="Customer" :options="$customers->pluck('name', 'id')->all()" />
                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-ui.input name="invoice_date" label="Invoice Date" type="date" :value="old('invoice_date', now()->toDateString())" />
                            <x-ui.input name="due_date" label="Due Date" type="date" :value="old('due_date')" />
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-ui.input name="currency" label="Currency" value="{{ old('currency', 'IDR') }}" />
                            <x-ui.input name="terms" label="Terms" />
                        </div>
                        <x-ui.textarea name="notes" label="Notes" rows="3" />
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold">Line Items</h4>
                            <x-ui.button type="button" variant="secondary" @click="addItem()">Add Line</x-ui.button>
                        </div>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Line <span x-text="index + 1"></span></p>
                                    <button type="button" class="text-xs font-medium text-red-600" @click="removeItem(index)" x-show="items.length > 1">Remove</button>
                                </div>

                                <div class="grid gap-3 md:grid-cols-12">
                                    <div class="md:col-span-5">
                                        <label class="block text-sm font-medium text-slate-700">Description</label>
                                        <input :name="'items[' + index + '][description]'" x-model="item.description" type="text" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-sm font-medium text-slate-700">Qty</label>
                                        <input :name="'items[' + index + '][quantity]'" x-model="item.quantity" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-slate-700">Unit Price</label>
                                        <input :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-slate-700">Discount Type</label>
                                        <select :name="'items[' + index + '][discount_type]'" x-model="item.discount_type" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                            <option value="">None</option>
                                            <option value="percentage">Percentage</option>
                                            <option value="fixed">Fixed</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-slate-700">Discount Value</label>
                                        <input :name="'items[' + index + '][discount_value]'" x-model="item.discount_value" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    </div>
                                    <div class="md:col-span-12">
                                        <label class="block text-sm font-medium text-slate-700">Tax</label>
                                        <select :name="'items[' + index + '][tax_id]'" x-model="item.tax_id" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                            <option value="">No tax</option>
                                            @foreach($taxes as $tax)
                                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <x-ui.button type="submit">Save Draft</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <x-ui.card class="xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold">Invoice Register</h3>
                    <p class="text-xs text-slate-500">Track invoices by customer, status, and search term.</p>
                </div>

                <form method="GET" class="flex flex-wrap gap-2">
                    <input name="search" value="{{ request('search') }}" placeholder="Search invoice" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <select name="status" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">Filter</button>
                </form>
            </div>

            <div class="mt-4">
                <x-ui.table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Invoice</th>
                            <th class="px-3 py-2 text-left">Customer</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="px-3 py-2">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-slate-900 hover:underline">{{ $invoice->invoice_number }}</a>
                                    <div class="text-xs text-slate-500">{{ $invoice->invoice_date?->format('Y-m-d') }}</div>
                                </td>
                                <td class="px-3 py-2">{{ $invoice->customer?->name ?? '-' }}</td>
                                <td class="px-3 py-2"><x-ui.badge :status="$invoice->status">{{ $invoice->status }}</x-ui.badge></td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $invoice->amount_due, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-slate-500">No invoices yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            <div class="mt-4">{{ $invoices->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
