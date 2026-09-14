@php($accounts = $dashboardData['accounts'] ?? [])

<section>
    <div class="mb-4 flex items-center gap-3">
        <h2
            class="
                font-mono text-xs font-semibold
                uppercase tracking-[0.18em]
                text-stone-500
            "
        >
            My accounts
        </h2>

        <div class="flex-1 border-t border-dashed border-stone-300"></div>
    </div>

    @if ($accounts === [])
        <div class="rounded-2xl border border-dashed border-stone-300 bg-[#fbf8f2] p-8 text-center text-sm text-stone-500">
            Add an account to start tracking your balances.
        </div>
    @else
        <div class="scrollbar-hidden flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2">
            @foreach ($accounts as $account)
                <article class="w-[82%] shrink-0 snap-start rounded-2xl border border-stone-200 p-5 shadow-sm sm:w-72 lg:w-80" style="--account-color: {{ $account['color'] ?? '#047857' }}; border-top: 4px solid var(--account-color); background-color: color-mix(in srgb, var(--account-color) 16%, white)">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-stone-900">
                                {{ $account['name'] }}
                            </p>
                            <p class="mt-1 text-xs capitalize text-stone-500">
                                {{ str_replace('_', ' ', $account['type']) }}
                            </p>
                        </div>

                        @if ($account['is_default'] ?? false)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-emerald-700">
                                Default
                            </span>
                        @endif
                    </div>

                    <p class="mt-6 font-mono text-2xl font-bold tracking-tight text-stone-900">
                        {{ number_format((float) ($account['current_balance'] ?? 0), 0) }}
                    </p>
                    <p class="mt-1 text-xs font-medium text-stone-500">
                        {{ $account['currency'] ?? 'FCFA' }}
                    </p>
                </article>
            @endforeach
        </div>
    @endif
</section>
