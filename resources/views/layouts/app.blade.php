{{-- <!DOCTYPE html>
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
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html> --}}


<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $title ?? 'Spendly' }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="min-h-screen bg-stone-100 text-gray-900">

    <div
        x-data="{ sidebarOpen: false }"
        class="min-h-screen"
    >

        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="
                fixed inset-y-0 left-0 z-50
                flex w-72 flex-col
                border-r border-gray-200
                bg-white
                transition-transform duration-300
                -translate-x-full
                lg:translate-x-0
            "
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >

            {{-- Logo --}}
            <div class="flex h-20 items-center justify-between px-6">

                <a
                    href="{{ route('dashboard') }}"
                    class="text-3xl font-semibold tracking-tight"
                >
                    Spendly
                </a>

                <button
                    type="button"
                    @click="sidebarOpen = false"
                    class="rounded-lg p-2 hover:bg-gray-100 lg:hidden"
                >
                    ✕
                </button>

            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-4 py-4">

                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Application
                </p>

                <div class="space-y-1">

                    <a
                        href="{{ route('dashboard') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>⌂</span>
                        <span>Dashboard</span>
                    </a>

                    <a
                        href="{{ route('analytics') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>◫</span>
                        <span>Analytics</span>
                    </a>

                    <a
                        href="{{ route('transactions') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>↔</span>
                        <span>Transactions</span>
                    </a>

                    <a
                        href="{{ route('accounts') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>▣</span>
                        <span>Accounts</span>
                    </a>

                    <a
                        href="{{ route('budgets') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>◒</span>
                        <span>Budgets</span>
                    </a>

                    <a
                        href="{{ route('categories') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>▦</span>
                        <span>Categories</span>
                    </a>

                    <a
                        href="{{ route('goals') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>◎</span>
                        <span>Goals</span>
                    </a>

                    <a
                        href="{{ route('recurring') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>↻</span>
                        <span>Recurring</span>
                    </a>

                    <a
                        href="{{ route('reports') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                    >
                        <span>▤</span>
                        <span>Reports</span>
                    </a>

                </div>

                <div class="my-6 border-t border-gray-200"></div>

                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    System
                </p>

                <a
                    href="{{ route('settings') }}"
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium hover:bg-gray-100"
                >
                    <span>⚙</span>
                    <span>Settings</span>
                </a>

            </nav>

            {{-- User --}}
            <div class="border-t border-gray-200 p-4">

                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-sm font-semibold text-white">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">
                            {{ auth()->user()->name ?? 'User' }}
                        </p>

                        <p class="truncate text-xs text-gray-500">
                            {{ auth()->user()->email ?? '' }}
                        </p>
                    </div>

                </div>

            </div>

        </aside>

        {{-- Main --}}
        <div class="min-h-screen lg:pl-72">

            {{-- Header --}}
            <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur">

                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">

                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="rounded-xl p-2 hover:bg-gray-100 lg:hidden"
                    >
                        ☰
                    </button>

                    <div class="hidden lg:block">
                        <span class="text-sm text-gray-500">
                            {{ $header ?? '' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">

                        <button
                            type="button"
                            class="rounded-xl p-2 hover:bg-gray-100"
                        >
                            🔔
                        </button>

                        <div class="hidden sm:block">
                            <span class="text-sm font-medium">
                                {{ auth()->user()->name ?? 'User' }}
                            </span>
                        </div>

                    </div>

                </div>

            </header>

            {{-- Page --}}
            <main class="p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

        </div>

    </div>

    @livewireScripts

</body>
</html>