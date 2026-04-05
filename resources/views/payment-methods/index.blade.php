<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">Payment Methods</h2>
    </x-slot>

    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
        <x-ui.card class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-0 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">
                <h3 class="text-base font-bold tracking-tight text-slate-900">Payment Channels</h3>
                <button type="button" @click="createOpen = !createOpen" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800"><i class="ph ph-plus transition-transform duration-200" :class="createOpen ? 'rotate-45' : ''"></i>Tambah Method</button>
            </div>

            <div x-show="createOpen" x-cloak class="border-b border-slate-100 bg-slate-50/50 p-6">
                <form method="POST" action="{{ route('payment-methods.store') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <x-ui.input name="name" label="Name" />
                    <x-ui.select name="type" label="Type" :options="['bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'virtual_account' => 'Virtual Account', 'qris' => 'QRIS', 'cheque' => 'Cheque', 'other' => 'Other']" />
                    <x-ui.input name="account_name" label="Account Name" />
                    <x-ui.input name="account_number" label="Account Number" />
                    <div class="md:col-span-2"><x-ui.button type="submit" class="rounded-xl px-5 py-2.5">Save Method</x-ui.button></div>
                </form>
            </div>

            <div class="divide-y divide-slate-100/80">
                @forelse($paymentMethods as $method)
                    <div x-data="{ editOpen: false }" class="bg-white">
                        <div class="flex items-center justify-between gap-4 px-6 py-4 transition-colors hover:bg-slate-50/30">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-500/20"><i class="ph ph-wallet text-lg"></i></div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $method->name }}</p>
                                    <p class="text-[11px] font-medium text-slate-500">{{ $method->account_number ?: '-' }} {{ $method->account_name ? '· '.$method->account_name : '' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-700">{{ str_replace('_', ' ', $method->type) }}</span>
                                <button type="button" @click="editOpen = !editOpen" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                    <i class="ph ph-pencil-simple text-base"></i>
                                </button>
                                <form method="POST" action="{{ route('payment-methods.destroy', $method) }}" class="m-0" onsubmit="return confirm('Hapus payment method {{ addslashes($method->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                        <i class="ph ph-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div x-show="editOpen" x-cloak class="border-t border-slate-100 bg-slate-50/50 px-6 py-5">
                            <form method="POST" action="{{ route('payment-methods.update', $method) }}" class="grid gap-4 md:grid-cols-2">
                                @csrf @method('PUT')
                                <x-ui.input name="name" label="Name" :value="$method->name" />
                                <x-ui.select name="type" label="Type" :options="['bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'virtual_account' => 'Virtual Account', 'qris' => 'QRIS', 'cheque' => 'Cheque', 'other' => 'Other']" :selected="$method->type" />
                                <x-ui.input name="account_name" label="Account Name" :value="$method->account_name" />
                                <x-ui.input name="account_number" label="Account Number" :value="$method->account_number" />
                                <div class="md:col-span-2 flex items-center gap-2">
                                    <x-ui.button type="submit" class="rounded-xl px-5 py-2">Save Changes</x-ui.button>
                                    <x-ui.button type="button" variant="secondary" @click="editOpen = false" class="rounded-xl px-5 py-2">Cancel</x-ui.button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-12 text-slate-400">
                        <i class="ph ph-credit-card mb-3 text-4xl text-slate-300"></i>
                        <p class="text-sm font-medium text-slate-500">No payment methods configured.</p>
                    </div>
                @endforelse
            </div>

            @if($paymentMethods->hasPages())<div class="border-t border-slate-100 bg-slate-50/30 px-6 py-4">{{ $paymentMethods->links() }}</div>@endif
        </x-ui.card>
    </div>
</x-app-layout>
