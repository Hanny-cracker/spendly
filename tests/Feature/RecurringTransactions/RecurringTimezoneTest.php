<?php

use App\Actions\RecurringTransactions\CreateRecurringTransaction;
use App\Data\RecurringTransaction\CreateRecurringTransactionData;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\RecurringTransactionNotificationType;
use App\Enums\TransactionType;
use App\Livewire\Recurring\Edit;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\RecurringTransactionNotification;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTimezoneSchedule(User $user, string $date, string $time): RecurringTransaction
{
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $category = Category::factory()->for($user)->expense()->create();
    $startDate = Carbon::parse($date);

    return app(CreateRecurringTransaction::class)->handle(new CreateRecurringTransactionData(
        userId: $user->id,
        accountId: $account->id,
        categoryId: $category->id,
        title: 'Timezone schedule',
        description: null,
        amount: 1000,
        type: TransactionType::Expense,
        frequency: RecurringFrequency::Daily,
        interval: 1,
        startDate: $startDate,
        nextRun: $startDate,
        endDate: null,
        status: RecurringStatus::Active,
        scheduledTime: $time,
    ));
}

it('stores a Douala local schedule in UTC and displays it in local time', function () {
    Carbon::setTestNow('2026-09-09 10:00:00 UTC');
    $user = User::factory()->create();
    UserPreference::query()->updateOrCreate(['user_id' => $user->id], ['timezone' => 'Africa/Douala']);

    $schedule = createTimezoneSchedule($user, '2026-09-09', '15:30');

    expect($schedule->timezone)->toBe('Africa/Douala')
        ->and($schedule->next_run->copy()->utc()->toDateTimeString())->toBe('2026-09-09 14:30:00');

    $this->actingAs($user)
        ->get(route('recurring.show', $schedule))
        ->assertOk()
        ->assertSee('09 Sep 2026 at 15:30');
});

it('preserves New York local wall clock time across daylight saving changes', function () {
    Carbon::setTestNow('2026-03-07 14:00:00 UTC');
    $user = User::factory()->create();
    UserPreference::query()->updateOrCreate(['user_id' => $user->id], ['timezone' => 'America/New_York']);

    $schedule = createTimezoneSchedule($user, '2026-03-07', '09:00')->refresh();
    Carbon::setTestNow('2026-03-07 15:00:00 UTC');
    app(RecurringTransactionService::class)->generate($schedule);
    $schedule->refresh();

    expect($schedule->next_run->copy()->utc()->toDateTimeString())->toBe('2026-03-08 13:00:00')
        ->and($schedule->next_run->copy()->setTimezone('America/New_York')->format('H:i'))->toBe('09:00');
});

it('preserves New York local wall clock time when daylight saving ends', function () {
    Carbon::setTestNow('2026-11-01 12:00:00 UTC');
    $user = User::factory()->create();
    UserPreference::query()->updateOrCreate(['user_id' => $user->id], ['timezone' => 'America/New_York']);
    $schedule = createTimezoneSchedule($user, '2026-11-01', '09:00')->refresh();

    Carbon::setTestNow('2026-11-01 14:00:00 UTC');
    app(RecurringTransactionService::class)->generate($schedule);
    $schedule->refresh();

    expect($schedule->next_run->copy()->utc()->toDateTimeString())->toBe('2026-11-02 14:00:00')
        ->and($schedule->next_run->copy()->setTimezone('America/New_York')->format('H:i'))->toBe('09:00');
});

it('preserves a fixed local clock in a timezone without daylight saving', function () {
    Carbon::setTestNow('2026-09-09 10:00:00 UTC');
    $user = User::factory()->create();
    UserPreference::query()->updateOrCreate(['user_id' => $user->id], ['timezone' => 'Africa/Douala']);
    $schedule = createTimezoneSchedule($user, '2026-09-09', '15:30')->refresh();

    Carbon::setTestNow('2026-09-09 15:00:00 UTC');
    app(RecurringTransactionService::class)->generate($schedule);
    $schedule->refresh();

    expect($schedule->next_run->copy()->utc()->toDateTimeString())->toBe('2026-09-10 14:30:00')
        ->and($schedule->next_run->copy()->setTimezone('Africa/Douala')->format('H:i'))->toBe('15:30');
});

it('converts an edited local schedule time back to UTC', function () {
    Carbon::setTestNow('2026-09-09 10:00:00 UTC');
    $user = User::factory()->create();
    UserPreference::query()->updateOrCreate(['user_id' => $user->id], ['timezone' => 'Africa/Douala']);
    $schedule = createTimezoneSchedule($user, '2026-09-10', '15:30');

    $this->actingAs($user);
    Livewire::test(Edit::class, ['recurringTransaction' => $schedule])
        ->set('scheduledTime', '16:45')
        ->call('save')
        ->assertHasNoErrors();

    expect($schedule->refresh()->next_run->copy()->utc()->toDateTimeString())->toBe('2026-09-10 15:45:00')
        ->and($schedule->timezone)->toBe('Africa/Douala');
});

it('formats queued recurring notification timestamps for the recipient timezone', function () {
    Carbon::setTestNow('2026-09-09 10:00:00 UTC');
    $user = User::factory()->create();
    UserPreference::query()->updateOrCreate(['user_id' => $user->id], ['timezone' => 'Africa/Douala']);
    $schedule = createTimezoneSchedule($user, '2026-09-09', '15:30');
    $scheduledFor = Carbon::parse('2026-09-09 14:30:00 UTC');

    $payload = (new RecurringTransactionNotification(
        recurringTransaction: $schedule,
        type: RecurringTransactionNotificationType::Generated,
        scheduledFor: $scheduledFor,
    ))->toDatabase($user);

    expect($payload['scheduled_for'])->toBe('2026-09-09 15:30:00')
        ->and($payload['message'])->toContain('09 Sep 2026 at 15:30');
});
