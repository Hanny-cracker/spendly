<div class="mx-auto max-w-2xl space-y-6">
    <div><a href="{{ route('accounts.show', $account) }}" class="text-sm font-medium text-stone-500 hover:text-emerald-700">&larr; {{ $account->name }}</a><h1 class="mt-4 text-2xl font-semibold tracking-tight text-stone-900">Edit Account</h1><p class="mt-1 text-sm text-stone-500">Update account details without changing its financial history.</p></div>
    <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-6">@include('livewire.accounts.partials.form')</section>
</div>
