<div>
    {{-- An unexamined life is not worth living. - Socrates --}}
</div>
@php
    $spending = $dashboardData['insights']['spending'] ?? [];
@endphp

<div class="rounded-lg border bg-white p-6 shadow-sm">

    <div class="mb-6">
        <h2 class="text-lg font-semibold">
            Spending Overview
        </h2>

        <p class="text-sm text-gray-500">
            Your spending for the selected period
        </p>
    </div>


    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        {{-- Total Spent --}}
        <div>
            <p class="text-sm text-gray-500">
                Total Spent
            </p>

            <p class="mt-1 text-2xl font-bold">
                {{ number_format($spending['totalSpent'] ?? 0) }}
            </p>
        </div>


        {{-- Transactions --}}
        <div>
            <p class="text-sm text-gray-500">
                Transactions
            </p>

            <p class="mt-1 text-2xl font-bold">
                {{ $spending['transactionCount'] ?? 0 }}
            </p>
        </div>


        {{-- Average --}}
        <div>
            <p class="text-sm text-gray-500">
                Average Transaction
            </p>

            <p class="mt-1 text-2xl font-bold">
                {{ number_format($spending['averageTransaction'] ?? 0) }}
            </p>
        </div>

    </div>


    {{-- Comparison --}}
    <div class="mt-6 border-t pt-4">

        @php
            $change = $spending['changePercentage'] ?? 0;
            $trend = $spending['trend'] ?? 'stable';
        @endphp

        <div class="flex items-center justify-between">

            <div>
                <p class="text-sm text-gray-500">
                    Previous Period
                </p>

                <p class="font-semibold">
                    {{ number_format($spending['previousPeriodSpent'] ?? 0) }}
                </p>
            </div>


            <div class="text-right">

                <p class="text-sm text-gray-500">
                    Spending Trend
                </p>

                <p class="font-semibold">
                    {{ ucfirst($trend) }}
                </p>

                <p class="text-sm">
                    {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                </p>

            </div>

        </div>

    </div>

</div>