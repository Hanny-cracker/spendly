<div class="space-y-7 transition-opacity duration-200" wire:loading.class="opacity-60">
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-1 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Activity</p>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">Transactions</h1>
            <p class="mt-1 text-sm text-stone-500">Track and manage your financial activity.</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
            Add Transaction
        </a>
    </section>

    <section class="grid grid-cols-3 gap-3">
        @foreach ([['Income', $summary['income'], 'text-emerald-700'], ['Expenses', $summary['expenses'], 'text-red-700'], ['Net', $summary['net'], $summary['net'] >= 0 ? 'text-emerald-700' : 'text-red-700']] as [$label, $amount, $color])
            <article class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-3 sm:p-5">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-stone-500 sm:text-xs">{{ $label }}</p>
                <p class="mt-3 truncate font-mono text-base font-bold {{ $color }} sm:text-2xl">{{ number_format((float) $amount, 0) }}</p>
                <p class="mt-1 text-[10px] text-stone-500 sm:text-xs">FCFA</p>
            </article>
        @endforeach
    </section>

    <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-4 sm:p-5">
        <div class="grid gap-3 lg:grid-cols-12">
            <label class="lg:col-span-4"><span class="sr-only">Search transactions</span><input type="search" wire:model.live.debounce.350ms="search" placeholder="Search title or description..." class="w-full rounded-xl border-stone-200 bg-white/60 text-sm focus:border-emerald-600 focus:ring-emerald-600/10"></label>
            <label class="lg:col-span-2"><span class="sr-only">Transaction type</span><select wire:model.live="type" class="w-full rounded-xl border-stone-200 bg-white/60 text-sm"><option value="">All types</option><option value="income">Income</option><option value="expense">Expense</option></select></label>
            <label class="lg:col-span-3"><span class="sr-only">Account</span><select wire:model.live="account" class="w-full rounded-xl border-stone-200 bg-white/60 text-sm"><option value="">All accounts</option>@foreach ($accounts as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></label>
            <label class="lg:col-span-3"><span class="sr-only">Category</span><select wire:model.live="category" class="w-full rounded-xl border-stone-200 bg-white/60 text-sm"><option value="">All categories</option>@foreach ($categories as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></label>
            <label class="lg:col-span-3"><span class="mb-1 block text-xs text-stone-500">From</span><input type="date" wire:model.live="startDate" class="w-full rounded-xl border-stone-200 bg-white/60 text-sm"></label>
            <label class="lg:col-span-3"><span class="mb-1 block text-xs text-stone-500">To</span><input type="date" wire:model.live="endDate" class="w-full rounded-xl border-stone-200 bg-white/60 text-sm"></label>
            <div class="flex items-end lg:col-span-3">@if ($hasActiveFilters)<button type="button" wire:click="clearFilters" class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">Clear filters</button>@endif</div>
        </div>
    </section>

    <section>
        <div class="mb-4 flex items-center gap-3"><h2 class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">All Transactions</h2><div class="flex-1 border-t border-dashed border-stone-300"></div><span class="text-xs text-stone-500" wire:loading>Updating...</span></div>
        <div class="rounded-2xl border border-stone-200 bg-[#fbf8f2]">
            @forelse ($transactions as $transaction)
                @php
                    $isIncome = $transaction->type->isIncome();
                    $isExpense = $transaction->type->isExpense();
                    $isTransfer = $transaction->transfer_id !== null;
                    $categoryName = $isTransfer ? 'Transfer' : ($transaction->category?->name ?? 'Uncategorized');
                    $prefix = $isIncome ? '+' : ($isExpense ? '−' : '');
                @endphp
                <article class="grid grid-cols-[auto_1fr_auto] items-center gap-x-3 gap-y-1 border-b border-stone-200 px-4 py-3.5 last:border-b-0 sm:grid-cols-[auto_minmax(0,1fr)_auto_auto_auto_auto] sm:px-5">
                    <div class="row-span-2 flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold {{ $isTransfer ? 'bg-stone-100 text-stone-700' : ($isIncome ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700') }}">{{ strtoupper(mb_substr($categoryName, 0, 1)) }}</div>
                    <p class="min-w-0 truncate text-sm font-semibold text-stone-900">{{ $transaction->title }}</p>
                    <p class="whitespace-nowrap text-right font-mono text-sm font-bold sm:col-start-5 {{ $isTransfer ? 'text-stone-700' : ($isIncome ? 'text-emerald-700' : 'text-red-700') }}">{{ $prefix }}{{ number_format((float) $transaction->amount, 0) }} <span class="hidden text-[10px] sm:inline">FCFA</span></p>
                    <div class="relative col-start-3 row-start-2 justify-self-end sm:col-start-6 sm:row-start-1" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = ! open" class="flex h-8 w-8 items-center justify-center rounded-lg text-stone-500 transition hover:bg-stone-100 hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-emerald-600" aria-label="Actions for {{ $transaction->title }}" :aria-expanded="open">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition.origin.top.right class="absolute right-0 top-full z-50 mt-1 w-36 rounded-xl border border-stone-200 bg-[#fbf8f2] p-1.5 shadow-lg">
                            <a href="{{ route('transactions.show', $transaction) }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm text-stone-700 hover:bg-stone-100">View</a>
                            @if (! $isTransfer)<a href="{{ route('transactions.edit', $transaction) }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm text-stone-700 hover:bg-stone-100">Edit</a>@endif
                        </div>
                    </div>
                    <time class="hidden whitespace-nowrap text-xs text-stone-500 sm:col-start-3 sm:row-start-1 sm:block">{{ $transaction->date->format('d M Y') }}</time>
                    <span class="hidden rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-semibold capitalize text-stone-600 sm:col-start-4 sm:row-start-1 sm:block">{{ $transaction->status->value }}</span>
                    <p class="col-start-2 min-w-0 truncate text-xs text-stone-500">{{ $categoryName }} · {{ $transaction->account?->name ?? 'Unknown account' }}</p>
                    <div class="col-span-2 col-start-2 flex justify-between text-xs text-stone-500 sm:hidden"><span>{{ $transaction->date->format('d M Y') }} · {{ ucfirst($transaction->status->value) }}</span><span class="font-mono text-[10px]">FCFA</span></div>
                </article>
            @empty
                <div class="px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-stone-800">{{ $hasTransactions ? 'No matching transactions' : 'No transactions yet' }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ $hasTransactions ? 'Try changing or clearing your filters.' : 'Your income and expenses will appear here.' }}</p>
                    @if ($hasTransactions)
                        <button type="button" wire:click="clearFilters" class="mt-4 rounded-xl border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-100">Clear filters</button>
                    @else
                        <a href="{{ route('transactions.create') }}" class="mt-4 inline-flex rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Add your first transaction</a>
                    @endif
                </div>
            @endforelse
        </div>
        @if ($transactions->hasPages())<div class="mt-5">{{ $transactions->links() }}</div>@endif
    </section>
</div>
