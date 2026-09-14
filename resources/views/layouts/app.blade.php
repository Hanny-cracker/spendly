<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ $title ?? 'Spendly' }}
    </title>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])

    @livewireStyles

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>


<body class="
        min-h-screen
        overflow-x-hidden
        bg-[#f3eee8]
        text-stone-900
        antialiased
    ">

    <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen">

        {{-- =============================================
        MOBILE SIDEBAR OVERLAY
        ============================================== --}}
        <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="
            fixed inset-0 z-40
            bg-stone-950/40
            backdrop-blur-[1px]
            lg:hidden
        "></div>


        {{-- =============================================
        SIDEBAR
        ============================================== --}}
        <aside id="application-sidebar" x-cloak class="
            fixed inset-y-0 left-0 z-50

            w-[280px]

            transform
            border-r border-stone-200
            bg-[#fffdf8]

            transition-transform
            duration-300
            ease-in-out

            lg:translate-x-0
        " :class="
            sidebarOpen
                ? 'translate-x-0'
                : '-translate-x-full'
        ">

            @include('components.navigation.sidebar')

        </aside>


        {{-- =============================================
        APPLICATION
        ============================================== --}}
        <div class="
            min-h-screen
            transition-all
            duration-300
            lg:pl-[280px]
        ">

            {{-- =========================================
            TOP HEADER
            ========================================== --}}
            <header class="
                sticky top-0 z-30

                border-b border-stone-200/80

                bg-[#fffdf8]/95
                backdrop-blur-md
            ">

                <div class="
                    flex h-16
                    items-center
                    justify-between

                    px-4
                    sm:px-6
                    lg:px-8
                ">

                    {{-- Mobile --}}
                    <div class="flex items-center gap-3 lg:hidden">

                        <button type="button" @click="sidebarOpen = true" class="
                            flex h-10 w-10
                            items-center
                            justify-center

                            rounded-xl
                            border border-stone-200
                            bg-[#fffdf8]

                            text-stone-700

                            transition
                            hover:bg-stone-100
                        " aria-label="Open sidebar" :aria-expanded="sidebarOpen" aria-controls="application-sidebar">

                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>

                        </button>


                        <a href="{{ route('dashboard') }}" class="
                            text-xl
                            font-semibold
                            tracking-tight
                        ">
                            Spendly
                        </a>

                    </div>


                    {{-- Desktop page title --}}
                    <div class="hidden lg:block">

                        <p class="
                            text-sm
                            font-medium
                            text-stone-500
                        ">
                            {{ $header ?? 'Overview' }}
                        </p>

                    </div>


                    {{-- Header actions --}}
                    <div class="ml-auto flex items-center gap-2">

                        {{-- Notification --}}
                        @auth
                        <livewire:notifications.dropdown />
                        @endauth


                        {{-- Account menu --}}
                        @auth
                        <div class="border-l border-stone-200 pl-2 sm:pl-4">
                            <x-account-menu />
                        </div>
                        @endauth

                    </div>

                </div>

            </header>


            {{-- =========================================
            PAGE CONTENT
            ========================================== --}}
            <main class="
                mx-2 my-2 min-w-0 overflow-x-hidden rounded-xl bg-[#fffdf8] shadow-sm
                sm:mx-3 sm:my-4
                lg:mx-4 lg:my-5
                xl:mx-6 xl:my-6
                p-4
                sm:p-4
                lg:p-4
                xl:p-6
                
                w-auto
                max-w-[1600px]
            ">

                {{ $slot }}

            </main>

        </div>

    </div>


    @livewireScripts

</body>

</html>
