@php
    $balance = (float) data_get(
        $dashboardData,
        'total_balance',
        0
    );

    $income = (float) data_get(
        $dashboardData,
        'reports.income.total',
        0
    );

    $expenses = (float) data_get(
        $dashboardData,
        'reports.expense.total',
        0
    );

    $savings = (float) data_get($dashboardData, 'savings_balance', 0);
    $savingsRate = (float) data_get($dashboardData, 'insights.savings_rate.rate', 0);

    $currency = 'FCFA';
@endphp
<section>
    <div class="mb-4 flex items-center gap-3">

        <h2
            class="
                font-mono text-xs font-semibold
                uppercase tracking-[0.18em]
                text-stone-500
            "
        >
            Financial overview
        </h2>

        <div class="flex-1 border-t border-dashed border-stone-300"></div>

    </div>


    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

        {{-- Balance --}}
        <div
            class="
                rounded-2xl
                border border-stone-200
                bg-[#fbf8f2]
                p-4
                sm:p-5
            "
        >
            <div class="mb-4 flex items-center justify-between">

                <span
                    class="
                        text-xs font-medium
                        uppercase tracking-wider
                        text-stone-500
                    "
                >
                    Total Balance
                </span>

                <div
                    class="
                        flex h-8 w-8 items-center justify-center
                        rounded-full bg-stone-100
                        text-stone-700
                    "
                >
                    ◉
                </div>

            </div>

            <p
                class="
                    font-mono
                    text-lg font-bold
                    tracking-tight
                    text-stone-900
                    sm:text-2xl
                "
            >
                {{ number_format($balance, 0) }}
            </p>

            <p class="mt-1 text-xs text-stone-500">
                {{ $currency }}
            </p>
        </div>


        {{-- Income --}}
        <div
            class="
                rounded-2xl
                border border-emerald-200
                bg-[#fbf8f2]
                p-4
                sm:p-5
            "
        >
            <div class="mb-4 flex items-center justify-between">

                <span
                    class="
                        text-xs font-medium
                        uppercase tracking-wider
                        text-stone-500
                    "
                >
                    Income
                </span>

                <div
                    class="
                        flex h-8 w-8 items-center justify-center
                        rounded-full bg-emerald-50
                        text-emerald-700
                    "
                >
                    ↑
                </div>

            </div>

            <p
                class="
                    font-mono
                    text-lg font-bold
                    tracking-tight
                    text-emerald-700
                    sm:text-2xl
                "
            >
                {{ number_format($income, 0) }}
            </p>

            <p class="mt-1 text-xs text-stone-500">
                {{ $currency }}
            </p>
        </div>


        {{-- Expenses --}}
        <div
            class="
                rounded-2xl
                border border-red-200
                bg-[#fbf8f2]
                p-4
                sm:p-5
            "
        >
            <div class="mb-4 flex items-center justify-between">

                <span
                    class="
                        text-xs font-medium
                        uppercase tracking-wider
                        text-stone-500
                    "
                >
                    Expenses
                </span>

                <div
                    class="
                        flex h-8 w-8 items-center justify-center
                        rounded-full bg-red-50
                        text-red-700
                    "
                >
                    ↓
                </div>

            </div>

            <p
                class="
                    font-mono
                    text-lg font-bold
                    tracking-tight
                    text-red-700
                    sm:text-2xl
                "
            >
                {{ number_format($expenses, 0) }}
            </p>

            <p class="mt-1 text-xs text-stone-500">
                {{ $currency }}
            </p>
        </div>


        {{-- Savings --}}
        <div
            class="
                rounded-2xl
                border border-amber-200
                bg-[#fbf8f2]
                p-4
                sm:p-5
            "
        >
            <div class="mb-4 flex items-center justify-between">

                <span
                    class="
                        text-xs font-medium
                        uppercase tracking-wider
                        text-stone-500
                    "
                >
                    Savings
                </span>

                <div
                    class="
                        flex h-8 w-8 items-center justify-center
                        rounded-full bg-amber-50
                        text-amber-700
                    "
                >
                    ◆
                </div>

            </div>

            <p
                class="
                    font-mono
                    text-lg font-bold
                    tracking-tight
                    text-amber-700
                    sm:text-2xl
                "
            >
                {{ number_format($savings, 0) }}
            </p>

            <p class="mt-1 text-xs text-stone-500">
                {{ $currency }}
            </p>
            <p class="mt-2 text-[11px] font-medium text-stone-500">
                {{ number_format($savingsRate, 1) }}% saved during this period
            </p>
        </div>

    </div>
</section>
