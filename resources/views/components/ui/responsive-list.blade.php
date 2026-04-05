@props(['items', 'emptyText' => 'No data found.'])

<div>
    <!-- Desktop Table -->
    <div class="hidden lg:block overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm">
        <x-ui.table class="w-full text-sm">
            <thead class="bg-slate-50/50 border-b border-slate-100">
                <tr>
                    {{ $header }}
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 bg-white">
                @forelse($items as $item)
                    <tr class="transition hover:bg-slate-50/50">
                        {{ $row(['item' => $item]) }}
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="ph ph-folder-open text-3xl mb-2"></i>
                                <p class="text-sm font-medium">{{ $emptyText }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <!-- Mobile Card List -->
    <div class="space-y-3 lg:hidden">
        @forelse($items as $item)
            <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm active:scale-[0.98] transition-transform duration-200">
                {{ $card(['item' => $item]) }}
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white/50 p-10 text-center">
                <div class="flex flex-col items-center justify-center text-slate-400">
                    <i class="ph ph-folder-open text-3xl mb-2"></i>
                    <p class="text-sm font-medium">{{ $emptyText }}</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(method_exists($items, 'links'))
        <div class="mt-6">
            {{ $items->links() }}
        </div>
    @endif
</div>
