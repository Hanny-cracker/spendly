<div class="flex h-full flex-col">

    {{-- Brand --}}
    <div class="flex items-center justify-between px-6 py-6">

        <a
            href="{{ route('dashboard') }}"
            class="block"
        >
            <div class="text-3xl font-semibold tracking-tight">
                Spendly
            </div>

            <div class="mt-1 text-xs text-gray-500">
                Expense Tracker
            </div>
        </a>

        {{-- Mobile close --}}
        <button
            type="button"
            @click="sidebarOpen = false"
            class="rounded-lg p-2 hover:bg-gray-200 lg:hidden"
            aria-label="Close navigation"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>

    </div>


    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-4">

        <p class="px-3 pb-3 pt-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
            Application
        </p>


        {{-- Dashboard --}}
        <a
            href="{{ route('dashboard') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('dashboard')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>⌂</span>
            <span>Dashboard</span>
        </a>


        {{-- Analytics --}}
        <a
            href="{{ route('analytics') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('analytics')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>◫</span>
            <span>Analytics</span>
        </a>


        {{-- Transactions --}}
        <a
            href="{{ route('transactions') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('transactions')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>↔</span>
            <span>Transactions</span>
        </a>


        {{-- Accounts --}}
        <a
            href="{{ route('accounts') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('accounts')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>▣</span>
            <span>Accounts</span>
        </a>


        {{-- Budgets --}}
        <a
            href="{{ route('budgets') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('budgets')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>◉</span>
            <span>Budgets</span>
        </a>


        {{-- Categories --}}
        <a
            href="{{ route('categories') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('categories')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>⌁</span>
            <span>Categories</span>
        </a>


        {{-- Goals --}}
        <a
            href="{{ route('goals') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('goals')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>◎</span>
            <span>Goals</span>
        </a>


        {{-- Recurring --}}
        <a
            href="{{ route('recurring') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('recurring')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>↻</span>
            <span>Recurring</span>
        </a>


        {{-- Reports --}}
        <a
            href="{{ route('reports') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('reports')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>▤</span>
            <span>Reports</span>
        </a>


        <div class="my-4 border-t border-gray-200"></div>


        {{-- Settings --}}
        <a
            href="{{ route('settings') }}"
            @click="sidebarOpen = false"
            class="
                mb-1 flex items-center gap-3 rounded-xl px-3 py-3
                text-sm font-medium
                {{ request()->routeIs('settings')
                    ? 'bg-white shadow-sm text-gray-900'
                    : 'text-gray-600 hover:bg-white/70' }}
            "
        >
            <span>⚙</span>
            <span>Settings</span>
        </a>

    </nav>


    {{-- Privacy card --}}
    <div class="p-4">

        <div class="rounded-2xl bg-white p-4 shadow-sm">

            <div class="mb-2 text-sm font-semibold">
                🛡 Secure. Private.
            </div>

            <p class="text-xs leading-5 text-gray-500">
                Your financial data is private and only visible to you.
            </p>

        </div>

    </div>

</div>