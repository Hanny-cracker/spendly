@php($goalSavings = $dashboardData['goal_savings'] ?? ['total' => 0, 'goals' => []])
<section>
    <div class="mb-4 flex items-center gap-3">
        <h2 class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Goal Savings</h2>
        <div class="flex-1 border-t border-dashed border-stone-300"></div>
        <a href="{{ route('goals') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">View goals</a>
    </div>

    <div class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-4 sm:p-5">
        <p class="font-mono text-xl font-bold text-stone-900">{{ number_format((float) ($goalSavings['total'] ?? 0), 0) }} <span class="text-xs font-medium text-stone-500">FCFA</span></p>
        <p class="mt-1 text-xs text-stone-500">Saved toward goals. This is an allocation of existing account money, not additional cash.</p>

        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse (array_slice($goalSavings['goals'] ?? [], 0, 6) as $goal)
                <div class="rounded-xl border border-stone-200 p-3">
                    <div class="flex items-center justify-between gap-3 text-sm"><span class="truncate font-semibold">{{ $goal['name'] }}</span><span class="font-mono text-xs text-stone-500">{{ number_format((float) $goal['percentage'], 1) }}%</span></div>
                    <p class="mt-1 font-mono text-xs text-stone-600">{{ number_format((float) $goal['current_amount'], 0) }} / {{ number_format((float) $goal['target_amount'], 0) }} FCFA</p>
                    <div class="mt-2 h-1.5 rounded-full bg-stone-200"><div class="h-full rounded-full bg-emerald-700" style="width: {{ min(100, max(0, (float) $goal['percentage'])) }}%"></div></div>
                </div>
            @empty
                <p class="text-sm text-stone-500">No goal savings yet.</p>
            @endforelse
        </div>
    </div>
</section>
