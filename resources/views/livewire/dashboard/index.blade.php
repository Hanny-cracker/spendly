<div
    class="relative space-y-8 transition-opacity duration-200"
    wire:loading.class="opacity-60"
    wire:target="startDate,endDate"
>
    <span
        class="pointer-events-none fixed bottom-4 right-4 z-40 rounded-full border border-stone-200 bg-[#fbf8f2] px-3 py-1.5 text-xs font-medium text-stone-600 shadow-sm"
        wire:loading
        wire:target="startDate,endDate"
    >
        Updating...
    </span>

    {{-- Header --}}
    <section
        class="
            flex flex-col gap-5
            md:flex-row
            md:items-end
            md:justify-between
        "
    >

        <div>
            <p
                class="
                    mb-1
                    font-mono text-xs font-semibold
                    uppercase tracking-[0.18em]
                    text-emerald-700
                "
            >
                Overview
            </p>

            <h1
                class="
                    text-2xl font-semibold
                    tracking-tight
                    text-stone-900
                    sm:text-3xl
                "
            >
                Dashboard
            </h1>

            <p class="mt-1 text-sm text-stone-500">
                Track your money and recent financial activity.
            </p>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="inline-flex items-center rounded-xl bg-red-700 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-600/30">
                    <span class="mr-1.5" aria-hidden="true">+</span> Expense
                </a>
                <a href="{{ route('transactions.create', ['type' => 'income']) }}" class="inline-flex items-center rounded-xl bg-emerald-700 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600/30">
                    <span class="mr-1.5" aria-hidden="true">+</span> Deposit
                </a>
            </div>
        </div>


        {{-- Period --}}
        <div
            class="
                flex w-full gap-2
                md:w-auto
            "
        >

            <label class="flex-1 md:flex-none">

                <span class="sr-only">
                    Start date
                </span>

                <input
                    type="date"
                    wire:model.live="startDate"
                    class="
                        w-full
                        rounded-xl
                        border border-stone-200
                        bg-[#fbf8f2]
                        px-3 py-2.5
                        text-sm
                        shadow-sm
                        outline-none
                        transition
                        focus:border-emerald-600
                        focus:ring-2
                        focus:ring-emerald-600/10
                    "
                >

            </label>


            <label class="flex-1 md:flex-none">

                <span class="sr-only">
                    End date
                </span>

                <input
                    type="date"
                    wire:model.live="endDate"
                    class="
                        w-full
                        rounded-xl
                        border border-stone-200
                        bg-[#fbf8f2]
                        px-3 py-2.5
                        text-sm
                        shadow-sm
                        outline-none
                        transition
                        focus:border-emerald-600
                        focus:ring-2
                        focus:ring-emerald-600/10
                    "
                >

            </label>

        </div>

    </section>


    {{-- Financial Overview --}}
    @include(
        'livewire.dashboard.partials.financial-overview'
    )

    @include('livewire.dashboard.partials.goal-savings')


    {{-- Accounts --}}
    @include('livewire.dashboard.partials.accounts')


    {{-- Recent transactions --}}
    <section>

        <div class="mb-4 flex items-center gap-3">

            <h2
                class="
                    font-mono text-xs font-semibold
                    uppercase tracking-[0.18em]
                    text-stone-500
                "
            >
                Recent transactions
            </h2>

            <div class="flex-1 border-t border-dashed border-stone-300"></div>

            @if (Route::has('transactions'))
                <a href="{{ route('transactions') }}" class="text-xs font-semibold text-emerald-700 transition hover:text-emerald-800">
                    View all
                </a>
            @endif

        </div>

        @php($recentTransactions = $dashboardData['recent_transactions'] ?? [])

        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-[#fbf8f2]">
            @forelse ($recentTransactions as $transaction)
                @php($isIncome = ($transaction['type'] ?? 'expense') === 'income')
                @php($isExpense = ($transaction['type'] ?? 'expense') === 'expense')
                @php($isTransfer = $transaction['is_transfer'] ?? false)
                @php($categoryName = $isTransfer ? 'Transfer' : ($transaction['category_name'] ?? 'Uncategorized'))
                @php($amountPrefix = $isIncome ? '+' : ($isExpense ? '−' : ''))

                <article class="grid grid-cols-[auto_1fr_auto] items-center gap-x-3 gap-y-1 border-b border-stone-200 px-4 py-3.5 last:border-b-0 sm:grid-cols-[auto_minmax(0,1fr)_auto_auto] sm:px-5">
                    <div class="row-span-2 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $isTransfer ? 'bg-stone-100 text-stone-700' : ($isIncome ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700') }}">
                        {{ strtoupper(mb_substr($categoryName, 0, 1)) }}
                    </div>

                    <div class="min-w-0 self-end">
                        <p class="truncate text-sm font-semibold text-stone-900">
                            {{ $transaction['title'] }}
                        </p>
                    </div>

                    <p class="self-end whitespace-nowrap text-right font-mono text-sm font-bold sm:col-start-4 sm:row-start-1 {{ $isTransfer ? 'text-stone-700' : ($isIncome ? 'text-emerald-700' : 'text-red-700') }}">
                        {{ $amountPrefix }}{{ number_format((float) ($transaction['amount'] ?? 0), 0) }}
                        <span class="hidden text-[10px] font-medium sm:inline">FCFA</span>
                    </p>

                    <time class="hidden whitespace-nowrap text-xs text-stone-500 sm:col-start-3 sm:row-start-1 sm:block" datetime="{{ $transaction['date'] }}">
                        {{ \Carbon\Carbon::parse($transaction['date'])->format('d M Y') }}
                    </time>

                    <p class="col-start-2 min-w-0 truncate self-start text-xs text-stone-500">
                        {{ $categoryName }}
                        <span aria-hidden="true">·</span>
                        {{ $transaction['account_name'] }}
                    </p>

                    <div class="col-span-2 col-start-2 flex items-center justify-between sm:hidden">
                        <time class="text-xs text-stone-500" datetime="{{ $transaction['date'] }}">
                            {{ \Carbon\Carbon::parse($transaction['date'])->format('d M Y') }}
                        </time>
                        <span class="font-mono text-[10px] font-medium text-stone-500">FCFA</span>
                    </div>
                </article>
            @empty
                <div class="px-6 py-8 text-center">
                    <p class="text-sm font-semibold text-stone-800">No transactions yet</p>
                    <p class="mt-1 text-xs text-stone-500">Your recent financial activity will appear here.</p>
                </div>
            @endforelse
        </div>

    </section>

</div>
