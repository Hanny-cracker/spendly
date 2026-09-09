<div class="flex h-full flex-col">

    {{-- =========================================
        BRAND
    ========================================== --}}
    <div
        class="
            flex h-20  items-center justify-between
            border-b border-stone-200/80
            px-6
        "
    >

        <a
            href="{{ route('dashboard') }}"
            class="group"
        >

            <div class="flex items-center gap-3">

                {{-- Logo mark --}}
                <div
                    class="
                        flex h-10 w-10
                        items-center
                        justify-center

                        rounded-xl
                        bg-emerald-700

                        text-lg
                        font-bold
                        text-white
                    "
                >
                    S
                </div>


                <div>

                    <p
                        class="
                            text-2xl
                            font-semibold
                            tracking-tight
                            text-stone-900
                        "
                    >
                        Spendly
                    </p>

                    <p
                        class="
                            font-mono
                            text-[9px]
                            uppercase
                            tracking-[0.18em]
                            text-stone-400
                        "
                    >
                        Expense tracker
                    </p>

                </div>

            </div>

        </a>


        {{-- Mobile close --}}
        <button
            type="button"

            @click="sidebarOpen = false"

            class="
                flex h-9 w-9
                items-center
                justify-center

                rounded-lg

                text-stone-500

                transition
                hover:bg-stone-100
                hover:text-stone-900

                lg:hidden
            "

            aria-label="Close sidebar"
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
                    d="M6 6l12 12M18 6L6 18"
                />
            </svg>

        </button>

    </div>


    {{-- =========================================
        NAVIGATION
    ========================================== --}}
    <nav
        class="
            flex-1
            overflow-y-auto

            px-4
            py-6
        "
    >

        <p
            class="
                mb-3
                px-3

                font-mono
                text-[10px]
                font-semibold
                uppercase
                tracking-[0.18em]
                text-stone-400
            "
        >
            Application
        </p>


        <div class="space-y-1">

            {{-- Dashboard --}}
            <a
                href="{{ route('dashboard') }}"
                @click="sidebarOpen = false"

                class="
                    flex items-center
                    gap-3

                    rounded-xl

                    px-3
                    py-2.5

                    text-sm
                    font-medium

                    transition

                    {{ request()->routeIs('dashboard')
                        ? 'bg-emerald-700 text-white shadow-sm'
                        : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                    }}
                "
            >

                <span class="text-lg">⌂</span>

                Dashboard

            </a>


            {{-- Analytics --}}
            @if (Route::has('analytics'))

                <a
                    href="{{ route('analytics') }}"
                    @click="sidebarOpen = false"

                    class="
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('analytics*')
                            ? 'bg-emerald-700 text-white shadow-sm'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                        }}
                    "
                >
                    <span class="text-lg">⌁</span>

                    Analytics
                </a>

            @endif


            {{-- Transactions --}}
            @if (Route::has('transactions'))

                <a
                    href="{{ route('transactions') }}"
                    @click="sidebarOpen = false"

                    class="
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('transactions*')
                            ? 'bg-emerald-700 text-white shadow-sm'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                        }}
                    "
                >
                    <span class="text-lg">↔</span>

                    Transactions
                </a>

            @endif


            {{-- Accounts --}}
            @if (Route::has('accounts'))

                <a
                    href="{{ route('accounts') }}"
                    @click="sidebarOpen = false"

                    class="
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('accounts*')
                            ? 'bg-emerald-700 text-white shadow-sm'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                        }}
                    "
                >
                    <span class="text-lg">▣</span>

                    Accounts
                </a>

            @endif


            {{-- Budgets --}}
            @if (Route::has('budgets'))

                <a
                    href="{{ route('budgets') }}"
                    @click="sidebarOpen = false"

                    class="
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('budgets*')
                            ? 'bg-emerald-700 text-white shadow-sm'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                        }}
                    "
                >
                    <span class="text-lg">◒</span>

                    Budgets
                </a>

            @endif


            {{-- Categories --}}
            @if (Route::has('categories'))

                <a
                    href="{{ route('categories') }}"
                    @click="sidebarOpen = false"

                    class="
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('categories*')
                            ? 'bg-emerald-700 text-white shadow-sm'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                        }}
                    "
                >
                    <span class="text-lg">▦</span>

                    Categories
                </a>

            @endif


            {{-- Goals --}}
            @if (Route::has('goals'))

                <a
                    href="{{ route('goals') }}"
                    @click="sidebarOpen = false"

                    class="
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('goals*')
                            ? 'bg-emerald-700 text-white shadow-sm'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                        }}
                    "
                >
                    <span class="text-lg">◎</span>

                    Goals
                </a>

            @endif


            {{-- Recurring --}}
            @if (Route::has('recurring'))

                <a
                    href="{{ route('recurring') }}"
                    @click="sidebarOpen = false"

                    class="
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('recurring*')
                            ? 'bg-emerald-700 text-white shadow-sm'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                        }}
                    "
                >
                    <span class="text-lg">↻</span>

                    Recurring
                </a>

            @endif


            {{-- Reports --}}
            @if (Route::has('reports'))

                <a
                    href="{{ route('reports') }}"
                    @click="sidebarOpen = false"

                    class="
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('reports*')
                            ? 'bg-emerald-700 text-white shadow-sm'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                        }}
                    "
                >
                    <span class="text-lg">▤</span>

                    Reports
                </a>

            @endif

        </div>


        {{-- System --}}
        <div class="my-6 border-t border-stone-200"></div>


        <p
            class="
                mb-3
                px-3

                font-mono
                text-[10px]
                font-semibold
                uppercase
                tracking-[0.18em]
                text-stone-400
            "
        >
            System
        </p>


        @if (Route::has('settings'))

            <a
                href="{{ route('settings') }}"
                @click="sidebarOpen = false"

                class="
                    flex items-center gap-3
                    rounded-xl
                    px-3 py-2.5
                    text-sm font-medium
                    transition

                    {{ request()->routeIs('settings*')
                        ? 'bg-emerald-700 text-white shadow-sm'
                        : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                    }}
                "
            >

                <span class="text-lg">⚙</span>

                Settings

            </a>

        @endif

    </nav>


    {{-- =========================================
        USER
    ========================================== --}}
    @auth

        <div
            class="
                border-t border-stone-200
                p-4
            "
        >

            <div
                class="
                    flex items-center
                    gap-3

                    rounded-2xl

                    border border-stone-200

                    bg-white/60

                    p-3
                "
            >

                <div
                    class="
                        flex h-10 w-10
                        shrink-0
                        items-center
                        justify-center

                        rounded-full

                        bg-emerald-700

                        text-sm
                        font-bold
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


                <div class="min-w-0 flex-1">

                    <p
                        class="
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
                            truncate
                            text-xs
                            text-stone-500
                        "
                    >
                        {{ auth()->user()->email }}
                    </p>

                </div>

            </div>

        </div>

    @endauth

</div>