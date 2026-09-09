@php
    $reports = $analyticsData['reports'] ?? [];
    $insights = $analyticsData['insights'] ?? [];
    $income = $reports['income'] ?? [];
    $expenses = $reports['expense'] ?? [];
    $cashFlow = $reports['cash_flow'] ?? [];
    $savingsRate = $insights['savings_rate'] ?? [];
    $trend = $insights['spending_trend'] ?? [];
    $categories = $insights['categories'] ?? [];
    $health = $insights['financial_health'] ?? [];
    $concentration = $insights['spending_concentration'] ?? [];
    $incomeInsight = $insights['income'] ?? [];
@endphp

<div class="space-y-8 transition-opacity duration-200" wire:loading.class="opacity-60" wire:target="startDate,endDate">
    <section class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="mb-1 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Insights</p>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">Analytics</h1>
            <p class="mt-1 text-sm text-stone-500">Understand where your money goes and how your finances are changing.</p>
        </div>
        <div class="flex w-full gap-2 md:w-auto">
            <label class="flex-1 md:flex-none"><span class="sr-only">Start date</span><input type="date" wire:model.live="startDate" class="w-full rounded-xl border-stone-200 bg-[#fbf8f2] px-3 py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600/10"></label>
            <label class="flex-1 md:flex-none"><span class="sr-only">End date</span><input type="date" wire:model.live="endDate" class="w-full rounded-xl border-stone-200 bg-[#fbf8f2] px-3 py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600/10"></label>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['Total Income', data_get($income, 'total', 0), 'text-emerald-700'],
            ['Total Expenses', data_get($expenses, 'total', 0), 'text-red-700'],
            ['Net Cash Flow', data_get($cashFlow, 'net_cash_flow', 0), data_get($cashFlow, 'net_cash_flow', 0) >= 0 ? 'text-emerald-700' : 'text-red-700'],
        ] as [$label, $value, $color])
            <article class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-4 sm:p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-stone-500">{{ $label }}</p>
                <p class="mt-4 font-mono text-xl font-bold {{ $color }} sm:text-2xl">{{ number_format((float) $value, 0) }}</p>
                <p class="mt-1 text-xs text-stone-500">FCFA</p>
            </article>
        @endforeach
        <article class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-4 sm:p-5">
            <p class="text-xs font-medium uppercase tracking-wider text-stone-500">Savings Rate</p>
            <p class="mt-4 font-mono text-xl font-bold text-amber-700 sm:text-2xl">{{ number_format((float) data_get($savingsRate, 'rate', 0), 1) }}%</p>
            <p class="mt-1 text-xs text-stone-500">{{ number_format((float) data_get($savingsRate, 'qualifying_period_savings', 0), 0) }} FCFA saved this period</p>
            <div class="mt-3 space-y-1 border-t border-stone-200 pt-3 text-[11px] text-stone-500">
                <p class="flex justify-between gap-2"><span>Savings accounts</span><span class="font-mono text-stone-700">{{ number_format((float) data_get($savingsRate, 'net_savings_account_activity', 0), 0) }}</span></p>
                <p class="flex justify-between gap-2"><span>Additional goal saving</span><span class="font-mono text-stone-700">{{ number_format((float) data_get($savingsRate, 'qualifying_additional_goal_saving', 0), 0) }}</span></p>
            </div>
        </article>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 xl:col-span-2">
            <div class="flex items-center justify-between gap-4">
                <div><h2 class="font-semibold text-stone-900">Spending Trend</h2><p class="mt-1 text-xs text-stone-500">Current period compared with the previous equivalent period.</p></div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold capitalize text-stone-700">{{ data_get($trend, 'status', 'stable') }}</span>
            </div>
            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-stone-200 p-4"><p class="text-xs text-stone-500">Current period</p><p class="mt-2 font-mono text-lg font-bold text-stone-900">{{ number_format((float) data_get($trend, 'current_total', 0), 0) }} FCFA</p></div>
                <div class="rounded-xl border border-stone-200 p-4"><p class="text-xs text-stone-500">Previous period</p><p class="mt-2 font-mono text-lg font-bold text-stone-900">{{ number_format((float) data_get($trend, 'previous_total', 0), 0) }} FCFA</p></div>
                <div class="rounded-xl border border-stone-200 p-4"><p class="text-xs text-stone-500">Change</p><p class="mt-2 font-mono text-lg font-bold {{ data_get($trend, 'percentage_change', 0) > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format((float) data_get($trend, 'percentage_change', 0), 1) }}%</p></div>
            </div>
        </section>

        <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5">
            <h2 class="font-semibold text-stone-900">Financial Health</h2>
            <div class="mt-5 text-center"><p class="font-mono text-5xl font-bold text-emerald-700">{{ data_get($health, 'score', 0) }}</p><p class="text-xs text-stone-500">/ 100</p><p class="mt-2 text-xs font-bold uppercase tracking-[0.18em] text-stone-700">{{ data_get($health, 'status', 'poor') }}</p></div>
            <dl class="mt-5 space-y-2 border-t border-stone-200 pt-4 text-sm">
                @foreach (['Savings Rate' => number_format((float) data_get($health, 'savings_rate', 0), 1).'%', 'Cash Flow' => ucfirst(data_get($health, 'cash_flow_status', 'neutral')), 'Spending Trend' => ucfirst(data_get($health, 'spending_trend', 'stable')), 'Concentration' => ucfirst(data_get($health, 'concentration_level', 'low'))] as $label => $value)
                    <div class="flex justify-between gap-3"><dt class="text-stone-500">{{ $label }}</dt><dd class="font-medium text-stone-800">{{ $value }}</dd></div>
                @endforeach
            </dl>
        </section>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5">
            <h2 class="font-semibold text-stone-900">Spending by Category</h2><p class="mt-1 text-xs text-stone-500">How your expenses are distributed.</p>
            <div class="mt-5 space-y-5">
                @forelse ($categories as $category)
                    <div>
                        <div class="flex items-end justify-between gap-3"><div><p class="text-sm font-medium text-stone-800">{{ $category['category_name'] }}</p><p class="text-xs text-stone-500">{{ $category['transaction_count'] }} transactions</p></div><div class="text-right"><p class="font-mono text-sm font-bold text-stone-900">{{ number_format((float) $category['total'], 0) }} FCFA</p><p class="text-xs text-stone-500">{{ number_format((float) $category['percentage'], 1) }}%</p></div></div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-stone-200"><div class="h-full rounded-full bg-emerald-700" style="width: {{ min(100, max(0, (float) $category['percentage'])) }}%"></div></div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-stone-300 p-6 text-center"><p class="text-sm font-medium text-stone-700">No expense categories yet</p><p class="mt-1 text-xs text-stone-500">Category insights will appear after you record expenses.</p></div>
                @endforelse
            </div>
        </section>

        <div class="space-y-4">
            <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5">
                <h2 class="font-semibold text-stone-900">Spending Concentration</h2>
                <p class="mt-1 text-xs text-stone-500">{{ match (data_get($concentration, 'concentration_level', 'low')) { 'high' => 'A large portion of your spending is concentrated in a few categories.', 'medium' => 'Your spending is moderately concentrated across leading categories.', default => 'Your spending is distributed across categories.' } }}</p>
                <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl border border-stone-200 p-3"><dt class="text-xs text-stone-500">Top category</dt><dd class="mt-1 font-medium text-stone-800">{{ data_get($concentration, 'top_category.category_name', 'None') }}</dd></div>
                    <div class="rounded-xl border border-stone-200 p-3"><dt class="text-xs text-stone-500">Level</dt><dd class="mt-1 font-medium capitalize text-stone-800">{{ data_get($concentration, 'concentration_level', 'low') }}</dd></div>
                    <div class="rounded-xl border border-stone-200 p-3"><dt class="text-xs text-stone-500">Top two</dt><dd class="mt-1 font-mono font-bold">{{ number_format((float) data_get($concentration, 'top_two_percentage', 0), 1) }}%</dd></div>
                    <div class="rounded-xl border border-stone-200 p-3"><dt class="text-xs text-stone-500">Top three</dt><dd class="mt-1 font-mono font-bold">{{ number_format((float) data_get($concentration, 'top_three_percentage', 0), 1) }}%</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5">
                <h2 class="font-semibold text-stone-900">Income Insights</h2>
                @if ((int) data_get($incomeInsight, 'transaction_count', 0) === 0)
                    <div class="mt-4 rounded-xl border border-dashed border-stone-300 p-5 text-center"><p class="text-sm font-medium text-stone-700">No income recorded</p><p class="mt-1 text-xs text-stone-500">Income insights will appear here.</p></div>
                @else
                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        @foreach (['Average income' => number_format((float) data_get($incomeInsight, 'average_income', 0), 0).' FCFA', 'Largest income' => number_format((float) data_get($incomeInsight, 'largest_income', 0), 0).' FCFA', 'Transactions' => data_get($incomeInsight, 'transaction_count', 0), 'Trend' => ucfirst(data_get($incomeInsight, 'trend', 'stable')), 'Top category' => data_get($incomeInsight, 'top_category_name', 'Uncategorized'), 'Category share' => number_format((float) data_get($incomeInsight, 'top_category_percentage', 0), 1).'%'] as $label => $value)
                            <div class="rounded-xl border border-stone-200 p-3"><dt class="text-xs text-stone-500">{{ $label }}</dt><dd class="mt-1 font-medium text-stone-800">{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                @endif
            </section>
        </div>
    </div>

    <span class="fixed bottom-4 right-4 rounded-full border border-stone-200 bg-[#fbf8f2] px-3 py-1.5 text-xs text-stone-600 shadow-sm" wire:loading wire:target="startDate,endDate">Updating...</span>
</div>
