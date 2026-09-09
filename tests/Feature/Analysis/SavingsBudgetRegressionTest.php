<?php

use App\Actions\Analysis\SavingsAnalysis;
use App\Actions\Goals\CreateGoalContribution;
use App\Actions\Transactions\CreateTransaction;
use App\Actions\Transfers\CreateTransfer;
use App\Data\Goal\CreateGoalContributionData;
use App\Data\Report\DateRangeData;
use App\Data\Transaction\CreateTransactionData;
use App\Data\Transfer\CreateTransferData;
use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Enums\RecurringStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\BudgetExceededException;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Services\Budgets\BudgetSpendingService;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Carbon\Carbon;

function savingsBudgetRange(User $user): DateRangeData
{
    return new DateRangeData($user->id, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30'));
}

function savingsRegressionBudget(User $user, Category $category, float $amount = 100000): Budget
{
    return Budget::factory()->for($user)->for($category)->create([
        'amount' => $amount,
        'period' => BudgetPeriod::Monthly,
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-30',
        'is_active' => true,
    ]);
}

function savingsRegressionTransaction(User $user, Account $account, Category $category, float $amount, TransactionType $type): CreateTransactionData
{
    return new CreateTransactionData(
        userId: $user->id,
        accountId: $account->id,
        categoryId: $category->id,
        title: 'Savings regression',
        description: null,
        amount: $amount,
        type: $type,
        date: Carbon::parse('2026-09-10'),
        status: TransactionStatus::Completed,
    );
}

it('does not consume an expense budget when cash is transferred into savings', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create(['current_balance' => 200000]);
    $savings = Account::factory()->for($user)->savings()->create();
    $expenseCategory = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = savingsRegressionBudget($user, $expenseCategory);

    app(CreateTransfer::class)->handle(new CreateTransferData($user->id, $cash->id, $savings->id, 50000, null, Carbon::parse('2026-09-10')));

    expect(app(BudgetSpendingService::class)->availability($budget->fresh('category'))->spent)->toBe(0.0)
        ->and(app(SavingsAnalysis::class)->handle(savingsBudgetRange($user))->netSavingsAccountActivity)->toBe(50000.0);
});

it('does not consume an expense budget when recording a goal contribution', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create();
    $expenseCategory = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = savingsRegressionBudget($user, $expenseCategory);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 100000]);

    app(CreateGoalContribution::class)->handle($goal, new CreateGoalContributionData($user->id, 30000, Carbon::parse('2026-09-10'), $cash->id));

    $analysis = app(SavingsAnalysis::class)->handle(savingsBudgetRange($user));
    expect(app(BudgetSpendingService::class)->availability($budget->fresh('category'))->spent)->toBe(0.0)
        ->and($analysis->periodGoalContributions)->toBe(30000.0)
        ->and($analysis->qualifyingAdditionalGoalSaving)->toBe(30000.0);
});

it('does not consume an expense budget for income deposited directly into savings', function () {
    $user = User::factory()->create();
    $savings = Account::factory()->for($user)->savings()->create();
    $incomeCategory = Category::factory()->for($user)->create(['type' => CategoryType::Income]);
    $expenseCategory = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = savingsRegressionBudget($user, $expenseCategory);

    app(CreateTransaction::class)->handle(savingsRegressionTransaction($user, $savings, $incomeCategory, 100000, TransactionType::Income));

    $analysis = app(SavingsAnalysis::class)->handle(savingsBudgetRange($user));
    expect(app(BudgetSpendingService::class)->availability($budget->fresh('category'))->spent)->toBe(0.0)
        ->and($analysis->periodSavingsAccountDeposits)->toBe(100000.0)
        ->and($analysis->completedIncome)->toBe(100000.0);
});

it('does not alter savings analysis when an over-budget expense is blocked', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create(['current_balance' => 200000]);
    $savings = Account::factory()->for($user)->savings()->create();
    $incomeCategory = Category::factory()->for($user)->create(['type' => CategoryType::Income]);
    $expenseCategory = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    savingsRegressionBudget($user, $expenseCategory, 10000);
    app(CreateTransaction::class)->handle(savingsRegressionTransaction($user, $savings, $incomeCategory, 100000, TransactionType::Income));
    $before = app(SavingsAnalysis::class)->handle(savingsBudgetRange($user))->toArray();

    expect(fn () => app(CreateTransaction::class)->handle(savingsRegressionTransaction($user, $cash, $expenseCategory, 15000, TransactionType::Expense)))
        ->toThrow(BudgetExceededException::class);

    expect(app(SavingsAnalysis::class)->handle(savingsBudgetRange($user))->toArray())->toBe($before);
});

it('does not alter savings analysis when a recurring expense is blocked', function () {
    Carbon::setTestNow('2026-09-10 09:00:00');
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create(['current_balance' => 200000]);
    $savings = Account::factory()->for($user)->savings()->create();
    $incomeCategory = Category::factory()->for($user)->create(['type' => CategoryType::Income]);
    $expenseCategory = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    savingsRegressionBudget($user, $expenseCategory, 10000);
    app(CreateTransaction::class)->handle(savingsRegressionTransaction($user, $savings, $incomeCategory, 100000, TransactionType::Income));
    $schedule = RecurringTransaction::factory()->for($user)->for($cash)->for($expenseCategory)->create([
        'amount' => 15000,
        'next_run' => today(),
        'last_generated_at' => null,
        'status' => RecurringStatus::Active,
    ]);
    $before = app(SavingsAnalysis::class)->handle(savingsBudgetRange($user))->toArray();

    expect(fn () => app(RecurringTransactionService::class)->generate($schedule))->toThrow(BudgetExceededException::class);
    expect(app(SavingsAnalysis::class)->handle(savingsBudgetRange($user))->toArray())->toBe($before);

    Carbon::setTestNow();
});
