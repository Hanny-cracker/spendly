<div class="space-y-7 transition-opacity" wire:loading.class="opacity-60">
    <section class="report-controls flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Statements</p>
            <h1 class="mt-1 text-3xl font-semibold">Reports</h1>
            <p class="mt-1 text-sm text-stone-500">Review your financial activity for any period.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center"><label class="block text-sm font-medium"><span
                    class="sr-only">Report period</span><select wire:model.live="period"
                    class="w-full rounded-xl border-stone-300 bg-[#fbf8f2] sm:w-52">
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="last_three_months">Last 3 Months</option>
                    <option value="this_year">This Year</option>
                    <option value="custom">Custom</option>
                </select></label>
            <div class="grid grid-cols-1 gap-2 min-[360px]:grid-cols-3"><a target="_blank" rel="noopener" href="{{ route('reports.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                    class="rounded-xl border border-stone-300 bg-[#fbf8f2] px-3 py-2.5 text-center text-sm font-semibold transition hover:bg-stone-100">Print</a><a
                    href="{{ route('reports.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                    class="rounded-xl border border-stone-300 bg-[#fbf8f2] px-3 py-2.5 text-center text-sm font-semibold transition hover:bg-stone-100">Export CSV</a><a
                    href="{{ route('reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                    class="rounded-xl bg-emerald-700 px-3 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-emerald-800">Export PDF</a>
            </div>
        </div>
    </section>
    @if($period === 'custom')<form wire:submit="applyCustom"
        class="report-controls flex flex-col gap-3 rounded-2xl border border-stone-200 bg-[#fbf8f2] p-4 sm:flex-row sm:items-end">
        <label class="flex-1 text-sm font-medium">From<input type="date" wire:model="startDate"
                class="mt-1 w-full rounded-xl border-stone-300">@error('startDate')<p class="text-xs text-red-600">{{
                $message }}</p>@enderror</label><label class="flex-1 text-sm font-medium">To<input type="date"
                wire:model="endDate" class="mt-1 w-full rounded-xl border-stone-300">@error('endDate')<p
                class="text-xs text-red-600">{{ $message }}</p>@enderror</label><button
            class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white">Apply</button></form>@endif
    <div wire:loading class="report-controls text-xs font-medium text-stone-500">Updating report...</div>
    @include('livewire.reports.partials.report-content', ['report' => $reportData])
</div>
