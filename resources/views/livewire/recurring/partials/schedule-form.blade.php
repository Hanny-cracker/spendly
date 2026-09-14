<fieldset>
    <legend class="text-sm font-medium">Type</legend>
    <div class="mt-2 grid grid-cols-2 gap-2">@foreach (['expense' => 'Expense', 'income' => 'Income'] as $value =>
        $label)<label
            class="cursor-pointer rounded-xl border p-3 text-center text-sm font-semibold has-[:checked]:border-emerald-700 has-[:checked]:bg-emerald-50"><input
                type="radio" wire:model.live="type" value="{{ $value }}" class="sr-only" @disabled($editing &&
                $hasHistory)>{{ $label }}</label>@endforeach</div>@if ($editing && $hasHistory)<p
        class="mt-1 text-xs text-stone-500">Type cannot change after transactions have been generated.</p>@endif
    @error('type')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
</fieldset>
<label class="block text-sm font-medium">Name<input wire:model.live="title" placeholder="Internet Subscription"
        class="mt-1 w-full rounded-xl border-stone-300">@error('title')<p class="text-xs text-red-600">{{ $message }}
    </p>@enderror</label>
<label class="block text-sm font-medium">Amount<div class="mt-1 flex"><input type="number" min="0.01" step="0.01"
            wire:model.live="amount" class="min-w-0 flex-1 rounded-l-xl border-stone-300"><span
            class="flex items-center rounded-r-xl border border-l-0 px-3 text-xs">FCFA</span></div>@error('amount')<p
        class="text-xs text-red-600">{{ $message }}</p>@enderror</label>
<div class="grid gap-5 sm:grid-cols-2"><label class="text-sm font-medium">Account<select wire:model.live="accountId"
            class="mt-1 w-full rounded-xl border-stone-300">
            <option value="">Select account</option>@foreach ($accounts as $account)<option value="{{ $account->id }}">
                {{ $account->name }}</option>@endforeach
        </select>@error('accountId')<p class="text-xs text-red-600">{{ $message }}</p>@enderror</label><label
        class="text-sm font-medium">Category<select wire:model.live="categoryId"
            class="mt-1 w-full rounded-xl border-stone-300">
            <option value="">Select category</option>@foreach ($categories as $category)<option
                value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
        </select>@error('categoryId')<p class="text-xs text-red-600">{{ $message }}</p>@enderror</label></div>
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4"><label class="text-sm font-medium">Frequency<select
            wire:model.live="frequency" class="mt-1 w-full rounded-xl border-stone-300">@foreach ($frequencies as $value =>
            $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>@error('frequency')<p
            class="text-xs text-red-600">{{ $message }}</p>@enderror</label><label class="text-sm font-medium">Start
        date<input type="date" wire:model.live="startDate" @readonly($editing && $hasHistory)
            class="mt-1 w-full rounded-xl border-stone-300 read-only:bg-stone-100">@error('startDate')<p
            class="text-xs text-red-600">{{ $message }}</p>@enderror</label><label
        class="text-sm font-medium">Time<input type="time" wire:model.live="scheduledTime"
            class="mt-1 w-full rounded-xl border-stone-300">@error('scheduledTime')<p class="text-xs text-red-600">{{
            $message }}</p>@enderror</label><label class="text-sm font-medium">End date<input type="date"
            wire:model.live="endDate" class="mt-1 w-full rounded-xl border-stone-300">@error('endDate')<p
            class="text-xs text-red-600">{{ $message }}</p>@enderror</label></div>
<label class="block text-sm font-medium">Description <span
        class="text-xs font-normal text-stone-500">(optional)</span><textarea wire:model="description" rows="3"
        class="mt-1 w-full rounded-xl border-stone-300"></textarea></label>
<aside class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Schedule</p>
    <p class="mt-2 text-sm text-stone-700">Repeats {{ $frequencies[$frequency] ?? $frequency }}.</p>
    <p class="text-sm text-stone-700">Starts {{ $startDate ? \Carbon\Carbon::parse($startDate.'
        '.$scheduledTime)->format('d M Y \a\t H:i') : 'when selected' }}.</p>
    <p class="text-sm text-stone-700">{{ $endDate ? 'Ends '.\Carbon\Carbon::parse($endDate)->format('d M Y').'.' : 'No
        end date.' }}</p>
</aside>
