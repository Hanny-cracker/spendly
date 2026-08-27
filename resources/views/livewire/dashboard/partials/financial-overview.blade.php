<div class="space-y-6">

    {{-- Section Header --}}
    <div>
        <h2 class="text-lg font-semibold text-gray-900">
            Financial Overview
        </h2>

        <p class="text-sm text-gray-500">
            A summary of your financial activity for the selected period.
        </p>
    </div>


    {{-- Main Financial Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Income --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Income
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ number_format($dashboardData['reports']['income']['total'] ?? 0, 0) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100">
                    <svg
                        class="h-5 w-5 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 19V5m0 0l-6 6m6-6l6 6"
                        />
                    </svg>
                </div>

            </div>

            <div class="mt-4 flex items-center justify-between text-xs">

                <span class="text-gray-500">
                    {{ $dashboardData['reports']['income']['transactionCount'] ?? 0 }}
                    transactions
                </span>

                <span class="font-medium text-green-600">
                    Avg.
                    {{ number_format($dashboardData['reports']['income']['average'] ?? 0, 0) }}
                </span>

            </div>

        </div>


        {{-- Expenses --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Expenses
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ number_format($dashboardData['reports']['expense']['total'] ?? 0, 0) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100">
                    <svg
                        class="h-5 w-5 text-red-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 5v14m0 0l6-6m-6 6l-6-6"
                        />
                    </svg>
                </div>

            </div>

            <div class="mt-4 flex items-center justify-between text-xs">

                <span class="text-gray-500">
                    {{ $dashboardData['reports']['expense']['transactionCount'] ?? 0 }}
                    transactions
                </span>

                <span class="font-medium text-red-600">
                    Avg.
                    {{ number_format($dashboardData['reports']['expense']['average'] ?? 0, 0) }}
                </span>

            </div>

        </div>


        {{-- Net Cash Flow --}}
        @php
            $netCashFlow =
                $dashboardData['reports']['cash_flow']['netCashFlow'] ?? 0;

            $isPositiveCashFlow = $netCashFlow >= 0;
        @endphp

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Net Cash Flow
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold
                        {{ $isPositiveCashFlow ? 'text-green-600' : 'text-red-600' }}"
                    >
                        {{ number_format($netCashFlow, 0) }}
                    </p>
                </div>

                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl
                    {{ $isPositiveCashFlow ? 'bg-green-100' : 'bg-red-100' }}"
                >
                    <svg
                        class="h-5 w-5
                        {{ $isPositiveCashFlow ? 'text-green-600' : 'text-red-600' }}"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12h18m-6-6l6 6-6 6"
                        />
                    </svg>
                </div>

            </div>

            <div class="mt-4 flex items-center justify-between text-xs">

                <span class="text-gray-500">
                    Income − Expenses
                </span>

                <span
                    class="font-medium
                    {{ $isPositiveCashFlow ? 'text-green-600' : 'text-red-600' }}"
                >
                    {{ $isPositiveCashFlow ? 'Positive' : 'Negative' }}
                </span>

            </div>

        </div>


        {{-- Savings Rate --}}
        @php
            $savingsRate =
                $dashboardData['insights']['savings_rate']['rate'] ?? 0;

            $savings =
                $dashboardData['insights']['savings_rate']['savings'] ?? 0;
        @endphp

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Savings Rate
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ number_format($savingsRate, 1) }}%
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                    <svg
                        class="h-5 w-5 text-blue-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v2m0 8v2m9-6a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>

            </div>

            <div class="mt-4 flex items-center justify-between text-xs">

                <span class="text-gray-500">
                    Saved
                </span>

                <span class="font-medium text-blue-600">
                    {{ number_format($savings, 0) }}
                </span>

            </div>

        </div>

    </div>


    {{-- Detailed Financial Summary --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Income Breakdown --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <div class="mb-5">
                <h3 class="font-semibold text-gray-900">
                    Income Summary
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Income generated during this period.
                </p>
            </div>

            <div class="space-y-4">

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Total income
                    </span>

                    <span class="font-semibold text-gray-900">
                        {{ number_format($dashboardData['reports']['income']['total'] ?? 0, 0) }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Transactions
                    </span>

                    <span class="font-semibold text-gray-900">
                        {{ $dashboardData['reports']['income']['transactionCount'] ?? 0 }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Average transaction
                    </span>

                    <span class="font-semibold text-gray-900">
                        {{ number_format($dashboardData['reports']['income']['average'] ?? 0, 0) }}
                    </span>
                </div>

            </div>

        </div>


        {{-- Expense Breakdown --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <div class="mb-5">
                <h3 class="font-semibold text-gray-900">
                    Expense Summary
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Spending during this period.
                </p>
            </div>

            <div class="space-y-4">

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Total expenses
                    </span>

                    <span class="font-semibold text-gray-900">
                        {{ number_format($dashboardData['reports']['expense']['total'] ?? 0, 0) }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Transactions
                    </span>

                    <span class="font-semibold text-gray-900">
                        {{ $dashboardData['reports']['expense']['transactionCount'] ?? 0 }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Average transaction
                    </span>

                    <span class="font-semibold text-gray-900">
                        {{ number_format($dashboardData['reports']['expense']['average'] ?? 0, 0) }}
                    </span>
                </div>

            </div>

        </div>


        {{-- Cash Flow Summary --}}
        @php
            $cashFlow =
                $dashboardData['reports']['cash_flow'] ?? [];

            $cashFlowIncome =
                $cashFlow['income'] ?? 0;

            $cashFlowExpenses =
                $cashFlow['expenses'] ?? 0;

            $cashFlowNet =
                $cashFlow['netCashFlow'] ?? 0;

            $cashFlowPositive =
                $cashFlowNet >= 0;
        @endphp

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <div class="mb-5">
                <h3 class="font-semibold text-gray-900">
                    Cash Flow
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Money coming in versus money going out.
                </p>
            </div>

            <div class="space-y-4">

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Money in
                    </span>

                    <span class="font-semibold text-green-600">
                        {{ number_format($cashFlowIncome, 0) }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Money out
                    </span>

                    <span class="font-semibold text-red-600">
                        {{ number_format($cashFlowExpenses, 0) }}
                    </span>
                </div>

                <div class="flex items-center justify-between border-t pt-4">
                    <span class="text-sm font-medium text-gray-700">
                        Net cash flow
                    </span>

                    <span
                        class="font-bold
                        {{ $cashFlowPositive
                            ? 'text-green-600'
                            : 'text-red-600' }}"
                    >
                        {{ number_format($cashFlowNet, 0) }}
                    </span>
                </div>

            </div>

        </div>

    </div>


    {{-- Financial Health --}}
    @php
        $financialHealth =
            $dashboardData['insights']['financial_health'] ?? [];

        $healthScore =
            $financialHealth['score'] ?? 0;

        $healthStatus =
            $financialHealth['status'] ?? 'unknown';

        $healthStatusLabel = match ($healthStatus) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Poor',
            default => ucfirst($healthStatus),
        };
    @endphp

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

            <div>
                <h3 class="font-semibold text-gray-900">
                    Financial Health
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Overall financial performance for the selected period.
                </p>
            </div>

            <div class="flex items-center gap-4">

                <div class="text-right">
                    <p class="text-xs text-gray-500">
                        Health Score
                    </p>

                    <p class="text-2xl font-bold text-gray-900">
                        {{ $healthScore }}/100
                    </p>
                </div>

                <div class="rounded-full bg-gray-100 px-4 py-2">
                    <span class="text-sm font-semibold text-gray-700">
                        {{ $healthStatusLabel }}
                    </span>
                </div>

            </div>

        </div>

    </div>

</div>