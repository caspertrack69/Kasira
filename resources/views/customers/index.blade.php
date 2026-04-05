<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Customers</h2>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900">Customer Directory</h3>
                    <p class="mt-0.5 text-xs font-medium text-slate-500">Kelola data customer dalam satu panel.</p>
                </div>
                <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm shadow-slate-900/20 transition hover:bg-slate-800">
                    <i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>
                    Tambah Customer
                </button>
            </div>

            <div x-show="createOpen" x-cloak class="border-b border-slate-100 bg-slate-50/50 p-6">
                <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ui.input name="customer_number" label="Customer Number" />
                        <x-ui.input name="name" label="Name" />
                        <x-ui.input name="email" label="Email" type="email" />
                        <x-ui.input name="phone" label="Phone" />
                        <div class="md:col-span-2">
                            <x-ui.textarea name="billing_address" label="Billing Address" rows="3" />
                        </div>
                    </div>
                    <x-ui.button type="submit" class="rounded-xl px-5 py-2.5">Save Customer</x-ui.button>
                </form>
            </div>

            <div class="divide-y divide-slate-100/80">
                @forelse($customers as $customer)
                    <div x-data="{ editOpen: false }" class="bg-white transition-colors hover:bg-slate-50/30">
                        <div class="flex items-center justify-between gap-4 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-500/20">
                                    <i class="ph ph-user text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $customer->name }}</p>
                                    <p class="font-mono text-[10px] uppercase tracking-wider text-slate-500">{{ $customer->customer_number }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="hidden font-medium text-slate-600 sm:block">{{ $customer->email ?? '-' }}</span>
                                <span class="font-bold text-slate-900">{{ number_format($customer->credit_balance, 2) }}</span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="editOpen = !editOpen" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="Edit customer">
                                        <i class="ph ph-pencil-simple text-base"></i>
                                    </button>
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="m-0" onsubmit="return confirm('Hapus customer {{ addslashes($customer->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Delete customer">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div x-show="editOpen" x-cloak class="border-t border-slate-100 bg-slate-50/50 px-6 py-5">
                            <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4">
                                @csrf @method('PUT')
                                <div class="grid gap-4 md:grid-cols-2">
                                    <x-ui.input name="customer_number" label="Customer Number" :value="$customer->customer_number" />
                                    <x-ui.input name="name" label="Name" :value="$customer->name" />
                                    <x-ui.input name="email" label="Email" type="email" :value="$customer->email" />
                                    <x-ui.input name="phone" label="Phone" :value="$customer->phone" />
                                    <x-ui.input name="tax_id" label="Tax ID" :value="$customer->tax_id" />
                                    <div class="flex items-center gap-2.5 self-end pb-1">
                                        <label class="inline-flex cursor-pointer items-center gap-2">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 shadow-sm transition focus:ring-slate-900" @checked($customer->is_active)>
                                            <span class="text-sm font-medium text-slate-700">Active</span>
                                        </label>
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-ui.textarea name="billing_address" label="Billing Address" rows="2" :value="$customer->billing_address" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-ui.textarea name="shipping_address" label="Shipping Address" rows="2" :value="$customer->shipping_address" />
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-ui.button type="submit" class="rounded-xl px-5 py-2">Save Changes</x-ui.button>
                                    <x-ui.button type="button" variant="secondary" @click="editOpen = false" class="rounded-xl px-5 py-2">Cancel</x-ui.button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-12 text-slate-400">
                        <i class="ph ph-users-three mb-3 text-4xl text-slate-300"></i>
                        <p class="text-sm font-medium text-slate-500">No customers registered yet.</p>
                    </div>
                @endforelse
            </div>

            @if($customers->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">{{ $customers->links() }}</div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
