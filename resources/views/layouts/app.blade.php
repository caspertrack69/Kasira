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
    <body class="font-sans antialiased bg-slate-100 text-slate-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <main class="lg:pl-64">
                @isset($header)
                    <header class="border-b border-slate-200 bg-white">
                        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

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
