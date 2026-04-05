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

<!-- Desktop Sidebar (Modern Clean) -->
<nav class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200/60 bg-white lg:flex">
    <div class="flex h-16 shrink-0 items-center px-6">
        <a href="{{ route('dashboard') }}" class="flex flex-col">
            <span class="text-xl font-bold tracking-tight text-slate-900">Kasira</span>
            <span class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Multi-Entity Billing</span>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4 text-sm font-medium">
        <div class="space-y-1">
            @foreach($navItems as $item)
                @php
                    $routePattern = $item['route'];
                    $targetRoute = $item['indexRoute'] ?? $item['route'];
                    $active = request()->routeIs($routePattern);
                @endphp
                <a href="{{ route($targetRoute) }}" 
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200 {{ $active ? 'bg-slate-900 text-white shadow-md shadow-slate-900/10' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i class="{{ $item['icon'] }} text-[1.15rem] {{ $active ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- User Profile Widget -->
    <div class="p-4">
        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white shadow-sm">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('profile.edit') }}" class="flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                    <i class="ph ph-user-circle text-sm"></i> Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-transparent bg-red-50 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 hover:text-red-700">
                        <i class="ph ph-sign-out text-sm"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Navigation Header -->
<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/60 bg-white/80 backdrop-blur-md lg:hidden">
    <div class="flex h-14 items-center justify-between px-4">
        <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight text-slate-900">Kasira</a>
        <button @click="open = !open" class="flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
            <i class="ph" :class="open ? 'ph-x' : 'ph-list'"></i>
        </button>
    </div>

    <div x-show="open" x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute inset-x-0 top-14 h-[calc(100vh-3.5rem)] overflow-y-auto border-t border-slate-100 bg-white px-4 py-4 pb-20 shadow-xl">
        <div class="space-y-1">
            @foreach($navItems as $item)
                @php
                    $routePattern = $item['route'];
                    $targetRoute = $item['indexRoute'] ?? $item['route'];
                    $active = request()->routeIs($routePattern);
                @endphp
                <a href="{{ route($targetRoute) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors {{ $active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="{{ $item['icon'] }} text-lg {{ $active ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</nav>