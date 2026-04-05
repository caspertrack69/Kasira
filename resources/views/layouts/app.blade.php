<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kasira') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans antialiased text-slate-900">
        @php($assignableEntities = auth()->user()?->isSuperAdmin() ? \App\Models\Entity::query()->where('is_active', true)->orderBy('name')->get() : auth()->user()?->entities()->where('is_active', true)->orderBy('name')->get())

        <div class="min-h-screen">
            @include('layouts.navigation')

            <main class="lg:pl-64">
                <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
                    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="text-xl font-semibold">Kasira</h1>
                            @endisset
                        </div>

                        @if($assignableEntities && $assignableEntities->isNotEmpty())
                            <div x-data="{ switcherOpen: false }" class="relative">
                                <button type="button" @click="switcherOpen = !switcherOpen" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50">
                                    <i class="ph ph-buildings text-base"></i>
                                    <span class="hidden sm:inline">Entity:</span>
                                    <span class="max-w-40 truncate">{{ $activeEntity?->name ?? 'No entity selected' }}</span>
                                    <i class="ph ph-caret-down text-sm text-slate-500"></i>
                                </button>

                                <div x-show="switcherOpen" x-cloak @click.outside="switcherOpen = false" class="absolute right-0 z-30 mt-2 w-80 rounded-lg border border-slate-200 bg-white p-2 shadow-lg">
                                    <p class="px-2 py-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Switch Active Entity</p>
                                    <div class="mt-1 max-h-72 space-y-1 overflow-y-auto">
                                        @foreach($assignableEntities as $entity)
                                            <form method="POST" action="{{ route('entities.switch', $entity) }}">
                                                @csrf
                                                <button type="submit" class="flex w-full items-center justify-between rounded-md px-2 py-2 text-left text-sm {{ ($activeEntity?->id ?? session('active_entity_id')) === $entity->id ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                                                    <span class="truncate">{{ $entity->name }}</span>
                                                    @if(($activeEntity?->id ?? session('active_entity_id')) === $entity->id)
                                                        <i class="ph ph-check text-base"></i>
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

                <div class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
                    @if (session('status'))
                        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
