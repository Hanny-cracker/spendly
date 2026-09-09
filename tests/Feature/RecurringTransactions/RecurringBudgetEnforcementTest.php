<?php

use App\Actions\Transactions\CreateTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Enums\RecurringStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\BudgetExceededException;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Budgets\BudgetSpendingService;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

function recurringBudget(User $user, Category $category, float $amount): Budget
{
    return Budget::factory()->for($user)->for($category)->create(['amount' => $amount, 'period' => BudgetPeriod::Monthly, 'start_date' => '2026-09-01', 'end_date' => '2026-09-30', 'is_active' => true]);
}

function recurringExpense(User $user, Account $account, Category $category, float $amount): CreateTransactionData
{
    return new CreateTransactionData($user->id, $account->id, $category->id, 'Existing expense', null, $amount, TransactionType::Expense, TransactionStatus::Completed, Carbon::parse('2026-09-05'));
}

it('keeps a budget-blocked recurring occurrence due and unsent', function () {
    Notification::fake();
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    recurringBudget($user, $category, 50000);
    app(CreateTransaction::class)->handle(recurringExpense($user, $account, $category, 45000));
    $schedule = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create(['amount' => 15000, 'next_run' => today(), 'last_generated_at' => null, 'status' => RecurringStatus::Active]);
    $balance = $account->refresh()->current_balance;
    $nextRun = $schedule->next_run->toDateTimeString();
    $count = Transaction::query()->count();

    expect(fn () => app(RecurringTransactionService::class)->generate($schedule))->toThrow(BudgetExceededException::class);
    expect(Transaction::query()->count())->toBe($count)
        ->and($account->refresh()->current_balance)->toBe($balance)
        ->and($schedule->refresh()->last_generated_at)->toBeNull()
        ->and($schedule->status)->toBe(RecurringStatus::Active)
        ->and($schedule->next_run->toDateTimeString())->toBe($nextRun);
    Notification::assertNothingSent();
});

it('generates a recurring expense that fits and advances its schedule', function () {
    Notification::fake();
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = recurringBudget($user, $category, 50000);
    app(CreateTransaction::class)->handle(recurringExpense($user, $account, $category, 30000));
    $schedule = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create(['amount' => 15000, 'next_run' => today(), 'last_generated_at' => null, 'status' => RecurringStatus::Active]);
    $oldNextRun = $schedule->next_run;

    $generated = app(RecurringTransactionService::class)->generate($schedule);

    expect($generated->amount)->toBe(15000.0)
        ->and($schedule->refresh()->last_generated_at)->not->toBeNull()
        ->and($schedule->next_run->gt($oldNextRun))->toBeTrue()
        ->and(app(BudgetSpendingService::class)->availability($budget->fresh('category'))->remaining)->toBe(5000.0);
});
