<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
                <i class="ph ph-file-text text-xl text-slate-600"></i>
            </div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Invoices</h2>
        </div>
    </x-slot>

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

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="grid items-start gap-6 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-1">
            <x-ui.card class="rounded-2xl border border-slate-200/60 p-5 shadow-sm transition hover:shadow-md">
                <button type="button" @click="createOpen = !createOpen" class="group flex w-full items-center justify-between text-left">
                    <div>
                        <h3 class="text-sm font-semibold tracking-tight text-slate-900">Invoice Actions</h3>
                        <p class="mt-0.5 text-[11px] font-medium text-slate-500">Buat invoice baru</p>
                    </div>
                    <span class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm shadow-slate-900/20 transition-all hover:bg-slate-800">
                        <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                        Tambah
                    </span>
                </button>
            </x-ui.card>

            <div x-show="createOpen" x-cloak 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-4 scale-95">
                 
                <x-ui.card class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-xl shadow-slate-200/40">
                    <h3 class="mb-5 text-sm font-bold tracking-tight text-slate-900">Create Invoice</h3>

                    <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6" x-data='{"items": @js($draftItems), addItem(){ this.items.push({description:"", quantity:1, unit_price:0, discount_type:"", discount_value:0, tax_id:""}); }, removeItem(index){ if (this.items.length > 1) { this.items.splice(index, 1); } }}'>
                        @csrf

                        <div class="space-y-4">
                            <x-ui.select name="customer_id" label="Customer" :options="$customers->pluck('name', 'id')->all()" />
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.input name="invoice_date" label="Invoice Date" type="date" :value="old('invoice_date', now()->toDateString())" />
                                <x-ui.input name="due_date" label="Due Date" type="date" :value="old('due_date')" />
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.input name="currency" label="Currency" value="{{ old('currency', 'IDR') }}" />
                                <x-ui.input name="terms" label="Terms" />
                            </div>
                            <x-ui.textarea name="notes" label="Notes" rows="3" />
                        </div>

                        <div class="space-y-4 border-t border-slate-100 pt-5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-bold tracking-tight text-slate-900">Line Items</h4>
                                <button type="button" @click="addItem()" class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                                    <i class="ph ph-plus"></i> Add Line
                                </button>
                            </div>

                            <template x-for="(item, index) in items" :key="index">
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 transition-all focus-within:border-slate-300 focus-within:bg-white focus-within:shadow-sm">
                                    <div class="mb-3 flex items-center justify-between">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Line <span x-text="index + 1"></span></p>
                                        <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50 hover:text-rose-600" @click="removeItem(index)" x-show="items.length > 1">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-12">
                                        <div class="md:col-span-12">
                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Description</label>
                                            <input :name="'items[' + index + '][description]'" x-model="item.description" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Qty</label>
                                            <input :name="'items[' + index + '][quantity]'" x-model="item.quantity" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                        </div>
                                        <div class="md:col-span-8">
                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Unit Price</label>
                                            <input :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                        </div>
                                        <div class="md:col-span-6">
                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Disc. Type</label>
                                            <select :name="'items[' + index + '][discount_type]'" x-model="item.discount_type" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                                <option value="">None</option>
                                                <option value="percentage">Percentage</option>
                                                <option value="fixed">Fixed</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-6">
                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Disc. Value</label>
                                            <input :name="'items[' + index + '][discount_value]'" x-model="item.discount_value" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                        </div>
                                        <div class="md:col-span-12">
                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Tax</label>
                                            <select :name="'items[' + index + '][tax_id]'" x-model="item.tax_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
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

                        <div class="pt-2">
                            <x-ui.button type="submit" class="w-full justify-center rounded-xl py-2.5">Save Draft Invoice</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>

        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm xl:col-span-2">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900">Invoice Register</h3>
                    <p class="mt-0.5 text-xs font-medium text-slate-500">Track invoices by customer and status</p>
                </div>

                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input name="search" value="{{ request('search') }}" placeholder="Search..." class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400 sm:w-48">
                    </div>
                    <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button class="inline-flex h-9 items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <x-ui.table class="w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Invoice</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Customer</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80 bg-white">
                        @forelse($invoices as $invoice)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-bold text-slate-900 transition hover:text-indigo-600 hover:underline">{{ $invoice->invoice_number }}</a>
                                    <div class="mt-0.5 text-[11px] font-medium text-slate-500">{{ $invoice->invoice_date?->format('d M, Y') ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    {{ $invoice->customer?->name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <x-ui.badge :status="$invoice->status" class="rounded-lg px-2.5 py-1 text-[11px] font-semibold">{{ $invoice->status }}</x-ui.badge>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-slate-900">
                                    {{ number_format((float) $invoice->amount_due, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="ph ph-file-dashed mb-3 text-4xl text-slate-300"></i>
                                        <p class="text-sm font-medium text-slate-500">No invoices yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            @if($invoices->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">
                    {{ $invoices->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>