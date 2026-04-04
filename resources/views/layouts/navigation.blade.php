<nav x-data="{ open: false }" class="fixed inset-y-0 left-0 z-40 hidden w-64 border-r border-slate-200 bg-white lg:block">
    <div class="flex h-full flex-col">
        <div class="border-b border-slate-200 p-4">
            <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight text-slate-900">Kasira</a>
            <p class="text-xs text-slate-500">Multi-Entity Billing Platform</p>
        </div>

        <div class="border-b border-slate-200 p-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Active Entity</p>
            <p class="text-sm font-semibold text-slate-800">{{ $activeEntity?->name ?? 'No entity selected' }}</p>

            @php($assignableEntities = auth()->user()?->isSuperAdmin() ? \App\Models\Entity::query()->where('is_active', true)->orderBy('name')->get() : auth()->user()?->entities()->where('is_active', true)->orderBy('name')->get())
            @if($assignableEntities && $assignableEntities->isNotEmpty())
                <form method="POST" action="{{ route('entities.switch', ['entity' => $activeEntity?->id ?? $assignableEntities->first()->id]) }}" class="mt-3">
                    @csrf
                    <select onchange="this.form.action='{{ url('/entity/switch') }}/'+this.value; this.form.submit();" class="w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        @foreach($assignableEntities as $entity)
                            <option value="{{ $entity->id }}" @selected(($activeEntity?->id ?? session('active_entity_id')) === $entity->id)>{{ $entity->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        <div class="flex-1 overflow-y-auto p-2 text-sm">
            <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
            <a href="{{ route('entities.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('entities.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Entities</a>
            <a href="{{ route('users.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('users.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Users</a>
            <a href="{{ route('customers.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('customers.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Customers</a>
            <a href="{{ route('items.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('items.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Items</a>
            <a href="{{ route('taxes.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('taxes.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Taxes</a>
            <a href="{{ route('payment-methods.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('payment-methods.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Payment Methods</a>
            <a href="{{ route('invoices.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('invoices.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Invoices</a>
            <a href="{{ route('payments.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('payments.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Payments</a>
            <a href="{{ route('credit-notes.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('credit-notes.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Credit Notes</a>
            <a href="{{ route('recurring-templates.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('recurring-templates.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Recurring</a>
            <a href="{{ route('reports.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('reports.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Reports</a>
            <a href="{{ route('notification-logs.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('notification-logs.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Notifications</a>
            <a href="{{ route('audit-logs.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('audit-logs.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Audit Logs</a>
            <a href="{{ route('settings.index') }}" class="mt-1 block rounded-md px-3 py-2 {{ request()->routeIs('settings.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Settings</a>
        </div>

        <div class="border-t border-slate-200 p-4 text-sm">
            <p class="font-medium">{{ Auth::user()->name }}</p>
            <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>

            <div class="mt-3 flex gap-2">
                <a href="{{ route('profile.edit') }}" class="rounded-md bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-md bg-red-600 px-2 py-1 text-xs font-medium text-white">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<nav class="sticky top-0 z-30 border-b border-slate-200 bg-white lg:hidden">
    <div class="flex items-center justify-between px-4 py-3">
        <a href="{{ route('dashboard') }}" class="text-base font-bold text-slate-900">Kasira</a>
        <button @click="open = ! open" class="rounded-md border border-slate-300 px-2 py-1 text-xs">Menu</button>
    </div>

    <div x-show="open" class="space-y-1 border-t border-slate-200 px-4 py-3">
        <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Dashboard</a>
        <a href="{{ route('invoices.index') }}" class="block rounded-md px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Invoices</a>
        <a href="{{ route('payments.index') }}" class="block rounded-md px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Payments</a>
        <a href="{{ route('reports.index') }}" class="block rounded-md px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Reports</a>
    </div>
</nav>
