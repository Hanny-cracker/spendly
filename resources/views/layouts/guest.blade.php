<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- @livewireStyles --}}
    </head>
    <body class="font-sans text-stone-900 antialiased">
        <div class="min-h-screen bg-[#f3eee8] px-4 py-8 sm:px-6 lg:flex lg:items-center lg:justify-center">
            <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl border border-stone-200 bg-[#fffdf8] shadow-xl lg:grid-cols-[0.9fr_1.1fr]">
                <aside class="hidden bg-emerald-800 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <a href="/" wire:navigate class="inline-flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-xl font-bold text-emerald-800">S</span>
                        <span class="text-2xl font-semibold tracking-tight">Spendly</span>
                    </a>
                    <div>
                        <p class="font-mono text-xs uppercase tracking-[0.2em] text-emerald-200">Your money, clearly</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight">Make every franc count.</h1>
                        <p class="mt-4 max-w-sm text-sm leading-6 text-emerald-100">Track spending, plan ahead, and build better financial habits in one calm workspace.</p>
                    </div>
                    <p class="text-xs text-emerald-200">Personal finance, made simple.</p>
                </aside>

                <main class="auth-panel p-6 sm:p-10">
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-700 text-lg font-bold text-white">S</span>
                        <span class="text-xl font-semibold tracking-tight">Spendly</span>
                    </div>
                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- @livewireScripts --}}
    </body>
</html>
