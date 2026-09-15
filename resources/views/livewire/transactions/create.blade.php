<div class="mx-auto max-w-3xl space-y-5">
    <div><p class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Transaction</p><div class="flex flex-wrap items-center justify-between gap-3"><h1 class="mt-1 text-2xl font-semibold text-stone-900">Add Transaction</h1><a href="{{ route('receipts.scan') }}" class="rounded-xl border border-stone-300 px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-100">Scan receipt</a></div><p class="mt-1 text-sm text-stone-500">Record income or an expense.</p></div>
    <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-6">@include('livewire.transactions.partials.form')</section>
</div>
