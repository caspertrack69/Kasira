<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
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

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900">Invoice Register</h3>
                    <p class="mt-0.5 text-xs font-medium text-slate-500">Track invoices by customer and status</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
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
                        <button class="inline-flex h-9 items-center justify-center rounded-xl bg-slate-100 px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200">Filter</button>
                    </form>
                    
                    <div class="hidden h-6 w-px bg-slate-200 lg:block"></div>
                    
                    <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                        New Invoice
                    </button>
                </div>
            </div>

            <div x-show="createOpen" x-cloak 
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="border-b border-slate-100 bg-slate-50/50 p-6">
                
                <form
                    method="POST"
                    action="{{ route('invoices.store') }}"
                    class="space-y-6"
                    x-data="{
                        items: {{ \Illuminate\Support\Js::from($draftItems) }},
                        addItem() {
                            this.items.push({ description: '', quantity: 1, unit_price: 0, discount_type: '', discount_value: 0, tax_id: '' });
                        },
                        removeItem(index) {
                            if (this.items.length > 1) {
                                this.items.splice(index, 1);
                            }
                        }
                    }"
                >
                    @csrf

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-4">
                            <x-ui.select name="customer_id" label="Customer" :options="$customers->pluck('name', 'id')->all()" />
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.input name="invoice_date" label="Invoice Date" type="date" :value="old('invoice_date', now()->toDateString())" />
                                <x-ui.input name="due_date" label="Due Date" type="date" :value="old('due_date')" />
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.input name="currency" label="Currency" value="{{ old('currency', 'IDR') }}" />
                                <x-ui.input name="terms" label="Terms" />
                            </div>
                            <x-ui.textarea name="notes" label="Notes" rows="1" />
                        </div>
                    </div>

                    <div class="space-y-4 rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <h4 class="text-sm font-bold tracking-tight text-slate-900">Line Items</h4>
                            <button type="button" @click="addItem()" class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-indigo-50 px-3 text-xs font-bold text-indigo-600 transition hover:bg-indigo-100">
                                <i class="ph ph-plus"></i> Add Line
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="relative rounded-xl border border-slate-100 bg-slate-50 p-5 transition-all focus-within:border-slate-300 focus-within:bg-white focus-within:shadow-sm">
                                    <button type="button" class="absolute right-4 top-4 inline-flex h-7 w-7 items-center justify-center rounded-lg text-rose-400 transition hover:bg-rose-50 hover:text-rose-600" @click="removeItem(index)" x-show="items.length > 1">
                                        <i class="ph ph-trash text-base"></i>
                                    </button>
                                    
                                    <p class="mb-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Line <span x-text="index + 1"></span></p>

                                    <div class="grid gap-5 md:grid-cols-12">
                                        <div class="md:col-span-12">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">Description</label>
                                            <input :name="'items[' + index + '][description]'" x-model="item.description" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">Qty</label>
                                            <input :name="'items[' + index + '][quantity]'" x-model="item.quantity" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                        </div>
                                        <div class="md:col-span-5">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">Unit Price</label>
                                            <input :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">Tax</label>
                                            <select :name="'items[' + index + '][tax_id]'" x-model="item.tax_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                                <option value="">No tax</option>
                                                @foreach($taxes as $tax)
                                                    <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-6">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">Disc. Type</label>
                                            <select :name="'items[' + index + '][discount_type]'" x-model="item.discount_type" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                                <option value="">None</option>
                                                <option value="percentage">Percentage</option>
                                                <option value="fixed">Fixed</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-6">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">Disc. Value</label>
                                            <input :name="'items[' + index + '][discount_value]'" x-model="item.discount_value" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-ui.button type="submit" class="rounded-xl px-8 py-2.5 font-bold">Save Draft Invoice</x-ui.button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto bg-white">
                <x-ui.table class="w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Invoice</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Customer</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80">
                        @forelse($invoices as $invoice)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-bold text-slate-900 transition hover:text-indigo-600 hover:underline">{{ $invoice->invoice_number }}</a>
                                    <div class="mt-0.5 text-[11px] font-medium text-slate-500">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-700">{{ $invoice->customer?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <x-ui.badge :status="$invoice->status" class="rounded-lg px-2.5 py-1 text-[11px] font-semibold">{{ $invoice->status }}</x-ui.badge>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-slate-900">{{ number_format((float) $invoice->amount_due, 2) }}</td>
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
