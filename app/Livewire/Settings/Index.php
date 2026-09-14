<?php

namespace App\Livewire\Settings;

use App\Actions\Settings\UpdateFinancialSettings;
use App\Actions\Users\DeleteUserAccount;
use App\Enums\CategoryType;
use App\Livewire\Actions\Logout;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use App\Models\UserPreference;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Index extends Component
{
    public string $section = 'profile';

    public string $name = '';

    public string $email = '';

    public string $currency = 'XAF';

    public string $timezone = 'UTC';

    public string $dateFormat = 'd/m/Y';

    public string $weekStartsOn = 'monday';

    public string $defaultAccountId = '';

    public string $defaultExpenseCategoryId = '';

    public string $defaultIncomeCategoryId = '';

    public bool $notifyRecurring24h = true;

    public bool $notifyRecurring6h = true;

    public bool $notifyRecurringSuccess = true;

    public bool $notifyRecurringFailure = true;

    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $deletePassword = '';

    public string $deleteConfirmation = '';

    public function mount(): void
    {
        $user = $this->authenticatedUser();
        $preference = $user->preference()->firstOrNew([], [
            'currency' => 'XAF',
            'timezone' => config('app.timezone', 'UTC'),
            'date_format' => 'd/m/Y',
            'week_starts_on' => 'monday',
        ]);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->currency = $preference->currency;
        $this->timezone = $preference->timezone;
        $this->dateFormat = $preference->date_format;
        $this->weekStartsOn = $preference->week_starts_on;
        $this->defaultAccountId = (string) ($user->accounts()->where('is_default', true)->value('id') ?? '');
        $this->defaultExpenseCategoryId = (string) ($preference->default_expense_category_id ?? '');
        $this->defaultIncomeCategoryId = (string) ($preference->default_income_category_id ?? '');
        $this->notifyRecurring24h = $preference->notify_recurring_24h ?? true;
        $this->notifyRecurring6h = $preference->notify_recurring_6h ?? true;
        $this->notifyRecurringSuccess = $preference->notify_recurring_success ?? true;
        $this->notifyRecurringFailure = $preference->notify_recurring_failure ?? true;
    }

    public function saveProfile(): void
    {
        $user = $this->authenticatedUser();
        $this->name = trim($this->name);
        $this->email = mb_strtolower(trim($this->email));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        session()->flash('profile_status', 'Profile updated.');
    }

    public function savePreferences(): void
    {
        $user = $this->authenticatedUser();
        $validated = $this->validate([
            'currency' => ['required', Rule::in(['XAF'])],
            'timezone' => ['required', Rule::in(DateTimeZone::listIdentifiers())],
            'dateFormat' => ['required', Rule::in(['d/m/Y', 'm/d/Y', 'Y-m-d'])],
            'weekStartsOn' => ['required', Rule::in(['monday', 'sunday'])],
        ]);

        UserPreference::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['currency' => $validated['currency'], 'timezone' => $validated['timezone'], 'date_format' => $validated['dateFormat'], 'week_starts_on' => $validated['weekStartsOn']],
        );

        session()->flash('preferences_status', 'Preferences saved.');
    }

    public function detectTimezone(string $timezone): void
    {
        if (! in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            return;
        }

        if (! $this->authenticatedUser()->preference()->exists()) {
            $this->timezone = $timezone;
        }
    }

    public function saveFinancialSettings(UpdateFinancialSettings $action): void
    {
        $user = $this->authenticatedUser();
        $validated = $this->validate([
            'defaultAccountId' => ['nullable', Rule::exists('accounts', 'id')->where('user_id', $user->id)],
            'defaultExpenseCategoryId' => ['nullable', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $user->id)->where('type', CategoryType::Expense))],
            'defaultIncomeCategoryId' => ['nullable', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $user->id)->where('type', CategoryType::Income))],
        ]);

        $action->handle(
            $user,
            $validated['defaultAccountId'] === '' ? null : (int) $validated['defaultAccountId'],
            $validated['defaultExpenseCategoryId'] === '' ? null : (int) $validated['defaultExpenseCategoryId'],
            $validated['defaultIncomeCategoryId'] === '' ? null : (int) $validated['defaultIncomeCategoryId'],
        );

        session()->flash('financial_status', 'Financial settings saved.');
    }

    public function saveNotificationSettings(): void
    {
        $user = $this->authenticatedUser();
        $validated = $this->validate([
            'notifyRecurring24h' => ['boolean'],
            'notifyRecurring6h' => ['boolean'],
            'notifyRecurringSuccess' => ['boolean'],
            'notifyRecurringFailure' => ['boolean'],
        ]);

        UserPreference::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'notify_recurring_24h' => $validated['notifyRecurring24h'],
                'notify_recurring_6h' => $validated['notifyRecurring6h'],
                'notify_recurring_success' => $validated['notifyRecurringSuccess'],
                'notify_recurring_failure' => $validated['notifyRecurringFailure'],
            ],
        );

        session()->flash('notification_status', 'Notification settings saved.');
    }

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'currentPassword' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        $this->authenticatedUser()->update(['password' => $validated['password']]);
        $this->reset('currentPassword', 'password', 'password_confirmation');

        session()->flash('password_status', 'Password updated successfully.');
    }

    public function deleteAccount(): void
    {
        $this->validate([
            'deletePassword' => ['required', 'string', 'current_password'],
            'deleteConfirmation' => ['required', 'in:DELETE'],
        ], [
            'deleteConfirmation.in' => 'Enter DELETE to confirm account deletion.',
        ]);

        $user = $this->authenticatedUser();
        app(Logout::class)();
        app(DeleteUserAccount::class)->handle($user);

        $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.settings.index', [
            'timezones' => DateTimeZone::listIdentifiers(),
            'accounts' => Account::query()->where('user_id', auth()->id())->orderByDesc('is_default')->orderBy('name')->get(['id', 'name']),
            'expenseCategories' => Category::query()->where('user_id', auth()->id())->where('type', CategoryType::Expense)->orderBy('name')->get(['id', 'name']),
            'incomeCategories' => Category::query()->where('user_id', auth()->id())->where('type', CategoryType::Income)->orderBy('name')->get(['id', 'name']),
        ])->layout('layouts.app', ['title' => 'Settings | Spendly', 'header' => 'Settings']);
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
