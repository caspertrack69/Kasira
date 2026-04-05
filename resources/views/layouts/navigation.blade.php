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

<!-- Mobile Navigation Header (Consolidated) -->
<nav class="sticky top-0 z-40 flex h-14 items-center justify-between border-b border-slate-200/60 bg-white/80 px-4 backdrop-blur-md lg:hidden">
    <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight text-slate-900 leading-none">Kasira</a>
    
    <div class="flex items-center gap-2">
        @if(isset($assignableEntities) && $assignableEntities->isNotEmpty())
            <div x-data="{ switcherOpen: false }" class="relative">
                <button type="button" @click="switcherOpen = !switcherOpen" class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                    <i class="ph ph-buildings text-base"></i>
                </button>
                <div x-show="switcherOpen" x-cloak @click.outside="switcherOpen = false" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="absolute right-0 z-50 mt-2 w-64 rounded-2xl border border-slate-100 bg-white p-1.5 shadow-xl ring-1 ring-black/5">
                    <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Switch Entity</div>
                    <div class="mt-1 max-h-[50vh] space-y-0.5 overflow-y-auto">
                        @foreach($assignableEntities as $entity)
                            <form method="POST" action="{{ route('entities.switch', $entity) }}">
                                @csrf
                                @php
                                    $isActive = ($activeEntity?->id ?? session('active_entity_id')) === $entity->id;
                                @endphp
                                <button type="submit" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm {{ $isActive ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <span class="truncate">{{ $entity->name }}</span>
                                    @if($isActive) <i class="ph ph-check-circle text-base"></i> @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="h-4 w-px bg-slate-200 mx-1"></div>

        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-[10px] font-bold text-white">
            {{ substr(Auth::user()->name, 0, 1) }}
        </div>
    </div>
</nav>

<!-- Mobile Bottom Navigation (PWA Style) -->
<nav x-data="{ menuOpen: false }" class="fixed bottom-0 inset-x-0 z-50 lg:hidden">
    <!-- Menu Drawer Overlay -->
    <div x-show="menuOpen" x-cloak @click="menuOpen = false" x-transition:opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
    
    <!-- Menu Drawer -->
    <div x-show="menuOpen" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="absolute bottom-0 inset-x-0 max-h-[80vh] overflow-y-auto rounded-t-[2.5rem] bg-white p-6 shadow-2xl ring-1 ring-slate-900/5 pb-24">
        
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900">App Menu</h3>
            <button @click="menuOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph ph-x text-xl"></i></button>
        </div>

        <div class="grid grid-cols-3 gap-3">
            @foreach($navItems as $item)
                @php
                    $routePattern = $item['route'];
                    $targetRoute = $item['indexRoute'] ?? $item['route'];
                    $active = request()->routeIs($routePattern);
                @endphp
                <a href="{{ route($targetRoute) }}" class="flex flex-col items-center gap-2 rounded-2xl border border-slate-100 p-4 transition-all {{ $active ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                    <i class="{{ $item['icon'] }} text-xl"></i>
                    <span class="text-[10px] font-bold uppercase tracking-tight">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-6 space-y-2">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm font-semibold text-slate-600">
                <i class="ph ph-user-circle text-xl"></i> My Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-xl bg-red-50 p-4 text-sm font-semibold text-red-600">
                    <i class="ph ph-sign-out text-xl"></i> Sign Out
                </button>
            </form>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="flex h-20 items-center justify-around border-t border-slate-200/80 bg-white/90 px-4 pb-4 backdrop-blur-xl">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('dashboard') ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="ph {{ request()->routeIs('dashboard') ? 'ph-gauge-fill' : 'ph-gauge' }} text-2xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-tight">Home</span>
        </a>
        <a href="{{ route('invoices.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('invoices.*') ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="ph {{ request()->routeIs('invoices.*') ? 'ph-file-text-fill' : 'ph-file-text' }} text-2xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-tight">Invoices</span>
        </a>
        <a href="{{ route('customers.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('customers.*') ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="ph {{ request()->routeIs('customers.*') ? 'ph-user-list-fill' : 'ph-user-list' }} text-2xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-tight">Clients</span>
        </a>
        <button @click="menuOpen = true" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-600">
            <i class="ph ph-squares-four text-2xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-tight">Menu</span>
        </button>
    </div>
</nav>