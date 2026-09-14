<div class="space-y-8 transition-opacity duration-200" wire:loading.class="opacity-60">
    <section class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="mb-1 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Your money</p>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">Accounts</h1>
            <p class="mt-1 text-sm text-stone-500">Manage your cash, bank, mobile money and other accounts.</p>
        </div>

        <a href="{{ route('accounts.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 md:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Add Account
        </a>
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <article class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5">
            <p class="text-xs font-medium uppercase tracking-wider text-stone-500">Total Balance</p>
            <p class="mt-4 font-mono text-2xl font-bold {{ $summary['total_balance'] < 0 ? 'text-red-700' : 'text-stone-900' }}">{{ number_format($summary['total_balance'], 0) }}</p>
            <p class="mt-1 text-xs text-stone-500">Across all accounts</p>
        </article>
        <article class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5">
            <p class="text-xs font-medium uppercase tracking-wider text-stone-500">Number of Accounts</p>
            <p class="mt-4 font-mono text-2xl font-bold text-stone-900">{{ $summary['account_count'] }}</p>
            <p class="mt-1 text-xs text-stone-500">{{ Str::plural('account', $summary['account_count']) }}</p>
        </article>
    </section>

    <section>
        <div class="mb-4 flex items-center gap-3">
            <h2 class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">My Accounts</h2>
            <div class="flex-1 border-t border-dashed border-stone-300"></div>
        </div>

        @if ($accounts->isNotEmpty())
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($accounts as $account)
                    <article class="relative overflow-hidden rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 shadow-sm">
                        <div class="absolute inset-x-0 top-0 h-1" style="background-color: {{ $account['color'] }}"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $account['color'] }}" aria-hidden="true"></span>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-semibold text-stone-900">{{ $account['name'] }}</h3>
                                    <p class="mt-1 text-xs text-stone-500">{{ $account['type_label'] }}</p>
                                </div>
                            </div>
                            @if ($account['is_default'])
                                <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Default</span>
                            @endif
                        </div>
                        <p class="mt-7 font-mono text-2xl font-bold tracking-tight {{ $account['current_balance'] < 0 ? 'text-red-700' : 'text-stone-900' }}">{{ number_format($account['current_balance'], 0) }}</p>
                        <p class="mt-1 text-xs font-medium text-stone-500">{{ $account['currency'] }}</p>
                        <div class="mt-6 flex flex-wrap items-center gap-4 border-t border-stone-200 pt-4">
                            <a href="{{ route('accounts.show', $account['public_id']) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-800">View account <span aria-hidden="true">&rarr;</span></a>
                            <a href="{{ route('transactions.create', ['type' => 'income', 'account' => $account['public_id']]) }}" class="text-sm font-semibold text-stone-600 hover:text-emerald-700">Add funds</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-stone-300 bg-[#fbf8f2] p-8 text-center sm:p-12">
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18v11H3zM3 9.5h18M16 14h2" /></svg>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-stone-900">No accounts yet</h3>
                <p class="mt-1 text-sm text-stone-500">Add your first account to start tracking your money.</p>
                <a href="{{ route('accounts.create') }}" class="mt-5 inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Add Account</a>
            </div>
        @endif
    </section>

    <span class="fixed bottom-4 right-4 rounded-full border border-stone-200 bg-[#fbf8f2] px-3 py-1.5 text-xs text-stone-600 shadow-sm" wire:loading>Updating...</span>
</div>
