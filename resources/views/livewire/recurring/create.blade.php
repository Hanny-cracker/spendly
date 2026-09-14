<div class="mx-auto max-w-3xl space-y-6">
    <div><a href="{{ route('recurring') }}" class="text-sm text-stone-500">&larr; Recurring</a>
        <p class="mt-4 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Automation</p>
        <h1 class="mt-1 text-2xl font-semibold">New Recurring Transaction</h1>
        <p class="mt-1 text-sm text-stone-500">Create a dated and timed schedule without recording a transaction today.
        </p>
    </div>
    <form wire:submit.prevent="save" class="space-y-5 rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-6">
        @include('livewire.recurring.partials.schedule-form', ['editing' => false])
        <div class="flex justify-end gap-2 border-t pt-5"><a href="{{ route('recurring') }}"
                class="rounded-xl border px-4 py-2.5 text-sm font-semibold">Cancel</a><button
                class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60"
                wire:loading.attr="disabled" wire:target="save"
                @disabled(blank($title) || blank($amount) || blank($accountId) || blank($categoryId) || blank($frequency) || blank($startDate) || blank($scheduledTime))><span wire:loading.remove wire:target="save">Create Recurring</span><span
                    wire:loading wire:target="save">Creating...</span></button></div>
    </form>
</div>
