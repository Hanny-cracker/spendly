@php($cashFlow = $report['cash_flow'] ?? [])
@php($summary = $report['transaction_summary'] ?? [])
@php($expenses = $report['expense_categories'] ?? [])
@php($income = $report['income_categories'] ?? [])
@php($accounts = $report['account_activity'] ?? [])
@php($insights = $report['insights'] ?? [])
<article class="space-y-7" data-report-content>
    <header class="border-b border-stone-300 pb-5">
        <h2 class="text-2xl font-semibold">Financial Report</h2>
        <p class="mt-1 font-mono text-sm text-stone-500">{{ \Carbon\Carbon::parse($report['start_date'])->format('d M
            Y') }} – {{ \Carbon\Carbon::parse($report['end_date'])->format('d M Y') }}</p>
    </header>
    @if(($summary['total_transactions'] ?? 0) === 0)<section
        class="rounded-2xl border border-dashed border-stone-300 bg-[#fbf8f2] p-10 text-center">
        <h3 class="font-semibold">No financial activity for this period.</h3>
        <p class="mt-1 text-sm text-stone-500">Try another date range.</p>
    </section>@endif
    <section>
        <h3 class="mb-4 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Financial Summary
        </h3>
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">@foreach([['Income',$cashFlow['total_income'] ??
            0,'text-emerald-700'],['Expenses',$cashFlow['total_expenses'] ?? 0,'text-red-700'],['Net Cash
            Flow',$cashFlow['net_cash_flow'] ?? 0,($cashFlow['net_cash_flow'] ??
            0)>=0?'text-emerald-700':'text-red-700']] as [$label,$value,$color])<div
                class="rounded-2xl border bg-[#fbf8f2] p-4">
                <p class="text-xs uppercase text-stone-500">{{ $label }}</p>
                <p class="mt-3 font-mono text-xl font-bold {{ $color }}">{{ number_format($value,0) }}</p>
                <p class="text-xs text-stone-500">FCFA</p>
            </div>@endforeach<div class="rounded-2xl border bg-[#fbf8f2] p-4">
                <p class="text-xs uppercase text-stone-500">Savings Rate</p>
                <p class="mt-3 font-mono text-xl font-bold text-amber-700">{{ number_format($cashFlow['savings_rate'] ??
                    0,1) }}%</p>
            </div>
        </div>
    </section>
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border bg-[#fbf8f2] p-5">
            <h3 class="font-semibold">Cash Flow</h3>
            <dl class="mt-5 space-y-3 text-sm">@foreach(['Total Income'=>number_format($cashFlow['total_income'] ??
                0,0).' FCFA','Total Expenses'=>number_format($cashFlow['total_expenses'] ?? 0,0).' FCFA','Net Cash
                Flow'=>number_format($cashFlow['net_cash_flow'] ?? 0,0).' FCFA','Income
                Transactions'=>$cashFlow['income_transaction_count'] ?? 0,'Expense
                Transactions'=>$cashFlow['expense_transaction_count'] ?? 0] as $label=>$value)<div
                    class="flex justify-between border-b border-stone-200 pb-2 last:border-0">
                    <dt class="text-stone-500">{{ $label }}</dt>
                    <dd class="font-mono font-semibold">{{ $value }}</dd>
                </div>@endforeach</dl>
        </section>
        <section class="rounded-2xl border bg-[#fbf8f2] p-5">
            <h3 class="font-semibold">Transactions</h3>
            <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">@foreach(['Total
                Transactions'=>$summary['total_transactions'] ?? 0,'Income
                Transactions'=>$summary['income_transactions'] ?? 0,'Expense
                Transactions'=>$summary['expense_transactions'] ?? 0,'Largest
                Expense'=>number_format($summary['largest_expense'] ?? 0,0).' FCFA','Largest
                Income'=>number_format($summary['largest_income'] ?? 0,0).' FCFA','Average
                Expense'=>number_format($summary['average_expense'] ?? 0,0).' FCFA'] as $label=>$value)<div
                    class="rounded-xl border p-3">
                    <dt class="text-xs text-stone-500">{{ $label }}</dt>
                    <dd class="mt-1 font-mono font-semibold">{{ $value }}</dd>
                </div>@endforeach</dl>
        </section>
    </div>
    <div class="grid gap-4 lg:grid-cols-2">@foreach([['Expenses by Category',$expenses,'bg-red-600'],['Income by
        Category',$income,'bg-emerald-700']] as [$heading,$categories,$bar])<section
            class="rounded-2xl border bg-[#fbf8f2] p-5">
            <h3 class="font-semibold">{{ $heading }}</h3>
            <div class="mt-5 space-y-4">@forelse($categories as $category)<div>
                    <div class="flex justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium">{{ $category['category_name'] }}</p>
                            <p class="text-xs text-stone-500">{{ $category['transaction_count'] }} {{
                                $category['transaction_count']===1?'transaction':'transactions' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-mono text-sm font-bold">{{ number_format($category['total'],0) }} FCFA</p>
                            <p class="text-xs text-stone-500">{{ number_format($category['percentage'],1) }}%</p>
                        </div>
                    </div>
                    <div class="mt-2 h-1.5 rounded-full bg-stone-200">
                        <div class="h-full rounded-full {{ $bar }}"
                            style="width:{{ min(100,$category['percentage']) }}%"></div>
                    </div>
                </div>@empty<p class="text-sm text-stone-500">No activity in this category group.</p>@endforelse</div>
        </section>@endforeach</div>
    <section class="rounded-2xl border bg-[#fbf8f2] p-5">
        <h3 class="font-semibold">Account Summary</h3>
        <p class="mt-1 text-xs text-stone-500">Activity during the selected period—not historical closing balances.</p>
        <div class="mt-5 space-y-3">@foreach($accounts as $account)<div
                class="grid gap-2 rounded-xl border p-4 sm:grid-cols-[1fr_repeat(4,auto)] sm:items-center sm:gap-6">
                <p class="font-medium">{{ $account['name'] }}</p>
                <p class="text-xs"><span class="text-stone-500">Income</span><br><span
                        class="font-mono text-emerald-700">{{ number_format($account['income'],0) }}</span></p>
                <p class="text-xs"><span class="text-stone-500">Expenses</span><br><span
                        class="font-mono text-red-700">{{ number_format($account['expenses'],0) }}</span></p>
                <p class="text-xs"><span class="text-stone-500">Net Activity</span><br><span
                        class="font-mono font-semibold">{{ number_format($account['net_activity'],0) }}</span></p>
                <p class="text-xs"><span class="text-stone-500">Transactions</span><br><span class="font-mono">{{
                        $account['transaction_count'] }}</span></p>
            </div>@endforeach</div>
    </section>
    @if(($summary['total_transactions'] ?? 0) > 0)<section class="rounded-2xl border bg-[#fbf8f2] p-5">
        <h3 class="font-semibold">Report Insights</h3>
        <dl class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border p-3">
                <dt class="text-xs text-stone-500">Largest spending category</dt>
                <dd class="mt-1 font-medium">{{ $expenses[0]['category_name'] ?? 'None' }}@if(isset($expenses[0])) — {{
                    number_format($expenses[0]['total'],0) }} FCFA @endif</dd>
            </div>
            <div class="rounded-xl border p-3">
                <dt class="text-xs text-stone-500">Savings rate</dt>
                <dd class="mt-1 font-mono font-bold">{{ number_format($cashFlow['savings_rate'] ?? 0,1) }}%</dd>
            </div>
            <div class="rounded-xl border p-3">
                <dt class="text-xs text-stone-500">Spending concentration</dt>
                <dd class="mt-1 font-medium capitalize">{{
                    data_get($insights,'spending_concentration.concentration_level','low') }}</dd>
            </div>
        </dl>
    </section>@endif
</article>