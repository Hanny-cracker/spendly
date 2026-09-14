<?php

use App\Actions\Analysis\SavingsAnalysis;
use App\Data\Report\DateRangeData;
use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\RecurringTransactionNotificationType;
use App\Enums\TransactionType;
use App\Exceptions\RecurringOccurrenceNotDueException;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\RecurringTransactionNotification;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

function executableSchedule(User $user, Carbon $nextRun, array $attributes = []): RecurringTransaction
{
    $account = $attributes['account'] ?? Account::factory()->for($user)->create(['current_balance' => 100000]);
    $type = $attributes['type'] ?? TransactionType::Expense;
    $category = $attributes['category'] ?? Category::factory()->for($user)->create(['type' => CategoryType::from($type->value)]);

    return RecurringTransaction::factory()->for($user)->for($account)->for($category)->create([
        'title' => $attributes['title'] ?? 'Daily schedule',
        'amount' => $attributes['amount'] ?? 10000,
        'type' => $type,
        'frequency' => $attributes['frequency'] ?? RecurringFrequency::Daily,
        'start_date' => $nextRun->toDateString(),
        'scheduled_time' => $nextRun->format('H:i'),
        'next_run' => $nextRun,
        'status' => $attributes['status'] ?? RecurringStatus::Active,
    ]);
}

it('processes the reported next-day occurrence exactly once without schedule drift', function () {
    Notification::fake();
    Carbon::setTestNow('2026-09-08 08:01:00');
    $user = User::factory()->create();
    $schedule = executableSchedule($user, Carbon::parse('2026-09-08 08:00:00'));

    $this->artisan('transactions:generate-recurring')->assertSuccessful();
    expect($schedule->refresh()->next_run->toDateTimeString())->toBe('2026-09-09 08:00:00');

    Carbon::setTestNow('2026-09-09 08:03:00');
    $this->artisan('transactions:generate-recurring')->assertSuccessful();
    $this->artisan('transactions:generate-recurring')->assertSuccessful();

    expect($schedule->refresh()->next_run->toDateTimeString())->toBe('2026-09-10 08:00:00')
        ->and(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(2)
        ->and(Transaction::query()->where('recurring_transaction_id', $schedule->id)->pluck('scheduled_for')->map->toDateTimeString()->all())->toBe(['2026-09-08 08:00:00', '2026-09-09 08:00:00']);

    Notification::assertSentToTimes($user, RecurringTransactionNotification::class, 2);
    Notification::assertSentTo($user, RecurringTransactionNotification::class, fn ($notification) => $notification->type === RecurringTransactionNotificationType::Generated && $notification->scheduledFor?->toDateTimeString() === '2026-09-09 08:00:00');
    Carbon::setTestNow();
});

it('ignores schedules whose date is today but scheduled time is still in the future', function () {
    Carbon::setTestNow('2026-09-09 10:00:00');
    $user = User::factory()->create();
    $schedule = executableSchedule($user, Carbon::parse('2026-09-09 18:00:00'));

    $this->artisan('transactions:generate-recurring')->assertSuccessful();

    expect(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(0)
        ->and($schedule->refresh()->next_run->toDateTimeString())->toBe('2026-09-09 18:00:00');

    Carbon::setTestNow('2026-09-09 18:01:00');
    $this->artisan('transactions:generate-recurring')->assertSuccessful();

    expect(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(1)
        ->and($schedule->refresh()->next_run->toDateTimeString())->toBe('2026-09-10 18:00:00');

    Carbon::setTestNow();
});

it('rechecks a locked occurrence and prevents duplicate balance effects', function () {
    Notification::fake();
    Carbon::setTestNow('2026-09-09 08:03:00');
    $user = User::factory()->create();
    $schedule = executableSchedule($user, Carbon::parse('2026-09-09 08:00:00'));
    $startingBalance = $schedule->account->current_balance;
    $service = app(RecurringTransactionService::class);

    $service->generate($schedule);
    expect(fn () => $service->generate($schedule))->toThrow(RecurringOccurrenceNotDueException::class);

    expect(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(1)
        ->and($schedule->account->refresh()->current_balance)->toBe($startingBalance - $schedule->amount);
    Notification::assertSentToTimes($user, RecurringTransactionNotification::class, 1);
    Carbon::setTestNow();
});

it('sends one budget failure notification and leaves the occurrence due', function () {
    Notification::fake();
    Carbon::setTestNow('2026-09-09 08:03:00');
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $category = Category::factory()->for($user)->expense()->create();
    Budget::factory()->for($user)->for($category)->create(['amount' => 10000, 'period' => BudgetPeriod::Monthly, 'start_date' => '2026-09-01', 'end_date' => '2026-09-30', 'is_active' => true]);
    $schedule = executableSchedule($user, Carbon::parse('2026-09-09 08:00:00'), ['account' => $account, 'category' => $category, 'amount' => 25000]);
    $startingBalance = $account->current_balance;

    $this->artisan('transactions:generate-recurring')->assertSuccessful();
    $this->artisan('transactions:generate-recurring')->assertSuccessful();

    expect(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(0)
        ->and($account->refresh()->current_balance)->toBe($startingBalance)
        ->and($schedule->refresh()->next_run->toDateTimeString())->toBe('2026-09-09 08:00:00')
        ->and($schedule->last_generated_at)->toBeNull();
    Notification::assertSentToTimes($user, RecurringTransactionNotification::class, 1);
    Notification::assertSentTo($user, RecurringTransactionNotification::class, fn ($notification) => $notification->type === RecurringTransactionNotificationType::BudgetFailure);
    Carbon::setTestNow();
});

it('pauses an unfunded recurring expense and notifies the owner', function () {
    Notification::fake();
    Carbon::setTestNow('2026-09-09 08:03:00');
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 0]);
    $category = Category::factory()->for($user)->expense()->create();
    $schedule = executableSchedule($user, Carbon::parse('2026-09-09 08:00:00'), [
        'account' => $account,
        'category' => $category,
        'amount' => 25000,
    ]);

    $this->artisan('transactions:generate-recurring')->assertSuccessful();

    expect(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(0)
        ->and($account->refresh()->current_balance)->toBe(0.0)
        ->and($schedule->refresh()->status)->toBe(RecurringStatus::Paused);
    Notification::assertSentTo($user, RecurringTransactionNotification::class, fn ($notification) => $notification->type === RecurringTransactionNotificationType::AccountFundsFailure);
    Carbon::setTestNow();
});

it('records recurring savings income through the normal savings analysis', function () {
    Notification::fake();
    Carbon::setTestNow('2026-09-09 08:03:00');
    $user = User::factory()->create();
    $savings = Account::factory()->for($user)->savings()->create();
    $income = Category::factory()->for($user)->income()->create();
    $schedule = executableSchedule($user, Carbon::parse('2026-09-09 08:00:00'), ['account' => $savings, 'category' => $income, 'amount' => 100000, 'type' => TransactionType::Income]);

    $this->artisan('transactions:generate-recurring')->assertSuccessful();
    $analysis = app(SavingsAnalysis::class)->handle(new DateRangeData($user->id, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30')));

    expect($analysis->periodSavingsAccountDeposits)->toBe(100000.0)
        ->and($analysis->completedIncome)->toBe(100000.0)
        ->and($analysis->savingsRate)->toBe(100.0)
        ->and(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(1);
    Carbon::setTestNow();
});
