<?php

use App\Actions\Analysis\SavingsAnalysis;
use App\Data\Report\DateRangeData;
use App\Enums\BudgetPeriod;
use App\Enums\RecurringStatus;
use App\Enums\RecurringTransactionNotificationType;
use App\Enums\TransactionType;
use App\Livewire\Settings\Index;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\RecurringTransactionNotification;
use App\Services\RecurringTransactions\RecurringTransactionNotificationService;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

function recurringForNotification(User $user, array $attributes = []): RecurringTransaction
{
    $account = $attributes['account'] ?? Account::factory()->for($user)->create(['current_balance' => 100000]);
    $type = $attributes['type'] ?? TransactionType::Expense;
    $category = $attributes['category'] ?? Category::factory()->for($user)->create(['type' => $type->value]);

    return RecurringTransaction::factory()->for($user)->for($account)->for($category)->create([
        'type' => $type,
        'amount' => $attributes['amount'] ?? 10000,
        'next_run' => $attributes['next_run'] ?? now(),
        'status' => RecurringStatus::Active,
        'last_24h_notified_at' => null,
        'last_6h_notified_at' => null,
    ]);
}

it('defaults every recurring notification preference to enabled', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Index::class)
        ->assertSet('notifyRecurring24h', true)
        ->assertSet('notifyRecurring6h', true)
        ->assertSet('notifyRecurringSuccess', true)
        ->assertSet('notifyRecurringFailure', true);
});

it('persists notification settings for only the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    UserPreference::factory()->for($other)->create();

    Livewire::actingAs($user)->test(Index::class)
        ->set('section', 'notifications')
        ->set('notifyRecurring24h', false)
        ->set('notifyRecurringSuccess', false)
        ->call('saveNotificationSettings')
        ->assertHasNoErrors()
        ->assertSee('Notification settings saved.');

    Livewire::actingAs($user)->test(Index::class)
        ->assertSet('notifyRecurring24h', false)
        ->assertSet('notifyRecurring6h', true)
        ->assertSet('notifyRecurringSuccess', false)
        ->assertSet('notifyRecurringFailure', true);

    expect($other->preference()->firstOrFail()->notify_recurring_24h)->toBeTrue()
        ->and($other->preference()->firstOrFail()->notify_recurring_success)->toBeTrue();
});

it('delivers enabled reminders exactly once', function (int $hours, string $trackingColumn, RecurringTransactionNotificationType $type) {
    Notification::fake();
    $user = User::factory()->create();
    $schedule = recurringForNotification($user, ['next_run' => now()->addHours($hours)]);

    $this->artisan('transactions:notify-upcoming')->assertSuccessful();
    $this->artisan('transactions:notify-upcoming')->assertSuccessful();

    Notification::assertSentToTimes($user, RecurringTransactionNotification::class, 1);
    Notification::assertSentTo($user, RecurringTransactionNotification::class, fn ($notification) => $notification->type === $type);
    expect($schedule->refresh()->{$trackingColumn})->not->toBeNull();
})->with([[20, 'last_24h_notified_at', RecurringTransactionNotificationType::Upcoming24Hours], [6, 'last_6h_notified_at', RecurringTransactionNotificationType::Upcoming6Hours]]);

it('skips disabled reminders while marking their occurrence as handled', function (int $hours, string $preferenceColumn, string $trackingColumn) {
    Notification::fake();
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create([$preferenceColumn => false]);
    $schedule = recurringForNotification($user, ['next_run' => now()->addHours($hours)]);

    $this->artisan('transactions:notify-upcoming')->assertSuccessful();

    Notification::assertNothingSent();
    expect($schedule->refresh()->{$trackingColumn})->not->toBeNull();
})->with([[20, 'notify_recurring_24h', 'last_24h_notified_at'], [6, 'notify_recurring_6h', 'last_6h_notified_at']]);

it('suppresses success delivery without suppressing recurring financial execution', function () {
    Notification::fake();
    Carbon::setTestNow('2026-09-09 08:03:00');
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create(['notify_recurring_success' => false]);
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $schedule = recurringForNotification($user, ['account' => $account, 'next_run' => Carbon::parse('2026-09-09 08:00:00'), 'amount' => 25000]);

    app(RecurringTransactionService::class)->generate($schedule);

    Notification::assertNothingSent();
    expect(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(1)
        ->and($account->refresh()->current_balance)->toBe(75000.0)
        ->and($schedule->refresh()->next_run->toDateTimeString())->toBe('2026-10-09 08:00:00');
    Carbon::setTestNow();
});

it('suppresses failure delivery without changing safe budget failure behavior', function () {
    Notification::fake();
    Carbon::setTestNow('2026-09-09 08:03:00');
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create(['notify_recurring_failure' => false]);
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $category = Category::factory()->for($user)->expense()->create();
    Budget::factory()->for($user)->for($category)->create(['amount' => 10000, 'period' => BudgetPeriod::Monthly, 'start_date' => '2026-09-01', 'end_date' => '2026-09-30', 'is_active' => true]);
    $schedule = recurringForNotification($user, ['account' => $account, 'category' => $category, 'next_run' => Carbon::parse('2026-09-09 08:00:00'), 'amount' => 25000]);

    $this->artisan('transactions:generate-recurring')->assertSuccessful();

    Notification::assertNothingSent();
    expect(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(0)
        ->and($account->refresh()->current_balance)->toBe(100000.0)
        ->and($schedule->refresh()->next_run->toDateTimeString())->toBe('2026-09-09 08:00:00')
        ->and($schedule->last_generated_at)->toBeNull();
    Carbon::setTestNow();
});

it('keeps recurring savings income fully financial when success delivery is disabled', function () {
    Notification::fake();
    Carbon::setTestNow('2026-09-09 08:03:00');
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create(['notify_recurring_success' => false]);
    $savings = Account::factory()->for($user)->savings()->create(['current_balance' => 0]);
    $income = Category::factory()->for($user)->income()->create();
    $schedule = recurringForNotification($user, ['account' => $savings, 'category' => $income, 'type' => TransactionType::Income, 'next_run' => Carbon::parse('2026-09-09 08:00:00'), 'amount' => 100000]);

    app(RecurringTransactionService::class)->generate($schedule);
    $analysis = app(SavingsAnalysis::class)->handle(new DateRangeData($user->id, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30')));

    Notification::assertNothingSent();
    expect($savings->refresh()->current_balance)->toBe(100000.0)
        ->and($analysis->periodSavingsAccountDeposits)->toBe(100000.0)
        ->and($analysis->completedIncome)->toBe(100000.0)
        ->and($analysis->savingsRate)->toBe(100.0);
    Carbon::setTestNow();
});

it('keeps historical database notifications after preferences are disabled', function () {
    $user = User::factory()->create();
    $schedule = recurringForNotification($user);
    app(RecurringTransactionNotificationService::class)->generated($schedule, now());
    expect($user->notifications()->count())->toBe(1);

    Livewire::actingAs($user)->test(Index::class)->set('notifyRecurringSuccess', false)->call('saveNotificationSettings')->assertHasNoErrors();

    expect($user->notifications()->count())->toBe(1);
});
