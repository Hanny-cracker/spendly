<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

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


<body
    class="
        min-h-screen
        bg-[#f5f2eb]
        text-stone-900
        antialiased
    "
>

<div
    x-data="{ sidebarOpen: false }"
    class="min-h-screen"
>

    {{-- =============================================
        MOBILE SIDEBAR OVERLAY
    ============================================== --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="
            fixed inset-0 z-40
            bg-stone-950/40
            backdrop-blur-[1px]
            lg:hidden
        "
    ></div>


    {{-- =============================================
        SIDEBAR
    ============================================== --}}
    <aside
        x-cloak
        class="
            fixed inset-y-0 left-0 z-50

            w-[280px]

            transform
            border-r border-stone-200
            bg-[#fbf8f2]

            transition-transform
            duration-300
            ease-in-out

            lg:translate-x-0
        "

        :class="
            sidebarOpen
                ? 'translate-x-0'
                : '-translate-x-full'
        "
    >

        @include('components.navigation.sidebar')

    </aside>


    {{-- =============================================
        APPLICATION
    ============================================== --}}
    <div
        class="
            min-h-screen
            transition-all
            duration-300
            lg:pl-[280px]
        "
    >

        {{-- =========================================
            TOP HEADER
        ========================================== --}}
        <header
            class="
                sticky top-0 z-30

                border-b border-stone-200/80

                bg-[#f5f2eb]/95
                backdrop-blur-md
            "
        >

            <div
                class="
                    flex h-16
                    items-center
                    justify-between

                    px-4
                    sm:px-6
                    lg:px-8
                "
            >

                {{-- Mobile --}}
                <div class="flex items-center gap-3 lg:hidden">

                    <button
                        type="button"
                        @click="sidebarOpen = true"

                        class="
                            flex h-10 w-10
                            items-center
                            justify-center

                            rounded-xl
                            border border-stone-200
                            bg-[#fbf8f2]

                            text-stone-700

                            transition
                            hover:bg-stone-100
                        "

                        aria-label="Open sidebar"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>

                    </button>


                    <a
                        href="{{ route('dashboard') }}"
                        class="
                            text-xl
                            font-semibold
                            tracking-tight
                        "
                    >
                        Spendly
                    </a>

                </div>


                {{-- Desktop page title --}}
                <div class="hidden lg:block">

                    <p
                        class="
                            text-sm
                            font-medium
                            text-stone-500
                        "
                    >
                        {{ $header ?? 'Overview' }}
                    </p>

                </div>


                {{-- Header actions --}}
                <div class="ml-auto flex items-center gap-2">

                    {{-- Notification --}}
                    <button
                        type="button"

                        class="
                            relative

                            flex h-10 w-10
                            items-center
                            justify-center

                            rounded-xl

                            text-stone-600

                            transition
                            hover:bg-[#fbf8f2]
                            hover:text-stone-900
                        "

                        aria-label="Notifications"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="
                                    M18 8a6 6 0 0 0-12 0
                                    c0 7-3 7-3 9
                                    h18
                                    c0-2-3-2-3-9
                                "
                            />

                            <path
                                stroke-linecap="round"
                                d="M10 21h4"
                            />
                        </svg>

                        {{-- Notification indicator --}}
                        <span
                            class="
                                absolute
                                right-2 top-2

                                h-2 w-2

                                rounded-full
                                bg-emerald-600

                                ring-2
                                ring-[#f5f2eb]
                            "
                        ></span>

                    </button>


                    {{-- Desktop user --}}
                    @auth

                        <div
                            class="
                                hidden
                                items-center
                                gap-3

                                border-l
                                border-stone-200

                                pl-4

                                sm:flex
                            "
                        >

                            <div class="text-right">

                                <p
                                    class="
                                        max-w-40
                                        truncate

                                        text-sm
                                        font-semibold
                                        text-stone-800
                                    "
                                >
                                    {{ auth()->user()->name }}
                                </p>

                                <p
                                    class="
                                        max-w-40
                                        truncate

                                        text-xs
                                        text-stone-500
                                    "
                                >
                                    {{ auth()->user()->email }}
                                </p>

                            </div>


                            <div
                                class="
                                    flex h-9 w-9
                                    items-center
                                    justify-center

                                    rounded-full

                                    bg-emerald-700

                                    text-sm
                                    font-semibold
                                    text-white
                                "
                            >
                                {{ strtoupper(
                                    substr(
                                        auth()->user()->name ?? 'U',
                                        0,
                                        1
                                    )
                                ) }}
                            </div>

                        </div>

                    @endauth

                </div>

            </div>

        </header>


        {{-- =========================================
            PAGE CONTENT
        ========================================== --}}
        <main
            class="
                mx-auto
                w-full
                max-w-[1600px]

                p-4
                sm:p-6
                lg:p-8
                xl:p-10
            "
        >

            {{ $slot }}

        </main>

    </div>

</div>


@livewireScripts

</body>

</html>