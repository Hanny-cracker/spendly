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
                <img src="{{ asset('images/spendly-logo.png') }}" alt="Spendly" class="h-14 w-44 object-contain object-left mix-blend-multiply">

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



            {{-- Transactions --}}

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



            {{-- Accounts --}}

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



            {{-- Budgets --}}

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



            {{-- Categories --}}

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



            {{-- Goals --}}

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



            {{-- Recurring --}}

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



            {{-- Reports --}}

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

            @if (Route::has('subscription'))
                <a href="{{ route('subscription') }}" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('subscription*') ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                    <span class="text-lg">◈</span>
                    Subscription
                </a>
            @endif

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

            <x-account-menu variant="sidebar" />

        </div>

    @endauth

</div>
