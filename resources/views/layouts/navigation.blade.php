@php
    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'ph ph-gauge'],
        ['route' => 'entities.*', 'indexRoute' => 'entities.index', 'label' => 'Entities', 'icon' => 'ph ph-buildings'],
        ['route' => 'users.*', 'indexRoute' => 'users.index', 'label' => 'Users', 'icon' => 'ph ph-users-three'],
        ['route' => 'customers.*', 'indexRoute' => 'customers.index', 'label' => 'Customers', 'icon' => 'ph ph-user-list'],
        ['route' => 'items.*', 'indexRoute' => 'items.index', 'label' => 'Items', 'icon' => 'ph ph-package'],
        ['route' => 'taxes.*', 'indexRoute' => 'taxes.index', 'label' => 'Taxes', 'icon' => 'ph ph-percent'],
        ['route' => 'payment-methods.*', 'indexRoute' => 'payment-methods.index', 'label' => 'Payment Methods', 'icon' => 'ph ph-credit-card'],
        ['route' => 'invoices.*', 'indexRoute' => 'invoices.index', 'label' => 'Invoices', 'icon' => 'ph ph-file-text'],
        ['route' => 'payments.*', 'indexRoute' => 'payments.index', 'label' => 'Payments', 'icon' => 'ph ph-hand-coins'],
        ['route' => 'credit-notes.*', 'indexRoute' => 'credit-notes.index', 'label' => 'Credit Notes', 'icon' => 'ph ph-receipt'],
        ['route' => 'recurring-templates.*', 'indexRoute' => 'recurring-templates.index', 'label' => 'Recurring', 'icon' => 'ph ph-arrows-clockwise'],
        ['route' => 'reports.*', 'indexRoute' => 'reports.index', 'label' => 'Reports', 'icon' => 'ph ph-chart-line-up'],
        ['route' => 'notification-logs.*', 'indexRoute' => 'notification-logs.index', 'label' => 'Notifications', 'icon' => 'ph ph-bell'],
        ['route' => 'audit-logs.*', 'indexRoute' => 'audit-logs.index', 'label' => 'Audit Logs', 'icon' => 'ph ph-shield-check'],
        ['route' => 'settings.*', 'indexRoute' => 'settings.index', 'label' => 'Settings', 'icon' => 'ph ph-gear'],
    ];
@endphp

<nav x-data="{ open: false }" class="fixed inset-y-0 left-0 z-40 hidden w-64 border-r border-slate-200 bg-white lg:block">
    <div class="flex h-full flex-col">
        <div class="border-b border-slate-200 p-4">
            <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight text-slate-900">Kasira</a>
            <p class="text-xs text-slate-500">Multi-Entity Billing Platform</p>
        </div>

        <div class="flex-1 overflow-y-auto p-2 text-sm">
            @foreach($navItems as $item)
                @php
                    $routePattern = $item['route'];
                    $targetRoute = $item['indexRoute'] ?? $item['route'];
                    $active = request()->routeIs($routePattern);
                @endphp
                <a href="{{ route($targetRoute) }}" class="mt-1 flex items-center gap-2 rounded-md px-3 py-2 {{ $active ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i class="{{ $item['icon'] }} text-base"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="border-t border-slate-200 p-4 text-sm">
            <p class="font-medium">{{ Auth::user()->name }}</p>
            <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>

            <div class="mt-3 flex gap-2">
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1 rounded-md bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700">
                    <i class="ph ph-user-circle text-sm"></i>
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="inline-flex items-center gap-1 rounded-md bg-red-600 px-2 py-1 text-xs font-medium text-white">
                        <i class="ph ph-sign-out text-sm"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white lg:hidden">
    <div class="flex items-center justify-between px-4 py-3">
        <a href="{{ route('dashboard') }}" class="text-base font-bold text-slate-900">Kasira</a>
        <button @click="open = !open" class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-1 text-xs">
            <i class="ph ph-list text-sm"></i>
            Menu
        </button>
    </div>

    <div x-show="open" x-cloak class="space-y-1 border-t border-slate-200 px-4 py-3">
        @foreach($navItems as $item)
            @php
                $routePattern = $item['route'];
                $targetRoute = $item['indexRoute'] ?? $item['route'];
            @endphp
            <a href="{{ route($targetRoute) }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm {{ request()->routeIs($routePattern) ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="{{ $item['icon'] }} text-base"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
