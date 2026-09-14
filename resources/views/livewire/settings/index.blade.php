<div class="mx-auto max-w-5xl space-y-6" x-init="$wire.detectTimezone(Intl.DateTimeFormat().resolvedOptions().timeZone)">
    <header><p class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Your account</p><h1 class="mt-1 text-3xl font-semibold tracking-tight">Settings</h1><p class="mt-1 text-sm text-stone-500">Manage your profile and application preferences.</p></header>
    <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
        <nav class="flex gap-2 overflow-x-auto rounded-2xl border border-stone-200 bg-[#fbf8f2] p-2 lg:block lg:space-y-1" aria-label="Settings sections">
            @foreach (['profile' => 'Profile', 'preferences' => 'Preferences', 'financial' => 'Financial', 'notifications' => 'Notifications', 'security' => 'Security'] as $key => $label)
                <button type="button" wire:click="$set('section', '{{ $key }}')" class="whitespace-nowrap rounded-xl px-4 py-2.5 text-left text-sm font-semibold transition lg:w-full {{ $section === $key ? 'bg-emerald-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">{{ $label }}</button>
            @endforeach
        </nav>
        <div>
            @if ($section === 'profile')
                <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-7">
                    <h2 class="text-lg font-semibold">Profile</h2><p class="mt-1 text-sm text-stone-500">Update your account identity and email address.</p>
                    @if (session('profile_status'))<p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('profile_status') }}</p>@endif
                    <form wire:submit="saveProfile" class="mt-6 space-y-5">
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Name</span><input type="text" wire:model="name" autocomplete="name" required class="w-full rounded-xl border-stone-300 bg-white/60 focus:border-emerald-600 focus:ring-emerald-600">@error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Email</span><input type="email" wire:model="email" autocomplete="username" required class="w-full rounded-xl border-stone-300 bg-white/60 focus:border-emerald-600 focus:ring-emerald-600">@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                        <div class="flex justify-end border-t border-stone-200 pt-5"><button type="submit" wire:loading.attr="disabled" wire:target="saveProfile" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:opacity-60"><span wire:loading.remove wire:target="saveProfile">Save Profile</span><span wire:loading wire:target="saveProfile">Saving...</span></button></div>
                    </form>
                </section>
            @elseif ($section === 'preferences')
                <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-7">
                    <h2 class="text-lg font-semibold">Preferences</h2><p class="mt-1 text-sm text-stone-500">Choose how Spendly presents dates and schedules. Financial values are never converted.</p>
                    @if (session('preferences_status'))<p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('preferences_status') }}</p>@endif
                    <form wire:submit="savePreferences" class="mt-6 grid gap-5 sm:grid-cols-2">
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Currency</span><select wire:model="currency" class="w-full rounded-xl border-stone-300 bg-white/60"><option value="XAF">XAF — Central African CFA franc</option></select><p class="mt-1 text-xs text-stone-500">Spendly does not perform currency conversion.</p></label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Timezone</span><select wire:model="timezone" class="w-full rounded-xl border-stone-300 bg-white/60">@foreach ($timezones as $timezoneOption)<option value="{{ $timezoneOption }}">{{ str_replace('_', ' ', $timezoneOption) }}</option>@endforeach</select>@error('timezone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Date format</span><select wire:model="dateFormat" class="w-full rounded-xl border-stone-300 bg-white/60"><option value="d/m/Y">21/09/2026 — DD/MM/YYYY</option><option value="m/d/Y">09/21/2026 — MM/DD/YYYY</option><option value="Y-m-d">2026-09-21 — YYYY-MM-DD</option></select>@error('dateFormat')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Week starts on</span><select wire:model="weekStartsOn" class="w-full rounded-xl border-stone-300 bg-white/60"><option value="monday">Monday</option><option value="sunday">Sunday</option></select>@error('weekStartsOn')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                        <div class="flex justify-end border-t border-stone-200 pt-5 sm:col-span-2"><button type="submit" wire:loading.attr="disabled" wire:target="savePreferences" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-60"><span wire:loading.remove wire:target="savePreferences">Save Preferences</span><span wire:loading wire:target="savePreferences">Saving...</span></button></div>
                    </form>
                </section>
            @elseif ($section === 'financial')
                <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-7">
                    <h2 class="text-lg font-semibold">Financial Settings</h2><p class="mt-1 text-sm text-stone-500">Choose defaults used when recording new financial activity.</p>
                    @if (session('financial_status'))<p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('financial_status') }}</p>@endif
                    @if ($accounts->isEmpty())
                        <div class="mt-6 rounded-xl border border-dashed border-stone-300 p-5"><p class="font-medium">No accounts available.</p><p class="mt-1 text-sm text-stone-500">Create an account before choosing a default.</p><a href="{{ route('accounts.create') }}" class="mt-3 inline-flex text-sm font-semibold text-emerald-700">Create account</a></div>
                    @endif
                    <form wire:submit="saveFinancialSettings" class="mt-6 space-y-5">
                        @if ($accounts->isNotEmpty())<label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Default Transaction Account</span><select wire:model="defaultAccountId" class="w-full rounded-xl border-stone-300 bg-white/60">@foreach ($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select>@error('defaultAccountId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>@endif
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Default Expense Category</span><select wire:model="defaultExpenseCategoryId" class="w-full rounded-xl border-stone-300 bg-white/60"><option value="">No default</option>@foreach ($expenseCategories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>@error('defaultExpenseCategoryId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Default Income Category</span><select wire:model="defaultIncomeCategoryId" class="w-full rounded-xl border-stone-300 bg-white/60"><option value="">No default</option>@foreach ($incomeCategories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>@error('defaultIncomeCategoryId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                        <div class="flex justify-end border-t border-stone-200 pt-5"><button type="submit" wire:loading.attr="disabled" wire:target="saveFinancialSettings" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-60"><span wire:loading.remove wire:target="saveFinancialSettings">Save Financial Settings</span><span wire:loading wire:target="saveFinancialSettings">Saving...</span></button></div>
                    </form>
                </section>
            @elseif ($section === 'notifications')
                <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-7">
                    <h2 class="text-lg font-semibold">Notification Settings</h2><p class="mt-1 text-sm text-stone-500">Control recurring transaction notifications without changing schedule execution.</p>
                    @if (session('notification_status'))<p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('notification_status') }}</p>@endif
                    <form wire:submit="saveNotificationSettings" class="mt-6 space-y-7">
                        <fieldset><legend class="text-sm font-semibold text-stone-900">Recurring transaction reminders</legend><div class="mt-3 divide-y divide-stone-200 rounded-xl border border-stone-200">
                            <label class="flex cursor-pointer items-start justify-between gap-4 p-4"><span><span class="block text-sm font-medium">24 hours before</span><span class="mt-1 block text-xs text-stone-500">Get notified one day before a recurring transaction is due.</span></span><input type="checkbox" wire:model="notifyRecurring24h" class="mt-1 h-5 w-5 rounded border-stone-300 text-emerald-700 focus:ring-emerald-600"></label>
                            <label class="flex cursor-pointer items-start justify-between gap-4 p-4"><span><span class="block text-sm font-medium">6 hours before</span><span class="mt-1 block text-xs text-stone-500">Get notified six hours before a recurring transaction is due.</span></span><input type="checkbox" wire:model="notifyRecurring6h" class="mt-1 h-5 w-5 rounded border-stone-300 text-emerald-700 focus:ring-emerald-600"></label>
                        </div></fieldset>
                        <fieldset><legend class="text-sm font-semibold text-stone-900">Recurring transaction activity</legend><div class="mt-3 divide-y divide-stone-200 rounded-xl border border-stone-200">
                            <label class="flex cursor-pointer items-start justify-between gap-4 p-4"><span><span class="block text-sm font-medium">Successful execution</span><span class="mt-1 block text-xs text-stone-500">Get notified when a recurring transaction is recorded successfully.</span></span><input type="checkbox" wire:model="notifyRecurringSuccess" class="mt-1 h-5 w-5 rounded border-stone-300 text-emerald-700 focus:ring-emerald-600"></label>
                            <label class="flex cursor-pointer items-start justify-between gap-4 p-4"><span><span class="block text-sm font-medium">Failed execution</span><span class="mt-1 block text-xs text-stone-500">Get notified when a recurring transaction could not be recorded.</span></span><input type="checkbox" wire:model="notifyRecurringFailure" class="mt-1 h-5 w-5 rounded border-stone-300 text-emerald-700 focus:ring-emerald-600"></label>
                        </div></fieldset>
                        <div class="flex justify-end border-t border-stone-200 pt-5"><button type="submit" wire:loading.attr="disabled" wire:target="saveNotificationSettings" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-60"><span wire:loading.remove wire:target="saveNotificationSettings">Save Notification Settings</span><span wire:loading wire:target="saveNotificationSettings">Saving...</span></button></div>
                    </form>
                </section>
            @else
                <div class="space-y-6">
                    <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-7">
                        <h2 class="text-lg font-semibold">Security &amp; Account</h2>
                        <p class="mt-1 text-sm text-stone-500">Keep your account password secure.</p>
                        @if (session('password_status'))<p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('password_status') }}</p>@endif
                        <form wire:submit="updatePassword" class="mt-6 space-y-5">
                            <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Current Password</span><input type="password" wire:model="currentPassword" autocomplete="current-password" required class="w-full rounded-xl border-stone-300 bg-white/60 focus:border-emerald-600 focus:ring-emerald-600">@error('currentPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                            <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">New Password</span><input type="password" wire:model="password" autocomplete="new-password" required class="w-full rounded-xl border-stone-300 bg-white/60 focus:border-emerald-600 focus:ring-emerald-600">@error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                            <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Confirm New Password</span><input type="password" wire:model="password_confirmation" autocomplete="new-password" required class="w-full rounded-xl border-stone-300 bg-white/60 focus:border-emerald-600 focus:ring-emerald-600"></label>
                            <div class="flex justify-end border-t border-stone-200 pt-5"><button type="submit" wire:loading.attr="disabled" wire:target="updatePassword" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:opacity-60"><span wire:loading.remove wire:target="updatePassword">Update Password</span><span wire:loading wire:target="updatePassword">Updating...</span></button></div>
                        </form>
                    </section>

                    <section class="rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 sm:p-7">
                        <h2 class="text-lg font-semibold">Account information</h2>
                        <dl class="mt-4 divide-y divide-stone-200 rounded-xl border border-stone-200 bg-white/40 px-4">
                            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:justify-between"><dt class="text-sm text-stone-500">Current email</dt><dd class="text-sm font-medium text-stone-800">{{ auth()->user()->email }}</dd></div>
                            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:justify-between"><dt class="text-sm text-stone-500">Account created</dt><dd class="text-sm font-medium text-stone-800">{{ auth()->user()->created_at?->format('d M Y') }}</dd></div>
                            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:justify-between"><dt class="text-sm text-stone-500">Email verification</dt><dd class="text-sm font-medium text-stone-800">{{ auth()->user()->email_verified_at ? 'Verified' : 'Not verified' }}</dd></div>
                        </dl>
                    </section>

                    <section x-data="{ confirmingDeletion: false }" class="rounded-2xl border border-red-200 bg-red-50/40 p-5 sm:p-7">
                        <h2 class="text-lg font-semibold text-red-900">Danger Zone</h2>
                        <p class="mt-1 text-sm text-red-800/70">Permanently delete your Spendly account and all associated financial data.</p>
                        <button type="button" @click="confirmingDeletion = true" class="mt-5 rounded-xl border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Delete Account</button>

                        <div x-cloak x-show="confirmingDeletion" @keydown.escape.window="confirmingDeletion = false" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-account-title">
                            <div @click.outside="confirmingDeletion = false" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 shadow-xl sm:p-7">
                                <h3 id="delete-account-title" class="text-lg font-semibold text-stone-900">Delete your Spendly account?</h3>
                                <p class="mt-2 text-sm text-stone-600">This permanently removes your accounts, transactions, transfers, budgets, categories, goals and contributions, recurring schedules, preferences, and Spendly notifications. This cannot be undone.</p>
                                <form wire:submit="deleteAccount" class="mt-6 space-y-4">
                                    <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Current Password</span><input type="password" wire:model="deletePassword" autocomplete="current-password" required class="w-full rounded-xl border-stone-300 bg-white focus:border-red-500 focus:ring-red-500">@error('deletePassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                                    <label class="block"><span class="mb-1.5 block text-sm font-medium text-stone-700">Enter DELETE to confirm</span><input type="text" wire:model="deleteConfirmation" autocomplete="off" required class="w-full rounded-xl border-stone-300 bg-white focus:border-red-500 focus:ring-red-500">@error('deleteConfirmation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</label>
                                    <div class="flex flex-col-reverse gap-3 border-t border-stone-200 pt-5 sm:flex-row sm:justify-end"><button type="button" @click="confirmingDeletion = false" class="rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700">Cancel</button><button type="submit" wire:loading.attr="disabled" wire:target="deleteAccount" class="rounded-xl bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800 disabled:opacity-60"><span wire:loading.remove wire:target="deleteAccount">Delete Account</span><span wire:loading wire:target="deleteAccount">Deleting...</span></button></div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </div>
</div>
