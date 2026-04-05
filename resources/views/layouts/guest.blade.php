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
    <body class="font-sans text-slate-800 antialiased selection:bg-slate-900 selection:text-white">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50 overflow-hidden">
            
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-slate-200/50 blur-3xl opacity-60 pointer-events-none"></div>
            <div class="absolute top-1/2 -right-32 w-80 h-80 rounded-full bg-slate-200/50 blur-3xl opacity-60 pointer-events-none"></div>

            <div class="relative z-10 w-full sm:max-w-md mt-6 px-6 py-8 sm:p-10 bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden sm:rounded-3xl transition-all">
                
                <div class="flex justify-center mb-8">
                    <a href="/" class="flex flex-col items-center gap-2 transition-transform duration-300 hover:scale-105">
                        <x-application-logo class="w-14 h-14 fill-current text-slate-900 drop-shadow-sm" />
                    </a>
                </div>

                {{ $slot }}
            </div>

            <div class="relative z-10 mt-8 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} {{ config('app.name', 'Kasira') }}. All rights reserved.
            </div>
        </div>
    </body>
</html>