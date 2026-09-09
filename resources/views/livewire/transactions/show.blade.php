@php
    $isIncome = $transaction->type->isIncome();
    $isExpense = $transaction->type->isExpense();
    $isTransfer = $transaction->transfer_id !== null;
    $prefix = $isIncome ? '+' : ($isExpense ? '−' : '');
    $currency = $transaction->account?->currency ?? 'FCFA';
@endphp

<div class="mx-auto max-w-4xl space-y-6">
    <a href="{{ route('transactions') }}" class="inline-flex items-center gap-2 text-sm font-medium text-stone-600 hover:text-emerald-700">← Transactions</a>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Transaction</p><h1 class="mt-1 text-2xl font-semibold text-stone-900">Transaction Details</h1></div>
        @if (! $isTransfer)
            <div class="flex gap-2"><a href="{{ route('transactions.edit', $transaction) }}" class="rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">Edit</a><button type="button" wire:click="confirmDeletion" class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button></div>
        @endif
    </div>

    <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div><span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider {{ $isTransfer ? 'bg-stone-100 text-stone-700' : ($isIncome ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700') }}">{{ $isTransfer ? 'Transfer' : $transaction->type->value }}</span><p class="mt-4 text-lg font-semibold text-stone-900">{{ $transaction->title }}</p><p class="mt-1 text-sm capitalize text-stone-500">{{ $transaction->status->value }}</p></div>
            <p class="font-mono text-3xl font-bold {{ $isTransfer ? 'text-stone-800' : ($isIncome ? 'text-emerald-700' : 'text-red-700') }}">{{ $prefix }}{{ number_format($transaction->amount, 0) }} <span class="text-sm">{{ $currency }}</span></p>
        </div>
    </section>

    <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            @foreach (['Account' => $transaction->account?->name ?? 'Unknown account', 'Category' => $isTransfer ? 'Transfer' : ($transaction->category?->name ?? 'Uncategorized'), 'Date' => $transaction->date->format('d M Y'), 'Status' => ucfirst($transaction->status->value), 'Type' => ucfirst($transaction->type->value)] as $label => $value)
                <div class="border-b border-stone-200 pb-3"><dt class="text-xs font-medium uppercase tracking-wider text-stone-500">{{ $label }}</dt><dd class="mt-1 text-sm font-semibold text-stone-800">{{ $value }}</dd></div>
            @endforeach
        </dl>
        @if ($transaction->description)
            <div class="mt-5"><h2 class="text-xs font-medium uppercase tracking-wider text-stone-500">Description</h2><p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $transaction->description }}</p></div>
        @endif
    </section>

    @if ($confirmingDeletion)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/40 p-4" x-data @keydown.escape.window="$wire.set('confirmingDeletion', false)">
            <div class="w-full max-w-md rounded-2xl border border-stone-200 bg-[#fbf8f2] p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="delete-title" @click.outside="$wire.set('confirmingDeletion', false)">
                <h2 id="delete-title" class="text-lg font-semibold text-stone-900">Delete transaction?</h2>
                <p class="mt-2 text-sm text-stone-600">You are about to permanently delete <strong>{{ $transaction->title }}</strong> ({{ number_format($transaction->amount, 0) }} {{ $currency }}). The affected account balance will be updated.</p>
                <div class="mt-6 flex justify-end gap-2"><button type="button" wire:click="$set('confirmingDeletion', false)" class="rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700">Cancel</button><button type="button" wire:click="delete" wire:loading.attr="disabled" wire:target="delete" class="rounded-xl bg-red-700 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-60"><span wire:loading.remove wire:target="delete">Delete transaction</span><span wire:loading wire:target="delete">Deleting...</span></button></div>
            </div>
        </div>
    @endif
</div>
