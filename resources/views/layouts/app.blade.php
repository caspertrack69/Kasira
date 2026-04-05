<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kasira') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <!-- Background diubah ke slate-50 untuk kesan soft, font disesuaikan -->
    <body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-slate-900 selection:text-white">
        @php($assignableEntities = auth()->user()?->isSuperAdmin() ? \App\Models\Entity::query()->where('is_active', true)->orderBy('name')->get() : auth()->user()?->entities()->where('is_active', true)->orderBy('name')->get())

        <div class="min-h-screen flex">
            @include('layouts.navigation')

            <main class="flex-1 lg:pl-64 flex flex-col min-h-screen transition-all duration-300">
                <!-- Header premium soft UI dengan backdrop blur -->
                <header class="sticky top-0 z-20 bg-white/70 backdrop-blur-xl border-b border-slate-200/50">
                    <div class="mx-auto flex h-16 w-full items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="text-xl font-semibold tracking-tight text-slate-900">Kasira</h1>
                            @endisset
                        </div>

                        @if($assignableEntities && $assignableEntities->isNotEmpty())
                            <div x-data="{ switcherOpen: false }" class="relative">
                                <!-- Pill button shape untuk switcher -->
                                <button type="button" @click="switcherOpen = !switcherOpen" class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200/80 bg-white px-3.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 hover:shadow">
                                    <i class="ph ph-buildings text-base text-slate-400"></i>
                                    <span class="hidden max-w-40 truncate sm:inline">{{ $activeEntity?->name ?? 'No entity selected' }}</span>
                                    <i class="ph ph-caret-down text-xs text-slate-400 transition-transform duration-200" :class="switcherOpen ? 'rotate-180' : ''"></i>
                                </button>

                                <!-- Dropdown premium rounded-2xl -->
                                <div x-show="switcherOpen" x-cloak @click.outside="switcherOpen = false" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                     class="absolute right-0 z-30 mt-2 w-72 rounded-2xl border border-slate-100 bg-white p-1.5 shadow-xl shadow-slate-200/50 ring-1 ring-black/5">
                                    
                                    <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Switch Active Entity
                                    </div>
                                    <div class="mt-1 max-h-[60vh] space-y-0.5 overflow-y-auto">
                                        @foreach($assignableEntities as $entity)
                                            <form method="POST" action="{{ route('entities.switch', $entity) }}">
                                                @csrf
                                                @php($isActive = ($activeEntity?->id ?? session('active_entity_id')) === $entity->id)
                                                <button type="submit" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm transition-colors {{ $isActive ? 'bg-slate-900 text-white font-medium' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                                                    <span class="truncate">{{ $entity->name }}</span>
                                                    @if($isActive)
                                                        <i class="ph ph-check-circle text-base"></i>
                                                    @endif
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </header>

                <div class="mx-auto w-full max-w-7xl flex-1 p-4 sm:p-6 lg:p-8">
                    @if (session('status'))
                        <x-ui.alert type="success" class="mb-5 rounded-xl border-emerald-100 bg-emerald-50 text-emerald-800 shadow-sm">
                            {{ session('status') }}
                        </x-ui.alert>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>