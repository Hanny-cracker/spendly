<?php

use App\Actions\Transactions\CreateTransaction;
use App\Actions\Transactions\DeleteTransaction;
use App\Actions\Transactions\UpdateTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Data\Transaction\UpdateTransactionData;
use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\BudgetExceededException;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Budgets\BudgetSpendingService;
use Carbon\Carbon;

function enforcedExpense(User $user, Account $account, Category $category, float $amount, string $date = '2026-09-15', TransactionStatus $status = TransactionStatus::Completed): CreateTransactionData
{
    return new CreateTransactionData($user->id, $account->id, $category->id, 'Budget expense', null, $amount, TransactionType::Expense, $status, Carbon::parse($date));
}

function activeBudget(User $user, Category $category, float $amount = 100000, string $start = '2026-09-01', string $end = '2026-09-30'): Budget
{
    return Budget::factory()->for($user)->for($category)->create(['amount' => $amount, 'period' => BudgetPeriod::Monthly, 'start_date' => $start, 'end_date' => $end, 'is_active' => true]);
}

it('allows expenses up to the exact remaining amount', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 200000]);
    $food = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = activeBudget($user, $food);
    $action = app(CreateTransaction::class);

    $action->handle(enforcedExpense($user, $account, $food, 60000));
    $action->handle(enforcedExpense($user, $account, $food, 40000));

    $availability = app(BudgetSpendingService::class)->availability($budget->fresh('category'));
    expect($availability->spent)->toBe(100000.0)->and($availability->remaining)->toBe(0.0);
});

it('atomically blocks completed overspending but allows no-budget income and pending records', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 200000]);
    $food = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $salary = Category::factory()->for($user)->create(['type' => CategoryType::Income]);
    activeBudget($user, $food, 100000);
    $action = app(CreateTransaction::class);
    $action->handle(enforcedExpense($user, $account, $food, 80000));
    $balance = $account->refresh()->current_balance;
    $count = Transaction::query()->count();

    expect(fn () => $action->handle(enforcedExpense($user, $account, $food, 30000)))->toThrow(BudgetExceededException::class, '20,000 FCFA remaining');
    expect(Transaction::query()->count())->toBe($count)->and($account->refresh()->current_balance)->toBe($balance);

    $action->handle(enforcedExpense($user, $account, $food, 30000, status: TransactionStatus::Pending));
    $action->handle(new CreateTransactionData($user->id, $account->id, $salary->id, 'Income', null, 500000, TransactionType::Income, TransactionStatus::Completed, Carbon::parse('2026-09-15')));
    expect(Transaction::query()->count())->toBe($count + 2);
});

it('allows an expense when no applicable budget exists', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 1000000]);
    $food = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);

    expect(app(CreateTransaction::class)->handle(enforcedExpense($user, $account, $food, 500000)))->toBeInstanceOf(Transaction::class);
});

it('excludes the edited expense and leaves all state unchanged after a rejected update', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 200000]);
    $food = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    activeBudget($user, $food);
    $create = app(CreateTransaction::class);
    $create->handle(enforcedExpense($user, $account, $food, 50000));
    $edited = $create->handle(enforcedExpense($user, $account, $food, 30000));
    $update = app(UpdateTransaction::class);
    $data = fn (float $amount) => new UpdateTransactionData($account->id, $food->id, 'Edited', null, null, null, $amount, TransactionType::Expense, Carbon::parse('2026-09-15'), TransactionStatus::Completed);

    expect($update->handle($edited, $data(45000))->amount)->toBe(45000.0);
    $balance = $account->refresh()->current_balance;
    expect(fn () => $update->handle($edited->refresh(), $data(60000)))->toThrow(BudgetExceededException::class);
    expect($edited->refresh()->amount)->toBe(45000.0)->and($account->refresh()->current_balance)->toBe($balance);
});

it('checks destination category and date when editing or completing a pending expense', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 300000]);
    $transport = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $food = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    activeBudget($user, $food, 10000, '2026-10-01', '2026-10-31');
    $transaction = app(CreateTransaction::class)->handle(enforcedExpense($user, $account, $transport, 20000, '2026-09-30', TransactionStatus::Pending));
    $data = new UpdateTransactionData($account->id, $food->id, $transaction->title, null, null, null, 20000, TransactionType::Expense, Carbon::parse('2026-10-01'), TransactionStatus::Completed);

    expect(fn () => app(UpdateTransaction::class)->handle($transaction, $data))->toThrow(BudgetExceededException::class);
    expect($transaction->refresh()->category_id)->toBe($transport->id)->and($transaction->status)->toBe(TransactionStatus::Pending)->and($transaction->date->toDateString())->toBe('2026-09-30');
});

it('uses the destination date budget when moving a completed expense', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 300000]);
    $food = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    activeBudget($user, $food, 100000, '2026-09-01', '2026-09-30');
    activeBudget($user, $food, 10000, '2026-10-01', '2026-10-31');
    $transaction = app(CreateTransaction::class)->handle(enforcedExpense($user, $account, $food, 20000, '2026-09-30'));
    $data = new UpdateTransactionData($account->id, $food->id, $transaction->title, null, null, null, 20000, TransactionType::Expense, Carbon::parse('2026-10-01'), TransactionStatus::Completed);

    expect(fn () => app(UpdateTransaction::class)->handle($transaction, $data))->toThrow(BudgetExceededException::class);
    expect($transaction->refresh()->date->toDateString())->toBe('2026-09-30');
});

it('restores derived availability when a completed expense is deleted', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 200000]);
    $food = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = activeBudget($user, $food);
    $transaction = app(CreateTransaction::class)->handle(enforcedExpense($user, $account, $food, 80000));

    expect(app(BudgetSpendingService::class)->availability($budget->fresh('category'))->remaining)->toBe(20000.0);
    app(DeleteTransaction::class)->handle($transaction);
    expect(app(BudgetSpendingService::class)->availability($budget->fresh('category'))->remaining)->toBe(100000.0);
});

it('never applies another users budget to an expense', function () {
    $budgetOwner = User::factory()->create();
    $spender = User::factory()->create();
    $budgetCategory = Category::factory()->for($budgetOwner)->create(['type' => CategoryType::Expense]);
    $spenderCategory = Category::factory()->for($spender)->create(['type' => CategoryType::Expense]);
    $account = Account::factory()->for($spender)->create(['current_balance' => 200000]);
    activeBudget($budgetOwner, $budgetCategory, 1);

    expect(app(CreateTransaction::class)->handle(enforcedExpense($spender, $account, $spenderCategory, 50000)))->toBeInstanceOf(Transaction::class);
});
