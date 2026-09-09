<div class="mx-auto max-w-3xl space-y-6">
    <div><a href="{{ route('recurring.show', $recurringTransaction) }}" class="text-sm text-stone-500">&larr; {{ $recurringTransaction->title }}</a><h1 class="mt-4 text-2xl font-semibold">Edit Recurring Transaction</h1><p class="mt-1 text-sm text-stone-500">Changes apply only to future generated transactions.</p></div>
    <form wire:submit="save" class="space-y-5 rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-6">
        @include('livewire.recurring.partials.schedule-form', ['editing' => true])
        <div class="flex justify-end gap-2 border-t pt-5"><a href="{{ route('recurring.show', $recurringTransaction) }}" class="rounded-xl border px-4 py-2.5 text-sm font-semibold">Cancel</a><button class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white">Save changes</button></div>
    </form>
</div>
